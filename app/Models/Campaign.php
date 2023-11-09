<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = "campaigns";

    protected  $primaryKey   = "id";

    const NEW_CREATED = 0;
    const ON_GOING = 1;
    const PAUSED = 2;

    const COMPLETE = 3;

    protected  $fillable = [
        "name",
        "slug",
        "type_of_campaign_id",
        "objective",
        "description",
        "start_date",
        "end_date",
        "channel",
        "status",
        "budget",
        "daily_budget"
    ];
    public function TypeOf() {
        return $this->belongsTo(TypeOfCampaign::class);
    }

    public function Thumbnails() {
        return $this->hasMany(CampaignThumbnails::class);
    }

    public function Activities() {
        return $this->hasMany(Activity::class);
    }

    public function ScheduleCampaign() {
        return $this->hasMany(ScheduleCampaign::class);
    }

}
