<?php

namespace App\Exports;

use App\Models\QuestionBank;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BankQuestionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(protected QuestionBank $bank)
    {
    }

    public function title(): string
    {
        return 'Soal';
    }

    public function headings(): array
    {
        return ['pertanyaan', 'tipe', 'poin', 'tag', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban_benar', 'gambar'];
    }

    public function collection()
    {
        return $this->bank->questions()->with('answers')->orderBy('order')->get();
    }

    public function map($bq): array
    {
        $type = match ($bq->question_type) {
            'true_false' => 'benar_salah',
            'essay' => 'essai',
            default => 'pilihan_ganda',
        };

        $ansArr = $bq->answers->values()->all();
        $correctIdx = collect($bq->answers)->search(fn ($a) => $a->is_correct);
        $correctKey = $correctIdx !== false ? chr(65 + $correctIdx) : 'A';

        $tags = is_array($bq->tags) ? implode(', ', $bq->tags) : '';

        return [
            $bq->question_text,
            $type,
            $bq->points,
            $tags,
            ($ansArr[0] ?? null)?->answer_text ?? '',
            ($ansArr[1] ?? null)?->answer_text ?? '',
            ($ansArr[2] ?? null)?->answer_text ?? '',
            ($ansArr[3] ?? null)?->answer_text ?? '',
            ($ansArr[4] ?? null)?->answer_text ?? '',
            $correctKey,
            $bq->question_image ? asset('storage/' . $bq->question_image) : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();
        $sheet->getStyle("A1:K1")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A1:K{$last}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
        ]);
        return [];
    }
}
