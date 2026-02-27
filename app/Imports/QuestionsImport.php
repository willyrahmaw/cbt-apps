<?php

namespace App\Imports;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToArray, WithHeadingRow
{
    protected int $examId;
    protected int $imported = 0;
    protected array $errors = [];

    public function __construct(int $examId)
    {
        $this->examId = $examId;
    }

    public function array(array $rows): void
    {
        $order = Question::where('exam_id', $this->examId)->max('order') ?? 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            $questionText = trim($row['pertanyaan'] ?? '');
            if (empty($questionText)) {
                $this->errors[] = "Baris {$rowNum}: Pertanyaan kosong, dilewati.";
                continue;
            }

            $type = strtolower(trim($row['tipe'] ?? 'pilihan_ganda'));
            $questionType = match ($type) {
                'benar_salah', 'true_false', 'bs' => 'true_false',
                'essai', 'essay', 'uraian' => 'essay',
                default => 'multiple_choice',
            };

            $points = max(1, intval($row['poin'] ?? 1));
            $correctKey = strtoupper(trim($row['jawaban_benar'] ?? 'A'));

            $imagePath = null;
            $imageUrl = trim($row['gambar'] ?? '');
            if (!empty($imageUrl)) {
                if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    $imagePath = $this->downloadImage($imageUrl, $rowNum);
                    // Jika gagal di-download (misalnya server tidak bisa mengakses internet),
                    // simpan saja URL mentah agar gambar tetap bisa ditampilkan di browser.
                    if (!$imagePath) {
                        $imagePath = $imageUrl;
                    }
                } else {
                    $this->errors[] = "Baris {$rowNum}: Kolom gambar bukan URL yang valid, gunakan format https://...";
                }
            }

            $order++;
            $question = Question::create([
                'exam_id' => $this->examId,
                'question_text' => $questionText,
                'question_type' => $questionType,
                'question_image' => $imagePath,
                'points' => $points,
                'order' => $order,
            ]);

            if ($questionType === 'essay') {
                // Essay: no answer options needed
            } elseif ($questionType === 'true_false') {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => 'Benar',
                    'is_correct' => in_array($correctKey, ['A', 'BENAR', 'TRUE', 'B']),
                    'order' => 0,
                ]);
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => 'Salah',
                    'is_correct' => in_array($correctKey, ['B', 'SALAH', 'FALSE', 'S']),
                    'order' => 1,
                ]);
            } else {
                $options = [
                    'A' => trim($row['opsi_a'] ?? ''),
                    'B' => trim($row['opsi_b'] ?? ''),
                    'C' => trim($row['opsi_c'] ?? ''),
                    'D' => trim($row['opsi_d'] ?? ''),
                    'E' => trim($row['opsi_e'] ?? ''),
                ];

                $hasAtLeastTwo = count(array_filter($options)) >= 2;
                if (!$hasAtLeastTwo) {
                    $question->delete();
                    $this->errors[] = "Baris {$rowNum}: Minimal 2 opsi jawaban harus diisi, dilewati.";
                    continue;
                }

                $orderIdx = 0;
                foreach ($options as $key => $text) {
                    if (empty($text)) continue;

                    Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $text,
                        'is_correct' => $key === $correctKey,
                        'order' => $orderIdx++,
                    ]);
                }
            }

            $this->imported++;
        }
    }

    private function downloadImage(string $url, int $rowNum): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                $this->errors[] = "Baris {$rowNum}: Gagal download gambar (HTTP {$response->status()}).";
                return null;
            }

            $contentType = $response->header('Content-Type');
            if (!$contentType || !str_contains($contentType, 'image')) {
                $this->errors[] = "Baris {$rowNum}: URL tidak mengembalikan file gambar langsung (Content-Type: {$contentType}).";
                return null;
            }

            $extension = match (true) {
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'gif') => 'gif',
                str_contains($contentType, 'webp') => 'webp',
                default => 'jpg',
            };

            $filename = 'questions/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Exception $e) {
            $this->errors[] = "Baris {$rowNum}: Gagal download gambar ({$e->getMessage()}).";
            return null;
        }
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
