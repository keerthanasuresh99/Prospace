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
        Schema::create('achievement_dreams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id');
            $table->string('image')->nullable();
            $table->date('achievement_date');
            $table->integer('featured_image')->default(0)->comment('1:Featured Image , 0 : Not featured image');
            $table->foreign('achievement_id')->references('id')->on('base_settings');
            $table->date('achieved_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_dreams');
    }
};
