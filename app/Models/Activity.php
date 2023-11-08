<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = "activities";

    protected $primaryKey = "id";

    protected  $fillable = [
        "name",
        "status",
        "description",
        "campaign_id"
    ];

    public function Campaign() {
        return $this->belongsTo(Campaign::all());
    }
}
