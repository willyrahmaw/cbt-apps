<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        $questionIds = Question::pluck('id')->toArray();
        if (empty($questionIds)) return;

        $answers = [];
        $now = now();
        $letters = ['A', 'B', 'C', 'D'];

        foreach ($questionIds as $qid) {
            $correctIdx = rand(0, 3);
            for ($o = 0; $o < 4; $o++) {
                $answers[] = [
                    'question_id' => $qid,
                    'answer_text' => 'Opsi ' . $letters[$o] . ' - Jawaban ' . ($qid + $o),
                    'is_correct' => $o === $correctIdx,
                    'order' => $o + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($answers, 300) as $chunk) {
            Answer::insert($chunk);
        }
    }
}
