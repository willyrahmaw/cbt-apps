<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SchoolClassesImport implements ToCollection, WithHeadingRow
{
    protected int $imported = 0;
    protected array $skipped = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $name = trim($row['nama'] ?? $row['name'] ?? '');
            if (empty($name)) {
                $this->skipped[] = "Baris {$rowNum}: Nama kelas kosong, dilewati.";
                continue;
            }

            if (SchoolClass::where('name', $name)->exists()) {
                $this->skipped[] = "Baris {$rowNum}: Kelas \"{$name}\" sudah ada.";
                continue;
            }

            $academicYear = trim($row['tahun_ajaran'] ?? $row['academic_year'] ?? '') ?: Setting::getAcademicYear();
            SchoolClass::create([
                'name' => $name,
                'grade_level' => trim($row['tingkat'] ?? $row['grade_level'] ?? '') ?: null,
                'academic_year' => $academicYear,
                'description' => trim($row['deskripsi'] ?? $row['description'] ?? '') ?: null,
            ]);

            $this->imported++;
        }
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
