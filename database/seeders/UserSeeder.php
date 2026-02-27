<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // Superadmin & Pembuat Soal
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@cbt.test',
            'password' => $password,
            'role' => 'superadmin',
        ]);

        $classId = \App\Models\SchoolClass::first()?->id;
        User::create([
            'name' => 'Siswa Demo',
            'email' => 'siswa@cbt.test',
            'password' => $password,
            'role' => 'pengguna',
            'school_class_id' => $classId,
        ]);

        $pembuatIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $u = User::create([
                'name' => "Pembuat Soal {$i}",
                'email' => "pembuat{$i}@cbt.test",
                'password' => $password,
                'role' => 'pembuat_soal',
            ]);
            $pembuatIds[] = $u->id;
        }

        // Pengguna (siswa) - 1200 users
        $classIds = \App\Models\SchoolClass::pluck('id')->toArray();
        $now = now();

        $chunks = [];
        for ($i = 1; $i <= 1200; $i++) {
            $chunks[] = [
                'name' => "Siswa {$i}",
                'email' => "siswa{$i}@cbt.test",
                'password' => $password,
                'role' => 'pengguna',
                'school_class_id' => $classIds[array_rand($classIds)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($chunks, 200) as $chunk) {
            User::insert($chunk);
        }
    }
}
