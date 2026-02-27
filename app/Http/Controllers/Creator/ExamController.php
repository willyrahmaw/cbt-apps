<?php

namespace App\Http\Controllers\Creator;

use App\Exports\ExamResultExport;
use App\Exports\QuestionsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::where('created_by', auth()->id())
            ->with(['category', 'questions', 'schoolClasses'])
            ->withCount('questions')
            ->latest()
            ->paginate(15);

        return view('creator.exams.index', compact('exams'));
    }

    public function create()
    {
        $categories = Category::all();
        $classes = SchoolClass::orderBy('name')->get();
        return view('creator.exams.create', compact('categories', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'duration' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'start_time' => 'nullable|date',
            'end_time' => ['nullable', 'date', function ($attr, $val, $fail) use ($request) {
                if ($request->filled('start_time') && $val && $val < $request->start_time) {
                    $fail('Waktu akhir harus setelah atau sama dengan waktu mulai.');
                }
            }],
        ]);

        $exam = Exam::create([
            ...$request->only('title', 'description', 'category_id', 'duration', 'passing_score'),
            'created_by' => auth()->id(),
            'token' => strtoupper(\Illuminate\Support\Str::random(6)),
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_answers' => $request->boolean('shuffle_answers'),
            'show_result' => $request->boolean('show_result', true),
            'start_time' => $request->filled('start_time') ? $request->start_time : null,
            'end_time' => $request->filled('end_time') ? $request->end_time : null,
        ]);

        if ($request->has('class_ids')) {
            $exam->schoolClasses()->sync($request->class_ids);
        }

        AuditLog::log('created', 'Exam', $exam->id, "Membuat ujian {$exam->title}", null, ['title' => $exam->title, 'duration' => $exam->duration]);

        return redirect()->route('creator.exams.questions', $exam)->with('success', 'Ujian berhasil dibuat. Silakan tambahkan soal.');
    }

    public function edit(Exam $exam)
    {
        $this->authorizeExam($exam);
        $categories = Category::all();
        $classes = SchoolClass::orderBy('name')->get();
        $exam->load('schoolClasses');
        return view('creator.exams.edit', compact('exam', 'categories', 'classes'));
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'duration' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,id',
            'terminate_on_events' => 'nullable|array',
            'terminate_on_events.*' => 'in:tab_switch,right_click,copy_attempt,paste_attempt,rate_limit,time_up_attempt,split_screen,window_blur,fullscreen_exit',
            'start_time' => 'nullable|date',
            'end_time' => ['nullable', 'date', function ($attr, $val, $fail) use ($request) {
                if ($request->filled('start_time') && $val && $val < $request->start_time) {
                    $fail('Waktu akhir harus setelah atau sama dengan waktu mulai.');
                }
            }],
        ]);

        $old = $exam->only(['title', 'duration', 'is_active']);
        $exam->update([
            ...$request->only('title', 'description', 'category_id', 'duration', 'passing_score'),
            'is_active' => $request->boolean('is_active'),
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_answers' => $request->boolean('shuffle_answers'),
            'show_result' => $request->boolean('show_result'),
            'terminate_on_events' => $request->input('terminate_on_events'),
            'start_time' => $request->filled('start_time') ? $request->start_time : null,
            'end_time' => $request->filled('end_time') ? $request->end_time : null,
        ]);

        $exam->schoolClasses()->sync($request->class_ids ?? []);

        AuditLog::log('updated', 'Exam', $exam->id, "Mengedit ujian {$exam->title}", $old, ['title' => $exam->title, 'duration' => $exam->duration, 'is_active' => $exam->is_active]);

        return redirect()->route('creator.exams.index')->with('success', 'Ujian berhasil diupdate.');
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeExam($exam);
        $title = $exam->title;
        $exam->delete();
        AuditLog::log('deleted', 'Exam', null, "Menghapus ujian {$title}", ['title' => $title], null);

        return back()->with('success', 'Ujian berhasil dihapus.');
    }

    public function toggleActive(Exam $exam)
    {
        $this->authorizeExam($exam);

        if (!$exam->is_active && $exam->questions()->count() === 0) {
            return back()->with('error', 'Tidak bisa mengaktifkan ujian tanpa soal.');
        }

        $exam->update(['is_active' => !$exam->is_active]);

        $status = $exam->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Ujian berhasil {$status}.");
    }

    public function regenerateToken(Exam $exam)
    {
        $this->authorizeExam($exam);
        $exam->update(['token' => strtoupper(\Illuminate\Support\Str::random(6))]);
        return back()->with('success', 'Token berhasil diperbarui: ' . $exam->token);
    }

    public function monitor(Exam $exam)
    {
        $this->authorizeExam($exam);
        $exam->load('schoolClasses');
        return view('creator.exams.monitor', compact('exam'));
    }

    public function monitorData(Exam $exam)
    {
        $this->authorizeExam($exam);

        $studentIds = \App\Models\User::where('role', 'pengguna')
            ->when($exam->schoolClasses->isNotEmpty(), function ($q) use ($exam) {
                $q->whereIn('school_class_id', $exam->schoolClasses->pluck('id'));
            })
            ->pluck('id');

        $sessions = ExamSession::where('exam_id', $exam->id)
            ->whereIn('user_id', $studentIds)
            ->with('user.schoolClass')
            ->get()
            ->keyBy('user_id');

        $data = [];
        foreach ($studentIds as $userId) {
            $user = \App\Models\User::with('schoolClass')->find($userId);
            if (!$user) continue;

            $session = $sessions->get($userId);
            $status = 'belum_mulai';
            $remainingSeconds = 0;
            $startedAt = null;
            $finishedAt = null;
            $score = null;

            if ($session) {
                if ($session->status === 'in_progress') {
                    $status = 'sedang_ujian';
                    $remainingSeconds = $session->remaining_time;
                    $startedAt = $session->started_at?->toIso8601String();
                } elseif ($session->status === 'completed') {
                    $status = 'selesai';
                    $score = $session->score;
                    $finishedAt = $session->finished_at?->toIso8601String();
                } elseif ($session->status === 'terminated') {
                    $status = 'dihentikan';
                    $score = $session->score;
                    $finishedAt = $session->finished_at?->toIso8601String();
                }
            }

            $data[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'class' => $user->schoolClass?->name ?? '-',
                'status' => $status,
                'remaining_seconds' => $remainingSeconds,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'score' => $score,
            ];
        }

        return response()->json([
            'exam' => ['title' => $exam->title, 'duration' => $exam->duration],
            'students' => array_values($data),
        ]);
    }

    public function preview(Exam $exam)
    {
        $this->authorizeExam($exam);

        $questions = $exam->questions()->with('answers')->orderBy('order')->get();

        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle(crc32('preview'));
        }
        if ($exam->shuffle_answers) {
            $questions->each(function ($question) {
                if ($question->question_type !== 'essay') {
                    $question->setRelation('answers', $question->answers->shuffle(crc32('preview-' . $question->id)));
                }
            });
        }

        return view('creator.exams.preview', compact('exam', 'questions'));
    }

    public function results(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);
        
        $query = $exam->examSessions()
            ->with(['user.schoolClass'])
            ->whereIn('status', ['completed', 'terminated']);

        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('school_class_id', $request->class_id);
            });
        }

        $sessions = $query->latest()->paginate(20);
        $exam->load('schoolClasses');
        
        return view('creator.exams.results', compact('exam', 'sessions'));
    }

    public function exportExcel(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);
        $filename = 'hasil-ujian-' . str()->slug($exam->title);
        if ($request->filled('class_id')) {
            $class = \App\Models\SchoolClass::find($request->class_id);
            if ($class) {
                $filename .= '-' . str()->slug($class->name);
            }
        }
        $filename .= '.xlsx';
        return Excel::download(new ExamResultExport($exam, $request->only('class_id', 'search')), $filename);
    }

    public function duplicate(Exam $exam)
    {
        $this->authorizeExam($exam);

        $copy = $exam->replicate(['token', 'is_active']);
        $copy->title = $exam->title . ' (Salinan)';
        $copy->token = strtoupper(\Illuminate\Support\Str::random(6));
        $copy->is_active = false;
        $copy->save();

        if ($exam->schoolClasses->isNotEmpty()) {
            $copy->schoolClasses()->sync($exam->schoolClasses->pluck('id'));
        }

        foreach ($exam->questions()->with('answers')->orderBy('order')->get() as $q) {
            $imagePath = null;
            if ($q->question_image && Storage::disk('public')->exists($q->question_image)) {
                $ext = pathinfo($q->question_image, PATHINFO_EXTENSION) ?: 'jpg';
                $imagePath = 'questions/' . \Illuminate\Support\Str::uuid() . '.' . $ext;
                Storage::disk('public')->copy($q->question_image, $imagePath);
            }
            $newQ = $copy->questions()->create([
                'question_text' => $q->question_text,
                'question_image' => $imagePath ?? $q->question_image,
                'question_type' => $q->question_type,
                'points' => $q->points,
                'order' => $q->order,
            ]);
            foreach ($q->answers as $a) {
                $newQ->answers()->create([
                    'answer_text' => $a->answer_text,
                    'is_correct' => $a->is_correct,
                    'order' => $a->order,
                ]);
            }
        }

        return redirect()->route('creator.exams.edit', $copy)->with('success', 'Ujian berhasil diduplikasi.');
    }

    public function exportQuestions(Exam $exam)
    {
        $this->authorizeExam($exam);
        $filename = 'soal-' . str()->slug($exam->title) . '.xlsx';
        return Excel::download(new QuestionsExport($exam), $filename);
    }

    public function exportPdf(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);
        $query = $exam->examSessions()
            ->with(['user.schoolClass'])
            ->whereIn('status', ['completed', 'terminated']);

        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('school_class_id', $request->class_id);
            });
        }

        $sessions = $query->orderBy('score', 'desc')->get();
        $exam->load('category');

        $filename = 'hasil-ujian-' . str()->slug($exam->title);
        if ($request->filled('class_id')) {
            $class = \App\Models\SchoolClass::find($request->class_id);
            if ($class) {
                $filename .= '-' . str()->slug($class->name);
            }
        }
        $filename .= '.pdf';

        $pdf = Pdf::loadView('exports.exam-results-pdf', compact('exam', 'sessions'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function authorizeExam(Exam $exam): void
    {
        if ($exam->created_by !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke ujian ini.');
        }
    }
}
