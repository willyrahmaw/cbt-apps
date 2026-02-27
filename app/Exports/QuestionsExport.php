<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class QuestionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(protected Exam $exam)
    {
    }

    public function title(): string
    {
        return 'Soal';
    }

    public function headings(): array
    {
        return ['pertanyaan', 'tipe', 'poin', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban_benar', 'gambar'];
    }

    public function collection()
    {
        return $this->exam->questions()->with('answers')->orderBy('order')->get();
    }

    public function map($q): array
    {
        $type = match ($q->question_type) {
            'true_false' => 'benar_salah',
            'essay' => 'essai',
            default => 'pilihan_ganda',
        };

        $ansArr = $q->answers->values()->all();
        $correctIdx = collect($q->answers)->search(fn ($a) => $a->is_correct);
        $correctKey = $correctIdx !== false ? chr(65 + $correctIdx) : 'A';

        return [
            $q->question_text,
            $type,
            $q->points,
            ($ansArr[0] ?? null)?->answer_text ?? '',
            ($ansArr[1] ?? null)?->answer_text ?? '',
            ($ansArr[2] ?? null)?->answer_text ?? '',
            ($ansArr[3] ?? null)?->answer_text ?? '',
            ($ansArr[4] ?? null)?->answer_text ?? '',
            $correctKey,
            $q->question_image ? asset('storage/' . $q->question_image) : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();
        $sheet->getStyle("A1:J1")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A1:J{$last}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
        ]);
        return [];
    }
}
