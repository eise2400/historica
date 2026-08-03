<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'maiden_name', 'birth_year', 'death_year', 'notes'];

    public function tags(): HasMany
    {
        return $this->hasMany(PhotoPersonTag::class);
    }

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'photo_person_tags')->withPivot([
            'x_percent', 'y_percent', 'note', 'status', 'suggested_by', 'reviewed_by',
        ]);
    }

    public function getYearRangeAttribute(): string
    {
        if ($this->birth_year && $this->death_year) {
            return "{$this->birth_year}–{$this->death_year}";
        }
        if ($this->birth_year) {
            return "* {$this->birth_year}";
        }
        if ($this->death_year) {
            return "† {$this->death_year}";
        }

        return '';
    }

    public function getFullNameAttribute(): string
    {
        $name = trim("{$this->first_name} {$this->last_name}");
        if ($this->maiden_name) {
            $name .= " geb. {$this->maiden_name}";
        }
        $years = $this->year_range;

        return $years ? "{$name} ({$years})" : $name;
    }
}
