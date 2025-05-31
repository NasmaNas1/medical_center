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
        Schema::create('doctor_patient', function (Blueprint $table) {
             $table->uuid('patient_id');
             $table->unsignedBigInteger('doctor_id');
 
             $table->foreign('patient_id')
                  ->references('uuid')
                  ->on('patients')
                  ->onDelete('cascade');
                  
             $table->foreign('doctor_id')
                  ->references('id')
                  ->on('doctors')
                  ->onDelete('cascade');
             $table->primary(['doctor_id', 'patient_id']);
             $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_patient');
    }
};
