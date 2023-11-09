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
        Schema::table('activities', function (Blueprint $table) {
            $table->unique('name');
            $table->string('slug')->unique()->after('name');
            $table->unsignedBigInteger('type_of_activity_id');
            $table->foreign("type_of_activity_id")->references("id")->on("types_of_activity");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('type_of_activity_id');
            $table->dropColumn('slug');

        });
    }
};
