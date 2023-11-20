<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetBudget extends Model
{
    use HasFactory;

    protected $table = "target_budget";

    protected $primaryKey = "id";

    protected $fillable = [
        'amount' ,
        'target',
        'campaign_id'
    ];

    public function Campaign() {
        return $this->belongsTo(Campaign::class);
    }
}
