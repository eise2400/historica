<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipApplication extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'first_name', 'last_name', 'street', 'postal_code', 'city',
        'email', 'phone', 'birth_date', 'message', 'handled',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'handled' => 'boolean',
        ];
    }
}
