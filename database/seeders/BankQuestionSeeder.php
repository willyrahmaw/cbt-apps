<?php

namespace Database\Seeders;

use App\Models\BankQuestion;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class BankQuestionSeeder extends Seeder
{
    private array $questionTemplates = [
        'Berapakah hasil dari {a} + {b}?',
        'Jika {a} x {b} = ... maka hasilnya adalah?',
        'Manakah pernyataan yang benar tentang {topic}?',
        'Apa fungsi dari {topic} dalam konteks tersebut?',
        'Berdasarkan teks, mengapa {topic} terjadi?',
        'Simpulan yang tepat dari paragraf di atas adalah...',
        'Pernyataan yang sesuai dengan isi teks adalah...',
        'Kata "{word}" dalam kalimat tersebut bermakna...',
        'Prinsip kerja {topic} adalah...',
        'Dari data tersebut, dapat disimpulkan bahwa...',
    ];

    private array $topics = ['fotosintesis', 'gravitasi', 'reaksi kimia', 'ekosistem', 'demokrasi', 'pasar', 'kolonialisme', 'algoritma', 'jaringan', 'puisi'];

    public function run(): void
    {
        $bankIds = QuestionBank::pluck('id')->toArray();
        if (empty($bankIds)) return;

        $questions = [];
        $now = now();

        for ($i = 0; $i < 300; $i++) {
            $tpl = $this->questionTemplates[$i % count($this->questionTemplates)];
            $q = str_replace(
                ['{a}', '{b}', '{topic}', '{word}'],
                [rand(1, 100), rand(1, 100), $this->topics[array_rand($this->topics)], 'kata' . rand(1, 50)],
                $tpl
            );
            $questions[] = [
                'question_bank_id' => $bankIds[array_rand($bankIds)],
                'question_text' => $q,
                'question_type' => ['multiple_choice', 'true_false'][rand(0, 1)],
                'points' => rand(1, 3),
                'order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($questions, 100) as $chunk) {
            BankQuestion::insert($chunk);
        }
    }
}
