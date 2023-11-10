<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityScheduleCampaign extends Model
{
    use HasFactory;

    protected $table = 'activity_schedule_campaigns';

    protected $primaryKey = ['activity_id', 'schedule_campaign_id'];

    protected $fillable = [
        "activity_id",
        "schedule_campaign_id"
    ];



}
