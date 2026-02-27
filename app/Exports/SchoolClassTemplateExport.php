<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchoolClassTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Kelas';
    }

    public function headings(): array
    {
        return ['nama', 'tingkat', 'tahun_ajaran', 'deskripsi'];
    }

    public function array(): array
    {
        return [
            ['X IPA 1', 'Kelas 10', '2025/2026', 'Kelas IPA angkatan 2025'],
            ['X IPA 2', 'Kelas 10', '2025/2026', 'Kelas IPA angkatan 2025'],
            ['X IPS 1', 'Kelas 10', '2025/2026', 'Kelas IPS angkatan 2025'],
            ['XI IPA 1', 'Kelas 11', '2025/2026', ''],
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
        return ['A' => 20, 'B' => 15, 'C' => 15, 'D' => 35];
    }
}
