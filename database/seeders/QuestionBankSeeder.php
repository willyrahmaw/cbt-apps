<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id')->toArray();
        $pembuatIds = User::where('role', 'pembuat_soal')->pluck('id')->toArray();
        if (empty($pembuatIds)) return;

        $banks = [];
        $subjects = ['Matematika', 'Fisika', 'Kimia', 'Biologi', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'TIK', 'Sejarah'];
        $now = now();

        for ($i = 0; $i < 40; $i++) {
            $subj = $subjects[$i % count($subjects)];
            $banks[] = [
                'name' => "Bank Soal {$subj} " . (intdiv($i, count($subjects)) + 1),
                'category_id' => $categories[array_rand($categories)],
                'description' => "Kumpulan soal {$subj}",
                'created_by' => $pembuatIds[array_rand($pembuatIds)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($banks, 20) as $chunk) {
            QuestionBank::insert($chunk);
        }
    }
}
