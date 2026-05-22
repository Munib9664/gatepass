<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'block',
        'number',
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    public function getLabelAttribute(): string
    {
        return "{$this->block}-{$this->number}";
    }
}
