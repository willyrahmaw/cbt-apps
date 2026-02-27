<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Matematika', 'description' => 'Soal matematika'],
            ['name' => 'Fisika', 'description' => 'Soal fisika'],
            ['name' => 'Kimia', 'description' => 'Soal kimia'],
            ['name' => 'Biologi', 'description' => 'Soal biologi'],
            ['name' => 'Bahasa Indonesia', 'description' => 'Soal bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'description' => 'English'],
            ['name' => 'IPA', 'description' => 'Ilmu Pengetahuan Alam'],
            ['name' => 'IPS', 'description' => 'Ilmu Pengetahuan Sosial'],
            ['name' => 'TIK', 'description' => 'Teknologi Informasi'],
            ['name' => 'Sejarah', 'description' => 'Sejarah Indonesia'],
            ['name' => 'Ekonomi', 'description' => 'Ekonomi'],
            ['name' => 'PPKn', 'description' => 'Pendidikan Pancasila'],
        ];

        foreach ($categories as $c) {
            Category::create(array_merge($c, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
