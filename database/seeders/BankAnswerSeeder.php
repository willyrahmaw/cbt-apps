<?php

namespace Database\Seeders;

use App\Models\BankAnswer;
use App\Models\BankQuestion;
use Illuminate\Database\Seeder;

class BankAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $questionIds = BankQuestion::pluck('id')->toArray();
        if (empty($questionIds)) return;

        $answers = [];
        $now = now();
        $choices = ['Benar', 'Salah', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Tidak ada yang tepat', 'Semua benar'];

        foreach ($questionIds as $qid) {
            $correctIdx = rand(0, 3);
            for ($o = 0; $o < 4; $o++) {
                $answers[] = [
                    'bank_question_id' => $qid,
                    'answer_text' => $choices[($qid + $o) % count($choices)] . ' ' . ($o + 1),
                    'is_correct' => $o === $correctIdx,
                    'order' => $o + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($answers, 200) as $chunk) {
            BankAnswer::insert($chunk);
        }
    }
}
