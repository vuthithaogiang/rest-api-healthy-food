<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = "products";


    protected $primaryKey = "id";

    protected $fillable  =[
        "name",
        "slug",
        "brand",
        "description",
        "price",
        "quantity",
        "status",
        "category_id"
    ];

    public function Category() {
        return $this->belongsTo(CategoryProduct::class);
    }

    public function Thumbnails() {
        return $this->hasMany(ProductThumbnails::class);
    }

}
