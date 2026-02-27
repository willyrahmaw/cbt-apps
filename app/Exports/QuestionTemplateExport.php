<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class QuestionTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Soal';
    }

    public function headings(): array
    {
        return [
            'pertanyaan',
            'tipe',
            'poin',
            'opsi_a',
            'opsi_b',
            'opsi_c',
            'opsi_d',
            'opsi_e',
            'jawaban_benar',
            'gambar',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Berapakah hasil dari 2 + 2?',
                'pilihan_ganda',
                1,
                '3',
                '4',
                '5',
                '6',
                '',
                'B',
                '',
            ],
            [
                'Indonesia adalah negara kepulauan terbesar di dunia',
                'benar_salah',
                1,
                '',
                '',
                '',
                '',
                '',
                'A',
                '',
            ],
            [
                'Jelaskan proses terjadinya hujan!',
                'essai',
                5,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                'Siapakah presiden pertama Indonesia?',
                'pilihan_ganda',
                2,
                'Soekarno',
                'Soeharto',
                'Habibie',
                'Megawati',
                'Jokowi',
                'A',
                'https://contoh.com/gambar-soal.jpg',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 16,
            'C' => 8,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 16,
            'J' => 35,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:J1")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A2:J{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF9C3'],
            ],
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '92400E'],
                'size' => 10,
            ],
        ]);

        $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->setSelectedCell('A' . ($lastRow + 1));

        $sheet->getComment('A1')->getText()->createTextRun("Tulis pertanyaan di kolom ini");
        $sheet->getComment('B1')->getText()->createTextRun("Isi: pilihan_ganda, benar_salah, atau essai");
        $sheet->getComment('C1')->getText()->createTextRun("Bobot poin soal (angka). Untuk essai, poin = skor maksimal");
        $sheet->getComment('D1')->getText()->createTextRun("Opsi jawaban A (kosongkan untuk tipe essai)");
        $sheet->getComment('I1')->getText()->createTextRun("Huruf jawaban benar: A, B, C, D, atau E. Untuk benar_salah: A=Benar, B=Salah. Kosongkan untuk essai");
        $sheet->getComment('J1')->getText()->createTextRun("Opsional. Isi URL gambar (https://...) atau kosongkan");

        return [];
    }
}
