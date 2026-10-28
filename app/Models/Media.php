<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Accessor for 'name' to provide seamless compatibility with TV show templates.
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['title'] ?? ($this->data['name'] ?? null);
    }
}
