<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Throwable;

class Photo extends Model
{
    use HasFactory;

    /**
     * Fixed size (px) that thumbnails are cropped to. Kept well under the
     * originals so listing pages with thousands of photos stay fast.
     */
    public const THUMBNAIL_WIDTH = 480;

    public const THUMBNAIL_HEIGHT = 360;

    /**
     * Uploaded photos are capped to this size before being stored - the
     * site never serves true full-resolution originals. Scans/photos from
     * a phone or flatbed scanner can be tens of megapixels; nothing on the
     * site ever displays more than this.
     */
    public const MAX_IMAGE_DIMENSION = 2000;

    public const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

    protected $fillable = [
        'title', 'slug', 'image_path', 'thumbnail_path', 'description',
        'category_id', 'location_id',
        'date_from', 'date_to', 'date_text',
        'source', 'inventory_number', 'is_published', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Photo $photo) {
            if (! $photo->slug) {
                $base = Str::slug($photo->title) ?: 'foto';
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $photo->id)->exists()) {
                    $i++;
                    $slug = "{$base}-{$i}";
                }
                $photo->slug = $slug;
            }

            if ($photo->image_path && $photo->isDirty('image_path')) {
                $photo->optimizeImage();
            }
        });

        static::saved(function (Photo $photo) {
            if ($photo->image_path && ($photo->wasChanged('image_path') || ! $photo->thumbnail_path)) {
                $photo->generateThumbnail();
            }
        });
    }

    /**
     * The site never needs more than MAX_IMAGE_DIMENSION px / MAX_IMAGE_BYTES
     * of a photo - full-resolution originals (phone photos, flatbed scans)
     * are discarded in favour of a capped, re-encoded JPEG. Runs before the
     * photo is saved so the smaller path is what actually gets persisted.
     * Best-effort: a failure here must not block saving the photo itself,
     * it just leaves the originally uploaded file untouched.
     */
    public function optimizeImage(): void
    {
        $disk = Storage::disk('public');
        $originalPath = $this->image_path;

        if (! $disk->exists($originalPath)) {
            return;
        }

        $newPath = 'photos/'.now()->format('Y/m').'/'.Str::ulid().'.jpg';

        try {
            $manager = ImageManager::gd();
            $image = $manager->read($disk->path($originalPath))
                ->scaleDown(self::MAX_IMAGE_DIMENSION, self::MAX_IMAGE_DIMENSION);

            $quality = 85;
            do {
                $encoded = $image->toJpeg(quality: $quality);
                $quality -= 10;
            } while (strlen((string) $encoded) > self::MAX_IMAGE_BYTES && $quality >= 40);

            $disk->makeDirectory(dirname($newPath));
            $disk->put($newPath, (string) $encoded);
        } catch (Throwable $e) {
            Log::warning("Bildoptimierung für Foto #{$this->id} fehlgeschlagen: {$e->getMessage()}");

            return;
        }

        if ($newPath !== $originalPath) {
            $disk->delete($originalPath);
        }

        // Replacing an existing photo's image (e.g. re-uploading via the
        // edit form) leaves the previous optimized file orphaned on disk
        // otherwise - clean it up too, unless it's somehow the same file.
        $previousPath = $this->getOriginal('image_path');
        if ($previousPath && $previousPath !== $originalPath && $previousPath !== $newPath) {
            $disk->delete($previousPath);
        }

        $this->image_path = $newPath;
    }

    /**
     * Crop a small preview image next to the original so listing pages with
     * thousands of photos never have to load full-resolution files. Kept
     * best-effort: a failure here must not block saving the photo itself.
     */
    public function generateThumbnail(): void
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($this->image_path)) {
            return;
        }

        $thumbnailPath = 'photos/thumbnails/'.pathinfo($this->image_path, PATHINFO_FILENAME).'.jpg';
        $disk->makeDirectory('photos/thumbnails');

        try {
            $manager = ImageManager::gd();
            $manager->read($disk->path($this->image_path))
                ->cover(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT)
                ->save($disk->path($thumbnailPath), quality: 80);
        } catch (Throwable $e) {
            Log::warning("Thumbnail-Erstellung für Foto #{$this->id} fehlgeschlagen: {$e->getMessage()}");

            return;
        }

        // Bypass model events (update() would re-trigger `saved` above).
        static::withoutEvents(fn () => $this->newQueryWithoutScopes()
            ->where('id', $this->id)
            ->update(['thumbnail_path' => $thumbnailPath]));
        $this->thumbnail_path = $thumbnailPath;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function personTags(): HasMany
    {
        return $this->hasMany(PhotoPersonTag::class);
    }

    public function approvedTags(): HasMany
    {
        return $this->personTags()->where('status', PhotoPersonTag::STATUS_APPROVED)->with('person');
    }

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'photo_person_tags')->withPivot([
            'x_percent', 'y_percent', 'note', 'status',
        ]);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /**
     * Small preview for grids/listings; falls back to the full image for
     * photos uploaded before thumbnails existed or if generation failed.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $path = $this->thumbnail_path ?: $this->image_path;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function getDateDisplayAttribute(): string
    {
        if ($this->date_text) {
            return $this->date_text;
        }
        if ($this->date_from && $this->date_to && ! $this->date_from->equalTo($this->date_to)) {
            return $this->date_from->format('d.m.Y').' – '.$this->date_to->format('d.m.Y');
        }
        if ($this->date_from) {
            return $this->date_from->format('d.m.Y');
        }

        return '';
    }

    public function getUrlAttribute(): string
    {
        return route('archive.show', $this->slug);
    }
}
