<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $table = "donations";
    protected $primaryKey = "id";

    protected  $fillable = [
        "amount",
        "message",
        "status",
        "payment_method",
        "campaign_id",
        "user_id"
    ];

    public function Campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function User() {
        return $this->belongsTo(User::class);
    }
}
