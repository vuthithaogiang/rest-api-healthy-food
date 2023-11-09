<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfActivity extends Model
{
    use HasFactory;

    protected $primaryKey = "id";

    protected $table = "types_of_activity";

    protected $fillable = [
        "name",
        "slug",
        "description"
    ];

    public function Activities() {
        return $this->hasMany(Activity::class);
    }
}
