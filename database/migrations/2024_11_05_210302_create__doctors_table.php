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
        // Schema::create('doctors', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('image')->nullable();
        //     $table->foreignId('specialization_id')->references('id')->on('Specializations')->onDelete('cascade');
        //     $table->string('email');
        //     $table->text('about_doctor');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
