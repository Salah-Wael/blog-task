<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;

    public function post()
    {
        return $this->belongsToMany(Post::class, 'news_tags');
    }
}
