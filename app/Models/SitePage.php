<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SitePage extends Model
{
    public const CREATED_AT = null;

    protected $fillable = ['slug', 'title', 'content', 'document_path'];

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? Storage::disk('public')->url($this->document_path) : null;
    }
}
