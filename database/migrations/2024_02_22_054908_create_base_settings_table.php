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
        Schema::create('base_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('value');
            $table->text('description')->nullable();
            $table->integer('sort_order')->unsigned()->default(0);
            $table->integer('status')->default(0);
            $table->integer('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_settings');
    }
};
