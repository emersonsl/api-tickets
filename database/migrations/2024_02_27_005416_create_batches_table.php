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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('sector_id');
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('sector_id')->references('id')->on('sectors');
            $table->string('title');
            $table->unsignedBigInteger('quantity');
            $table->unsignedFloat('value');
            $table->dateTime('release_date_time');
            $table->dateTime('expiration_date_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
