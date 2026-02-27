<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->unsignedInteger('remaining_seconds_on_termination')->nullable()->after('needs_grading');
        });

        \DB::statement("ALTER TABLE exam_sessions MODIFY COLUMN status ENUM('in_progress','completed','timed_out','terminated') DEFAULT 'in_progress'");

        Schema::table('exams', function (Blueprint $table) {
            $table->json('terminate_on_events')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn('remaining_seconds_on_termination');
        });

        \DB::statement("ALTER TABLE exam_sessions MODIFY COLUMN status ENUM('in_progress','completed','timed_out') DEFAULT 'in_progress'");

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('terminate_on_events');
        });
    }
};
