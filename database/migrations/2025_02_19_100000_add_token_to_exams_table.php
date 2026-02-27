<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('token', 8)->default('')->after('is_active');
        });

        $exams = \DB::table('exams')->get();
        foreach ($exams as $exam) {
            \DB::table('exams')->where('id', $exam->id)->update([
                'token' => strtoupper(\Illuminate\Support\Str::random(6)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
