<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('specialization_id')->constrained()->onDelete('cascade');
            $table->integer('duration')->comment('مدة الخدمة بالدقائق');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_specializations');
    }
};