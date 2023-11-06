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
        Schema::table('types_of_campaign', function (Blueprint $table) {
            $table->string("state")->after('status')->default('Public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('types_of_campaign', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
