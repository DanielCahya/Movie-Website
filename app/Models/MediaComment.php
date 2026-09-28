<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'user_id',
        'media_id',
        'media_type',
        'media_title',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(MediaComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(MediaComment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->hasMany(CommentLike::class);
    }
}
