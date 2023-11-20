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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->string('description');
            $table->string('thumbnail');
            $table->text("content");
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('schedule_campaign_id');
            $table->unsignedInteger('status')->default(0);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('activity_id')->references('id')->on('activities');
            $table->foreign('schedule_campaign_id')->references('id')->on('schedules_campaign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
