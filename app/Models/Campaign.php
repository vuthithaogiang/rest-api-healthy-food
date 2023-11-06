<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = "campaigns";

    protected  $primaryKey   = "id";

    protected  $fillable = [
         "name",
         "slug",
        "type_campaign_id",
        "objective",
        "description",
        "start_date",
        "end_date",
        "channel",
        "thumbnail",
        "status", "budget",
        "customer_kpi"
    ];
    public function TypeOf() {
        return $this->belongsTo(TypeOfCampaign::class);
    }

    public function Thumbnails() {
        return $this->hasMany(CampaignThumbnails::class);
    }

}
