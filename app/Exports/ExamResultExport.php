<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamResultExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected Exam $exam;
    protected array $filters;
    protected int $row = 0;
    protected int $totalSessions = 0;
    protected int $passed = 0;
    protected int $failed = 0;
    protected int $pending = 0;
    protected float $avgScore = 0.0;

    public function __construct(Exam $exam, array $filters = [])
    {
        $this->exam = $exam;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = $this->exam->examSessions()
            ->with(['user.schoolClass'])
            ->where('status', 'completed');

        if (isset($this->filters['search']) && $this->filters['search']) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', "%{$this->filters['search']}%")
                  ->orWhere('email', 'like', "%{$this->filters['search']}%");
            });
        }

        if (isset($this->filters['class_id']) && $this->filters['class_id']) {
            $query->whereHas('user', function($q) {
                $q->where('school_class_id', $this->filters['class_id']);
            });
        }

        $sessions = $query->orderBy('score', 'desc')->get();

        // Hitung ringkasan seperti di PDF
        $this->totalSessions = $sessions->count();
        $this->passed = $sessions->where('needs_grading', false)
            ->filter(fn($s) => $s->score >= $this->exam->passing_score)->count();
        $this->failed = $sessions->where('needs_grading', false)
            ->filter(fn($s) => $s->score < $this->exam->passing_score)->count();
        $this->pending = $sessions->where('needs_grading', true)->count();
        $this->avgScore = $this->totalSessions > 0 ? round((float) $sessions->avg('score'), 1) : 0.0;

        return $sessions;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Peserta',
            'NIS',
            'Email',
            'Kelas',
            'Skor',
            'Jawaban Benar',
            'Total Soal',
            'Status',
            'Waktu Mulai',
            'Waktu Selesai',
        ];
    }

    public function map($session): array
    {
        $this->row++;
        $passed = $session->score >= $this->exam->passing_score;

        $status = 'Lulus';
        if ($session->needs_grading) {
            $status = 'Menunggu Penilaian';
        } elseif (!$passed) {
            $status = 'Tidak Lulus';
        }

        return [
            $this->row,
            $session->user->name,
            $session->user->nis,
            $session->user->email,
            $session->user->schoolClass?->name ?? '-',
            $session->score,
            $session->correct_answers,
            $session->total_questions,
            $status,
            $session->started_at->format('d/m/Y H:i'),
            $session->finished_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->row + 1;

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(16); // NIS
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']],
            ],
            "A1:K{$lastRow}" => [
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D1D5DB']],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Hasil Ujian';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Sisipkan beberapa baris di atas tabel untuk header & ringkasan
                $sheet->insertNewRowBefore(1, 9);

                // Title
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'Laporan Hasil Ujian');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('4F46E5');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Sub title (sesuai PDF)
                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue('A2', 'CBT App - Computer Based Test');
                $sheet->getStyle('A2')->getFont()->setSize(11)->getColor()->setRGB('64748B');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Info ujian (kiri)
                $sheet->setCellValue('A4', 'Nama Ujian');
                $sheet->setCellValue('B4', ': ' . $this->exam->title);
                $sheet->setCellValue('A5', 'Kategori');
                $sheet->setCellValue('B5', ': ' . ($this->exam->category->name ?? '-'));
                $sheet->setCellValue('A6', 'Durasi');
                $sheet->setCellValue('B6', ': ' . $this->exam->duration . ' menit');
                $sheet->setCellValue('A7', 'KKM (Passing Score)');
                $sheet->setCellValue('B7', ': ' . $this->exam->passing_score);
                $sheet->setCellValue('A8', 'Jumlah Peserta');
                $sheet->setCellValue('B8', ': ' . $this->totalSessions);
                $sheet->setCellValue('A9', 'Tanggal Cetak');
                $sheet->setCellValue('B9', ': ' . now()->format('d F Y, H:i'));

                // Ringkasan (kanan) – mirip blok summary di PDF
                $sheet->setCellValue('G4', 'Ringkasan');
                $sheet->getStyle('G4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));

                $sheet->setCellValue('G5', 'Total Peserta');
                $sheet->setCellValue('H5', $this->totalSessions);
                $sheet->setCellValue('G6', 'Lulus');
                $sheet->setCellValue('H6', $this->passed);
                $sheet->setCellValue('G7', 'Tidak Lulus');
                $sheet->setCellValue('H7', $this->failed);
                $sheet->setCellValue('G8', 'Menunggu Penilaian');
                $sheet->setCellValue('H8', $this->pending);
                $sheet->setCellValue('G9', 'Rata-rata Skor');
                $sheet->setCellValue('H9', $this->avgScore);

                $sheet->getStyle('G5:H9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Sedikit formatting label
                $sheet->getStyle('A4:A9')->getFont()->setBold(true)->getColor()->setRGB('475569');
                $sheet->getStyle('G5:G9')->getFont()->setBold(true)->getColor()->setRGB('64748B');

                // Header kelompok "Detail Hasil Ujian" di atas tabel
                // Saat ini header kolom berada di baris 10 (setelah sisipan 9 baris di atas)
                // Kita geser ke bawah satu baris dan pakai baris 10 untuk judul kelompok.
                $sheet->insertNewRowBefore(10, 1);
                $sheet->mergeCells('A10:K10');
                $sheet->setCellValue('A10', 'Detail Hasil Ujian');
                $sheet->getStyle('A10')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('475569');
                $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
