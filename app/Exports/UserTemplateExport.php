<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'User';
    }

    public function headings(): array
    {
        return ['nama', 'email', 'password', 'role', 'kelas', 'nis'];
    }

    public function array(): array
    {
        return [
            ['Ahmad Rizki', 'ahmad@example.com', 'password123', 'pengguna', 'X IPA 1', '10001'],
            ['Budi Santoso', 'budi@example.com', 'password123', 'pengguna', 'X IPA 1', '10002'],
            ['Siti Nurhaliza', 'siti@example.com', 'password123', 'pengguna', 'X IPA 2', '10003'],
            ['Guru Matematika', 'guru@example.com', 'password123', 'pembuat_soal', '', ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E0E7FF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 30,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }
}
