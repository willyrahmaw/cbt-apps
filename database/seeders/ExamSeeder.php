<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::pluck('id')->toArray();
        $pembuatIds = User::where('role', 'pembuat_soal')->pluck('id')->toArray();
        if (empty($categoryIds) || empty($pembuatIds)) return;

        $exams = [];
        $titles = ['UTS', 'UAS', 'Quiz', 'Remedial', 'Try Out'];
        $subjects = ['Matematika', 'Fisika', 'Kimia', 'Biologi', 'Bahasa Indonesia', 'IPA', 'IPS'];
        $now = now();

        for ($i = 0; $i < 60; $i++) {
            $title = $titles[$i % count($titles)] . ' ' . $subjects[$i % count($subjects)] . ' ' . (intdiv($i, 10) + 1);
            $exams[] = [
                'title' => $title,
                'description' => "Ujian {$title}",
                'category_id' => $categoryIds[array_rand($categoryIds)],
                'created_by' => $pembuatIds[array_rand($pembuatIds)],
                'duration' => [30, 45, 60, 90][rand(0, 3)],
                'passing_score' => [60, 70, 75][rand(0, 2)],
                'is_active' => (bool) rand(0, 1),
                'token' => strtoupper(Str::random(6)),
                'shuffle_questions' => (bool) rand(0, 1),
                'shuffle_answers' => (bool) rand(0, 1),
                'show_result' => true,
                'start_time' => $now->copy()->subDays(rand(0, 30)),
                'end_time' => $now->copy()->addDays(rand(1, 60)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($exams, 20) as $chunk) {
            Exam::insert($chunk);
        }
    }
}
