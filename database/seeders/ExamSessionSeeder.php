<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExamSessionSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::where('role', 'pengguna')->pluck('id')->toArray();
        $examIds = Exam::pluck('id')->toArray();
        if (empty($userIds) || empty($examIds)) return;

        $sessions = [];
        $statuses = ['in_progress', 'completed', 'timed_out'];
        $now = now();

        for ($i = 0; $i < 1000; $i++) {
            $started = $now->copy()->subDays(rand(0, 60))->subMinutes(rand(0, 120));
            $status = $statuses[array_rand($statuses)];
            $finished = ($status !== 'in_progress') ? $started->copy()->addMinutes(rand(10, 60)) : null;
            $totalQ = rand(5, 30);
            $correct = $status === 'completed' ? rand(0, $totalQ) : 0;
            $score = $totalQ > 0 ? (int) round(($correct / $totalQ) * 100) : null;

            $sessions[] = [
                'user_id' => $userIds[array_rand($userIds)],
                'exam_id' => $examIds[array_rand($examIds)],
                'started_at' => $started,
                'finished_at' => $finished,
                'score' => $score,
                'correct_answers' => $correct,
                'total_questions' => $totalQ,
                'status' => $status,
                'needs_grading' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($sessions, 200) as $chunk) {
            ExamSession::insert($chunk);
        }
    }
}
