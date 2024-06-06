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
        Schema::create('achievement_tracker', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('memeber_id')->nullable();
            $table->string('bill_amount')->nullable();
            $table->string('bv')->nullable();
            $table->string('pv')->nullable();
            $table->string('group_bv')->nullable();
            $table->string('group_pv')->nullable();
            $table->date('date')->nullable();

            $table->foreign('achievement_id')->references('id')->on('achievement_lists');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('memeber_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_tracker');
    }
};
