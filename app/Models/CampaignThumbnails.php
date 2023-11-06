<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignThumbnails extends Model
{
    use HasFactory;

    protected $table = "campaign_thumbnails";

    protected $primaryKey = "id";

    protected $fillable = [
        'campaign_id',
        "path"
    ];

    public function Campaign() {
        return $this->belongsTo(Campaign::class);
    }
}
