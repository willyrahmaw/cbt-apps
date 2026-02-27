<?php

namespace App\Imports;

use App\Models\BankAnswer;
use App\Models\BankQuestion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankQuestionsImport implements ToArray, WithHeadingRow
{
    protected int $bankId;
    protected int $imported = 0;
    protected array $errors = [];

    public function __construct(int $bankId)
    {
        $this->bankId = $bankId;
    }

    public function array(array $rows): void
    {
        $order = BankQuestion::where('question_bank_id', $this->bankId)->max('order') ?? 0;

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
            $tagsRaw = trim($row['tag'] ?? $row['tags'] ?? '');
            $tags = $tagsRaw ? array_map('trim', array_filter(explode(',', $tagsRaw))) : null;

            $imagePath = null;
            $imageUrl = trim($row['gambar'] ?? '');
            if (!empty($imageUrl)) {
                if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    $imagePath = $this->downloadImage($imageUrl, $rowNum);
                    if (!$imagePath) {
                        $imagePath = $imageUrl;
                    }
                } else {
                    $this->errors[] = "Baris {$rowNum}: Kolom gambar bukan URL yang valid, gunakan format https://...";
                }
            }

            $order++;
            $bq = BankQuestion::create([
                'question_bank_id' => $this->bankId,
                'question_text' => $questionText,
                'question_type' => $questionType,
                'question_image' => $imagePath,
                'points' => $points,
                'order' => $order,
                'tags' => $tags,
            ]);

            if ($questionType === 'essay') {
                // no answers
            } elseif ($questionType === 'true_false') {
                BankAnswer::create(['bank_question_id' => $bq->id, 'answer_text' => 'Benar', 'is_correct' => in_array($correctKey, ['A', 'BENAR', 'TRUE', 'B']), 'order' => 0]);
                BankAnswer::create(['bank_question_id' => $bq->id, 'answer_text' => 'Salah', 'is_correct' => in_array($correctKey, ['B', 'SALAH', 'FALSE', 'S']), 'order' => 1]);
            } else {
                $options = ['A' => trim($row['opsi_a'] ?? ''), 'B' => trim($row['opsi_b'] ?? ''), 'C' => trim($row['opsi_c'] ?? ''), 'D' => trim($row['opsi_d'] ?? ''), 'E' => trim($row['opsi_e'] ?? '')];
                if (count(array_filter($options)) < 2) {
                    $bq->delete();
                    $this->errors[] = "Baris {$rowNum}: Minimal 2 opsi jawaban, dilewati.";
                    continue;
                }
                $oi = 0;
                foreach ($options as $key => $text) {
                    if (empty($text)) continue;
                    BankAnswer::create(['bank_question_id' => $bq->id, 'answer_text' => $text, 'is_correct' => $key === $correctKey, 'order' => $oi++]);
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

            $ct = $response->header('Content-Type');
            if (!$ct || !str_contains($ct, 'image')) {
                $this->errors[] = "Baris {$rowNum}: URL tidak mengembalikan file gambar langsung (Content-Type: {$ct}).";
                return null;
            }

            $ext = str_contains($ct, 'png') ? 'png'
                : (str_contains($ct, 'gif') ? 'gif'
                : (str_contains($ct, 'webp') ? 'webp' : 'jpg'));

            $path = 'bank-questions/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $response->body());
            return $path;
        } catch (\Exception $e) {
            $this->errors[] = "Baris {$rowNum}: Gagal download gambar.";
            return null;
        }
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getErrors(): array { return $this->errors; }
}
