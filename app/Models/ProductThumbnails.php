<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductThumbnails extends Model
{
    use HasFactory;

    protected $table = "product_thumbnails";

    protected $primaryKey = "id";

    protected $fillable = [
        "path"
    ];

    public function Product() {
        return $this->belongsTo(Product::class);
    }

}
