<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = DB::table('exam_sessions')
            ->whereIn('status', ['completed', 'timed_out'])
            ->get(['id', 'exam_id']);

        if ($sessions->isEmpty()) return;

        $examQuestions = [];
        foreach (DB::table('questions')->get(['exam_id', 'id']) as $q) {
            $examQuestions[$q->exam_id][] = $q->id;
        }

        $questionAnswers = [];
        foreach (DB::table('answers')->get(['question_id', 'id', 'is_correct']) as $a) {
            $questionAnswers[$a->question_id][] = $a;
        }

        $userAnswers = [];
        $now = now();

        foreach ($sessions as $sess) {
            $qIds = $examQuestions[$sess->exam_id] ?? [];
            if (empty($qIds)) continue;

            $answered = array_slice($qIds, 0, rand(1, min(count($qIds), 15)));
            foreach ($answered as $qid) {
                $ans = $questionAnswers[$qid] ?? [];
                $pick = !empty($ans) ? $ans[array_rand($ans)] : null;
                $isCorrect = $pick ? (bool) $pick->is_correct : false;

                $userAnswers[] = [
                    'exam_session_id' => $sess->id,
                    'question_id' => $qid,
                    'answer_id' => $pick?->id,
                    'essay_text' => null,
                    'essay_score' => null,
                    'is_correct' => $isCorrect,
                    'is_graded' => true,
                    'is_ragu' => (bool) rand(0, 4),
                    'time_spent_seconds' => rand(10, 180),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($userAnswers, 500) as $chunk) {
            DB::table('user_answers')->insert($chunk);
        }
    }
}
