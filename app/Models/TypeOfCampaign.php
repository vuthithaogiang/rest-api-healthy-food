<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfCampaign extends Model
{
    use HasFactory;

    protected $table = "types_of_campaign";

    protected $primaryKey = "id";

    protected $fillable = [
        "name",
        "slug",
        "status",
        "description",
        "state"
    ];

    public function Campaigns() {
        return $this->hasMany(Campaign::class);
    }

}
