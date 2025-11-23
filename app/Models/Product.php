<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'photo_batch_id',
        'title',
        'description',
        'price',
        'brand',
        'category',
        'size',
        'color',
        'material',
        'condition',
        'status',
    ];

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function batch()
    {
        return $this->belongsTo(PhotoBatch::class, 'photo_batch_id');
    }

    public function getImagePathsAttribute()
    {
        return $this->photos->pluck('image_path')->toArray();
    }
}
