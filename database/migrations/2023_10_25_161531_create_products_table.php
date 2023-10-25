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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("category_id");
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->longText('description');
            $table->string("brand");
            $table->unsignedDecimal("price", 14, 2);
            $table->unsignedSmallInteger("quantity");
            $table->smallInteger("status")->default(1);
            $table->foreign("category_id")->references("id")->on("category_products");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
