<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Answer;
use App\Models\QuestionBank;
use App\Models\BankQuestion;
use App\Models\BankAnswer;
use App\Models\ExamSession;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backup.index');
    }

    public function download(Request $request)
    {
        $request->validate(['scope' => 'nullable|in:all,exams_only']);

        $scope = $request->get('scope', 'all');

        $data = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'scope' => $scope,
        ];

        $data['categories'] = Category::all()->toArray();
        $data['exams'] = Exam::with(['questions.answers', 'schoolClasses'])->get()->map(function ($exam) {
            return [
                'title' => $exam->title,
                'description' => $exam->description,
                'category_name' => $exam->category?->name,
                'duration' => $exam->duration,
                'passing_score' => $exam->passing_score,
                'shuffle_questions' => $exam->shuffle_questions,
                'shuffle_answers' => $exam->shuffle_answers,
                'show_result' => $exam->show_result,
                'start_time' => $exam->start_time?->toIso8601String(),
                'end_time' => $exam->end_time?->toIso8601String(),
                'class_names' => $exam->schoolClasses->pluck('name')->toArray(),
                'questions' => $exam->questions->map(function ($q) {
                    return [
                        'question_text' => $q->question_text,
                        'question_type' => $q->question_type,
                        'points' => $q->points,
                        'question_image_path' => $q->question_image,
                        'answers' => $q->answers->map(fn($a) => [
                            'answer_text' => $a->answer_text,
                            'is_correct' => $a->is_correct,
                            'order' => $a->order,
                        ])->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();

        if ($scope === 'all') {
            $data['question_banks'] = QuestionBank::with(['questions.answers', 'category'])->get()->map(function ($bank) {
                return [
                    'name' => $bank->name,
                    'category_name' => $bank->category?->name,
                    'description' => $bank->description,
                    'questions' => $bank->questions->map(function ($q) {
                        return [
                            'question_text' => $q->question_text,
                            'question_type' => $q->question_type,
                            'points' => $q->points,
                            'tags' => $q->tags,
                            'question_image_path' => $q->question_image,
                            'answers' => $q->answers->map(fn($a) => [
                                'answer_text' => $a->answer_text,
                                'is_correct' => $a->is_correct,
                                'order' => $a->order,
                            ])->toArray(),
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        $filename = 'cbt-backup-' . now()->format('Y-m-d-His') . '.json';
        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function downloadDatabase()
    {
        $driver = config('database.default');
        $filename = 'cbt-database-' . now()->format('Y-m-d-His');

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $db = config('database.connections.' . $driver);
            $output = $this->mysqlDumpViaPhp($db, $driver);
            if ($output === false) {
                return back()->with('error', 'Gagal koneksi ke database.');
            }
            $filename .= '.sql';
            return response($output, 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($driver === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            if (!file_exists($path)) {
                return back()->with('error', 'File database SQLite tidak ditemukan.');
            }
            $filename .= '.sqlite';
            return response()->download($path, $filename, ['Content-Type' => 'application/x-sqlite3']);
        }

        return back()->with('error', 'Backup database belum didukung untuk driver: ' . $driver);
    }

    private function mysqlDumpViaPhp(array $db, string $driver): string|false
    {
        try {
            $pdo = new \PDO(
                sprintf('%s:host=%s;port=%s;dbname=%s;charset=utf8mb4', $driver === 'mariadb' ? 'mysql' : $driver, $db['host'], $db['port'] ?? 3306, $db['database']),
                $db['username'],
                $db['password'] ?? ''
            );
        } catch (\PDOException $e) {
            return false;
        }

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $out = "-- CBT Database Backup\n-- " . now()->toIso8601String() . "\n\n";
        $out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out .= $create[1] . ";\n\n";
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $cols = array_keys($rows[0]);
                $colsStr = implode('`, `', $cols);
                foreach ($rows as $row) {
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), array_values($row));
                    $out .= "INSERT INTO `{$table}` (`{$colsStr}`) VALUES (" . implode(', ', $vals) . ");\n";
                }
                $out .= "\n";
            }
        }
        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }

    public function restore(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:json,txt|max:10240']);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'File JSON tidak valid.');
        }

        if (empty($data['version']) || empty($data['exams'])) {
            return back()->with('error', 'Format backup tidak valid.');
        }

        $categoryMap = [];
        $classMap = [];

        DB::beginTransaction();
        try {
            if (!empty($data['categories'])) {
                foreach ($data['categories'] as $c) {
                    $cat = Category::firstOrCreate(
                        ['name' => $c['name']],
                        ['description' => $c['description'] ?? '', 'icon' => $c['icon'] ?? '']
                    );
                    $categoryMap[$c['name']] = $cat->id;
                }
            }

            $classes = \App\Models\SchoolClass::all()->keyBy('name');

            foreach ($data['exams'] as $examData) {
                $catName = $examData['category_name'] ?? '';
                $categoryId = $categoryMap[$catName] ?? Category::first()?->id ?? 1;

                $exam = Exam::create([
                    'title' => $examData['title'] . ' (Restore ' . now()->format('d/m H:i') . ')',
                    'description' => $examData['description'] ?? null,
                    'category_id' => $categoryId,
                    'created_by' => auth()->id(),
                    'duration' => $examData['duration'] ?? 60,
                    'passing_score' => $examData['passing_score'] ?? 60,
                    'token' => strtoupper(\Illuminate\Support\Str::random(6)),
                    'shuffle_questions' => $examData['shuffle_questions'] ?? false,
                    'shuffle_answers' => $examData['shuffle_answers'] ?? false,
                    'show_result' => $examData['show_result'] ?? true,
                    'is_active' => false,
                ]);

                $classIds = [];
                foreach ($examData['class_names'] ?? [] as $className) {
                    $cls = $classes->get($className);
                    if ($cls) $classIds[] = $cls->id;
                }
                if (!empty($classIds)) {
                    $exam->schoolClasses()->sync($classIds);
                }

                foreach ($examData['questions'] ?? [] as $i => $qData) {
                    $imagePath = null;
                    $srcPath = $qData['question_image_path'] ?? null;
                    if (!empty($srcPath) && $this->isSafeStoragePath($srcPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($srcPath)) {
                        $ext = pathinfo($srcPath, PATHINFO_EXTENSION) ?: 'jpg';
                        $imagePath = 'questions/' . \Illuminate\Support\Str::uuid() . '.' . $ext;
                        \Illuminate\Support\Facades\Storage::disk('public')->copy($srcPath, $imagePath);
                    }

                    $question = $exam->questions()->create([
                        'question_text' => $qData['question_text'] ?? '',
                        'question_type' => $qData['question_type'] ?? 'multiple_choice',
                        'points' => $qData['points'] ?? 1,
                        'question_image' => $imagePath,
                        'order' => $i,
                    ]);

                    foreach ($qData['answers'] ?? [] as $ai => $aData) {
                        $question->answers()->create([
                            'answer_text' => $aData['answer_text'] ?? '',
                            'is_correct' => $aData['is_correct'] ?? false,
                            'order' => $aData['order'] ?? $ai,
                        ]);
                    }
                }
            }

            if (!empty($data['question_banks'])) {
                foreach ($data['question_banks'] as $bankData) {
                    $catName = $bankData['category_name'] ?? '';
                    $categoryId = $categoryMap[$catName] ?? Category::first()?->id ?? 1;

                    $bank = QuestionBank::create([
                        'name' => $bankData['name'] . ' (Restore)',
                        'category_id' => $categoryId,
                        'description' => $bankData['description'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    foreach ($bankData['questions'] ?? [] as $i => $qData) {
                        $imagePath = null;
                        $srcPath = $qData['question_image_path'] ?? null;
                        if (!empty($srcPath) && $this->isSafeStoragePath($srcPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($srcPath)) {
                            $ext = pathinfo($srcPath, PATHINFO_EXTENSION) ?: 'jpg';
                            $imagePath = 'bank-questions/' . \Illuminate\Support\Str::uuid() . '.' . $ext;
                            \Illuminate\Support\Facades\Storage::disk('public')->copy($srcPath, $imagePath);
                        }

                        $bq = $bank->questions()->create([
                            'question_text' => $qData['question_text'] ?? '',
                            'question_type' => $qData['question_type'] ?? 'multiple_choice',
                            'points' => $qData['points'] ?? 1,
                            'question_image' => $imagePath,
                            'tags' => $qData['tags'] ?? null,
                            'order' => $i,
                        ]);

                        foreach ($qData['answers'] ?? [] as $ai => $aData) {
                            $bq->answers()->create([
                                'answer_text' => $aData['answer_text'] ?? '',
                                'is_correct' => $aData['is_correct'] ?? false,
                                'order' => $aData['order'] ?? $ai,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Backup berhasil dipulihkan. Ujian dan bank soal yang di-restore ditambahkan dengan status Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal restore: ' . $e->getMessage());
        }
    }

    /**
     * Validasi path aman untuk mencegah path traversal (../../../).
     */
    private function isSafeStoragePath(?string $path): bool
    {
        if (empty($path) || str_contains($path, '..')) {
            return false;
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $allowedPrefixes = ['questions/', 'bank-questions/'];
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix) || $path === rtrim($prefix, '/')) {
                return true;
            }
        }
        return false;
    }
}
