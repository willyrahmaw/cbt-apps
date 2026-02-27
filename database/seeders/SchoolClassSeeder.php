<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $grades = ['X', 'XI', 'XII'];
        $jurusans = ['IPA', 'IPS', 'Bahasa'];
        $classes = [];

        foreach ($grades as $grade) {
            foreach ($jurusans as $jurusan) {
                for ($i = 1; $i <= 4; $i++) {
                    $classes[] = [
                        'name' => "{$grade} {$jurusan} {$i}",
                        'grade_level' => $grade === 'X' ? 'Kelas 10' : ($grade === 'XI' ? 'Kelas 11' : 'Kelas 12'),
                        'academic_year' => '2024/2025',
                        'description' => "Kelas {$grade} {$jurusan} {$i}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Additional classes to reach ~50
        foreach (['X', 'XI', 'XII'] as $g) {
            $classes[] = [
                'name' => "{$g} IPA 5",
                'grade_level' => $g === 'X' ? 'Kelas 10' : ($g === 'XI' ? 'Kelas 11' : 'Kelas 12'),
                'academic_year' => '2024/2025',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($classes, 50) as $chunk) {
            SchoolClass::insert($chunk);
        }
    }
}
