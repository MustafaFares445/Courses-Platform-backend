<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'image', 'category_id'];

    public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

public function tags()
{
    return $this->belongsToMany(Tags::class, 'post_id', 'tag_id');
}

}
