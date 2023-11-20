<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = "posts";

    protected  $primaryKey = "id";

    protected $fillable = [
        "title",
        "description",
        "content",
        "thumbnail",
        "status",
        "campaign_id",
        "user_id"

    ];

    public function User() {
        return $this->belongsTo(User::class);
    }

    public function Campaign() {
        return $this->belongsTo(Campaign::class);
    }
}
