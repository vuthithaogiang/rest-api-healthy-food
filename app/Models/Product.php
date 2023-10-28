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
        "main_thumbnail",
        "description",
        "price",
        "quantity",
        "status",
        "category_id"
    ];
    const IN_AVAILABLE = 0; // STATEMENT
    const AVAILABLE = 1; // STATEMENT
    const UPCOMING = 2; // STATEMENT
    const NEW_ARRIVAL = 3; // STATEMENT

    const SOLD_OUT = 4; // CALCULATE
    const BEST_SELLER = 5; // CALCULATE

    public function Category() {
        return $this->belongsTo(CategoryProduct::class);
    }

    public function Thumbnails() {
        return $this->hasMany(ProductThumbnails::class);
    }

}
