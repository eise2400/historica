<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'image_path', 'description',
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
        });
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
