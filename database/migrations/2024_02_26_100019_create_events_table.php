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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('create_by');
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('create_by')->references('id')->on('users');
            $table->string('title');
            $table->dateTime('date_time');
            $table->string('banner_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
