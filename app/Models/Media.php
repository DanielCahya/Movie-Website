<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Media
 *
 * @property int $id
 * @property int $tmdb_id
 * @property string $media_type
 * @property string $title
 * @property string|null $poster_path
 * @property float|null $vote_average
 * @property string|null $genre_ids
 * @property array|null $data
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Media extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'data' => 'array',
        'vote_average' => 'float',
    ];

    /**
     * Bootstrap model events to ensure performance columns stay synchronized.
     */
    protected static function booted(): void
    {
        static::saving(function (Media $media) {
            $data = is_array($media->data) ? $media->data : (is_string($media->data) ? json_decode($media->data, true) : null);
            if (is_array($data)) {
                if (!isset($media->attributes['vote_average']) && isset($data['vote_average']) && is_numeric($data['vote_average'])) {
                    $media->attributes['vote_average'] = round((float) $data['vote_average'], 1);
                }
                if (!isset($media->attributes['genre_ids']) && isset($data['genre_ids']) && is_array($data['genre_ids'])) {
                    $media->attributes['genre_ids'] = ',' . implode(',', array_map('intval', $data['genre_ids'])) . ',';
                }
            }
        });
    }

    /**
     * Accessor for 'name' to provide seamless compatibility with TV show templates.
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['title'] ?? ($this->data['name'] ?? null);
    }
}
