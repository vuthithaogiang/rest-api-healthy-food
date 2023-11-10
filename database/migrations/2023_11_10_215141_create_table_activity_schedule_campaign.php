<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_schedule_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('schedule_campaign_id');
            $table->foreign("activity_id")->references("id")->on("activities");
            $table->foreign("schedule_campaign_id")->references("id")->on("schedules_campaign");
            $table->primary(['activity_id', 'schedule_campaign_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedule_campaigns');
    }
};
