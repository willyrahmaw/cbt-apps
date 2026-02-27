<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    private array $templates = [
        'Berapakah hasil dari {a} + {b}?',
        'Jika {a} dikurangi {b}, hasilnya adalah?',
        'Manakah yang merupakan sifat dari {topic}?',
        'Fungsi utama dari {topic} adalah...',
        'Berdasarkan bacaan, mengapa {topic} penting?',
        'Simpulan paragraf di atas adalah...',
        'Pernyataan yang sesuai dengan teks adalah...',
        'Makna kata "{word}" dalam kalimat tersebut...',
        'Prinsip kerja {topic} berdasarkan teks...',
        'Dari data, kesimpulan yang tepat ialah...',
    ];

    private array $topics = ['fotosintesis', 'gravitasi', 'reaksi kimia', 'ekosistem', 'demokrasi', 'pasar', 'sejarah', 'algoritma'];

    public function run(): void
    {
        $examIds = Exam::pluck('id')->toArray();
        if (empty($examIds)) return;

        $questions = [];
        $now = now();
        $order = 0;

        foreach ($examIds as $eid) {
            $count = rand(8, 15);
            for ($i = 0; $i < $count; $i++) {
                $order++;
                $tpl = $this->templates[$order % count($this->templates)];
                $q = str_replace(
                    ['{a}', '{b}', '{topic}', '{word}'],
                    [rand(1, 50), rand(1, 50), $this->topics[array_rand($this->topics)], 'istilah'],
                    $tpl
                );
                $questions[] = [
                    'exam_id' => $eid,
                    'question_text' => $q,
                    'question_type' => 'multiple_choice',
                    'points' => rand(1, 3),
                    'order' => $i + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($questions, 200) as $chunk) {
            Question::insert($chunk);
        }
    }
}
