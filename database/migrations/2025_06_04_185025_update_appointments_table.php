<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // تغيير حالة الموعد
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])
                  ->default('pending')->change();
            
            // إضافة الحقول الجديدة
            $table->foreignId('sub_specialization_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('duration')->comment('مدة الموعد بالدقائق');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('schedule_id')->nullable()->constrained('doctor_schedules')->onDelete('cascade');
            
            // إزالة الحقول القديمة (اختياري)
            $table->dropColumn(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->dropForeign(['sub_specialization_id']);
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['sub_specialization_id', 'duration', 'cancellation_reason', 'schedule_id']);
            
            // استعادة الحقول القديمة (اختياري)
            $table->time('start_time');
            $table->time('end_time');
        });
    }
};