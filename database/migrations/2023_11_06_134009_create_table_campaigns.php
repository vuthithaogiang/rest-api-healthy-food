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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("type_campaign_id");
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('objective');
            $table->longText('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('channel')->default('Web');
            $table->smallInteger("status")->default(0);
            $table->unsignedDecimal("budget", 14, 2)->nullable();
            $table->unsignedSmallInteger("customer_kpi")->nullable();
            $table->foreign("type_campaign_id")->references("id")->on("types_of_campaign");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
