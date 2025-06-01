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
        Schema::table('doctors', function (Blueprint $table) {
        
        $table->dropForeign(['specialization_id']);
        $table->foreign('specialization_id')
              ->references('id')
              ->on('specializations')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['specialization_id']);

        $table->foreign('specialization_id')
              ->references('id')
              ->on('Specializations')
              ->onDelete('cascade');
   
        });
    }
};
