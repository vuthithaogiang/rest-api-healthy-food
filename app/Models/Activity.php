<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = "activities";

    protected $primaryKey = "id";

    const NEW_CREATED = 0;
    const ON_GOING = 1;

    const PAUSED = 2;

    const COMPLETE = 3;

    protected  $fillable = [
        "name",
        "slug",
        "type_of_activity_id",
        "status",
        "description",
        "campaign_id"
    ];

    public function Campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function TypeOfActivity() {
        return $this->belongsTo(TypeOfActivity::class);
    }


}
