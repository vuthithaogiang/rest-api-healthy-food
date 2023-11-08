<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCampaign extends Model
{
    use HasFactory;


    protected  $table = "schedules_campaign";
    protected $primaryKey = "id";

    protected  $fillable = [
        "campaign_id",
        "start_date",
        "end_date"
    ];

    public function Cmapaign() {
        return $this->belongsTo(Campaign::class);
    }
}
