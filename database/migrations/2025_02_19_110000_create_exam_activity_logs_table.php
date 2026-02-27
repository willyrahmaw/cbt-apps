<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('event', 50); // tab_switch, right_click, copy_attempt, paste_attempt, rate_limit, time_up_attempt
            $table->json('meta')->nullable(); // extra data
            $table->timestamps();
        });

        Schema::table('exam_activity_logs', function (Blueprint $table) {
            $table->index(['exam_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_activity_logs');
    }
};
