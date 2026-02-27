<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class UsersImport implements ToCollection, WithHeadingRow
{
    protected int $imported = 0;
    protected array $skipped = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $name = trim($row['nama'] ?? $row['name'] ?? '');
            $email = trim($row['email'] ?? '');
            $nis = trim($row['nis'] ?? '');
            if (empty($name) || empty($email)) {
                $this->skipped[] = "Baris {$rowNum}: Nama atau email kosong, dilewati.";
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $this->skipped[] = "Baris {$rowNum}: Email {$email} sudah terdaftar.";
                continue;
            }

            if (!empty($nis) && User::where('nis', $nis)->exists()) {
                $this->skipped[] = "Baris {$rowNum}: NIS {$nis} sudah terdaftar.";
                continue;
            }

            $password = trim($row['password'] ?? '');
            if (empty($password)) {
                $password = 'password';
            }

            $role = $this->normalizeRole(trim($row['role'] ?? 'pengguna'));
            $schoolClassId = null;

            if ($role === 'pengguna') {
                $kelas = trim($row['kelas'] ?? $row['class'] ?? '');
                if (!empty($kelas)) {
                    $class = SchoolClass::where('name', $kelas)->first();
                    $schoolClassId = $class?->id;
                }
            }

            User::create([
                'name' => $name,
                'nis' => $nis ?: null,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => $role,
                'school_class_id' => $schoolClassId,
            ]);

            $this->imported++;
        }
    }

    protected function normalizeRole(string $value): string
    {
        $value = strtolower($value);
        return match ($value) {
            'admin', 'superadmin' => 'superadmin',
            'guru', 'pembuat', 'pembuat_soal', 'creator' => 'pembuat_soal',
            'siswa', 'pengguna', 'user' => 'pengguna',
            default => 'pengguna',
        };
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkipped(): array
    {
        return $this->skipped;
    }
}
