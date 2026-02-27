<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamActivityLog;
use App\Models\ExamSession;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $exams = Exam::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('schoolClasses')
                    ->orWhereHas('schoolClasses', function ($q) use ($user) {
                        $q->where('school_classes.id', $user->school_class_id);
                    });
            })
            ->with(['category', 'creator'])
            ->withCount('questions')
            ->latest()
            ->paginate(12);

        return view('student.exams.index', compact('exams'));
    }

    public function show(Exam $exam)
    {
        if (!$exam->is_active) {
            abort(404);
        }

        if (!$this->canAccessExam(auth()->user(), $exam)) {
            abort(403, 'Ujian ini tidak tersedia untuk kelas Anda.');
        }

        $exam->loadCount('questions');
        $existingSession = ExamSession::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        $completedCount = ExamSession::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->count();

        $hasTerminated = ExamSession::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->where('status', 'terminated')
            ->exists();

        $canStart = true;
        $timeMessage = null;
        if ($exam->start_time && now()->lt($exam->start_time)) {
            $canStart = false;
            $timeMessage = 'Ujian bisa dimulai pada ' . $exam->start_time->format('d/m/Y H:i');
        } elseif ($exam->end_time && now()->gt($exam->end_time)) {
            $canStart = false;
            $timeMessage = 'Waktu ujian sudah berakhir (ditutup ' . $exam->end_time->format('d/m/Y H:i') . ')';
        }

        return view('student.exams.show', compact('exam', 'existingSession', 'completedCount', 'hasTerminated', 'canStart', 'timeMessage'));
    }

    public function start(Request $request, Exam $exam)
    {
        if (!$exam->is_active) {
            abort(404);
        }

        if (!$this->canAccessExam(auth()->user(), $exam)) {
            return back()->with('error', 'Ujian ini tidak tersedia untuk kelas Anda.');
        }

        if ($exam->start_time && now()->lt($exam->start_time)) {
            return back()->with('error', 'Ujian baru bisa dimulai pada ' . $exam->start_time->format('d/m/Y H:i') . '.');
        }

        if ($exam->end_time && now()->gt($exam->end_time)) {
            return back()->with('error', 'Waktu ujian sudah berakhir. Ujian ditutup pada ' . $exam->end_time->format('d/m/Y H:i') . '.');
        }

        $request->validate(['exam_token' => 'required|string']);

        if (strtoupper(trim($request->exam_token)) !== $exam->token) {
            return back()->with('error', 'Token tidak valid. Silakan periksa token dari pengawas/guru.');
        }

        $existing = ExamSession::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return redirect()->route('student.exams.take', $existing);
        }

        $alreadyCompleted = ExamSession::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->exists();

        if ($alreadyCompleted) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', 'Anda sudah mengerjakan ujian ini. Setiap ujian hanya bisa dikerjakan 1 kali.');
        }

        $terminatedSession = ExamSession::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->where('status', 'terminated')
            ->whereNotNull('remaining_seconds_on_termination')
            ->latest()
            ->first();

        $startedAt = now();
        if ($terminatedSession) {
            $remaining = $terminatedSession->remaining_seconds_on_termination;
            $durationSeconds = $exam->duration * 60;
            $startedAt = now()->subSeconds($durationSeconds - $remaining);
        }

        $session = ExamSession::create([
            'user_id' => auth()->id(),
            'exam_id' => $exam->id,
            'started_at' => $startedAt,
            'total_questions' => $exam->questions()->count(),
        ]);

        return redirect()->route('student.exams.take', $session);
    }

    public function take(ExamSession $session)
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        if ($session->status !== 'in_progress') {
            $message = $session->status === 'terminated'
                ? 'Ujian diakhiri karena pelanggaran. Mintalah token baru ke pengawas untuk mengulang.'
                : null;
            return redirect()->route('student.results.show', $session)->with('info', $message);
        }

        if ($session->remaining_time <= 0) {
            return $this->finishExam($session);
        }

        $exam = $session->exam;
        $questions = $exam->questions()->with('answers')->get();

        $seed = crc32($session->id . '-questions');
        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle($seed);
        }

        if ($exam->shuffle_answers) {
            $questions->each(function ($question) use ($session) {
                if ($question->question_type !== 'essay') {
                    $answerSeed = crc32($session->id . '-answers-' . $question->id);
                    $question->setRelation('answers', $question->answers->shuffle($answerSeed));
                }
            });
        }

        $answeredIds = $session->userAnswers()->pluck('question_id')->toArray();
        $essayAnswers = $session->userAnswers()
            ->whereNotNull('essay_text')
            ->pluck('essay_text', 'question_id')
            ->toArray();
        $raguQuestionIds = $session->userAnswers()->where('is_ragu', true)->pluck('question_id')->toArray();
        $answerStates = $session->userAnswers()->get()->keyBy('question_id');

        return view('student.exams.take', compact('session', 'exam', 'questions', 'answeredIds', 'essayAnswers', 'raguQuestionIds', 'answerStates'));
    }

    public function remainingTime(ExamSession $session)
    {
        if ($session->user_id !== auth()->id() || $session->status !== 'in_progress') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json(['remaining_seconds' => $session->remaining_time]);
    }

    public function logActivity(Request $request, ExamSession $session)
    {
        if ($session->user_id !== auth()->id() || $session->status !== 'in_progress') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $request->validate(['event' => 'required|string|in:tab_switch,right_click,copy_attempt,paste_attempt,split_screen,window_blur,fullscreen_exit']);
        ExamActivityLog::record($session->id, $request->event);

        $terminateEvents = $session->exam->terminate_on_events ?? [];
        if (in_array($request->event, $terminateEvents)) {
            $session->terminateForViolation();
            return response()->json([
                'success' => true,
                'terminated' => true,
                'redirect' => route('student.results.show', $session),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function saveAnswer(Request $request, ExamSession $session)
    {
        if ($session->user_id !== auth()->id() || $session->status !== 'in_progress') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($session->remaining_time <= 0) {
            ExamActivityLog::record($session->id, 'time_up_attempt');
            $terminateEvents = $session->exam->terminate_on_events ?? [];
            if (in_array('time_up_attempt', $terminateEvents)) {
                $session->terminateForViolation();
                return response()->json(['error' => 'Waktu ujian sudah habis.', 'time_up' => true, 'terminated' => true, 'redirect' => route('student.results.show', $session)], 400);
            }
            return response()->json(['error' => 'Waktu ujian sudah habis.', 'time_up' => true], 400);
        }

        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        $question = \App\Models\Question::where('id', $request->question_id)
            ->where('exam_id', $session->exam_id)
            ->firstOrFail();

        $timeSpent = min(max($request->integer('time_spent_seconds', 0), 0), 3600);

        if ($question->question_type === 'essay') {
            $request->validate([
                'essay_text' => 'required|string|max:10000',
            ]);

            UserAnswer::updateOrCreate(
                [
                    'exam_session_id' => $session->id,
                    'question_id' => $request->question_id,
                ],
                [
                    'essay_text' => $request->essay_text,
                    'answer_id' => null,
                    'is_correct' => false,
                    'is_graded' => false,
                    'time_spent_seconds' => $timeSpent > 0 ? $timeSpent : null,
                ]
            );
        } else {
            $request->validate([
                'answer_id' => 'required|exists:answers,id',
            ]);

            $answer = \App\Models\Answer::where('id', $request->answer_id)
                ->where('question_id', $question->id)
                ->firstOrFail();

            UserAnswer::updateOrCreate(
                [
                    'exam_session_id' => $session->id,
                    'question_id' => $request->question_id,
                ],
                [
                    'answer_id' => $request->answer_id,
                    'is_correct' => $answer->is_correct,
                    'is_graded' => true,
                    'is_ragu' => $request->boolean('is_ragu'),
                    'time_spent_seconds' => $timeSpent > 0 ? $timeSpent : null,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function finish(ExamSession $session)
    {
        if ($session->user_id !== auth()->id() || $session->status !== 'in_progress') {
            return redirect()->route('dashboard');
        }

        $raguCount = $session->userAnswers()->where('is_ragu', true)->count();
        if ($raguCount > 0) {
            return redirect()->route('student.exams.take', $session)
                ->with('error', "Masih ada {$raguCount} soal yang ditandai ragu-ragu. Silakan tinjau dan pastikan jawaban Anda sebelum menyelesaikan ujian.");
        }

        return $this->finishExam($session);
    }

    private function canAccessExam($user, Exam $exam): bool
    {
        $hasClassRestriction = $exam->schoolClasses()->exists();
        if (!$hasClassRestriction) {
            return true;
        }
        if (!$user->school_class_id) {
            return false;
        }
        return $exam->schoolClasses()->where('school_classes.id', $user->school_class_id)->exists();
    }

    private function finishExam(ExamSession $session)
    {
        $exam = $session->exam;
        $totalPoints = $exam->questions()->sum('points');
        $earnedPoints = 0;
        $correctCount = 0;
        $hasEssay = false;

        foreach ($session->userAnswers()->with('question')->get() as $ua) {
            if ($ua->question->question_type !== 'essay') {
                if ($ua->is_correct) {
                    $earnedPoints += $ua->question->points;
                    $correctCount++;
                }
            } else {
                $hasEssay = true;
            }
        }

        $essayExists = $exam->questions()->where('question_type', 'essay')->exists();
        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;

        $session->update([
            'finished_at' => now(),
            'score' => $score,
            'correct_answers' => $correctCount,
            'status' => 'completed',
            'needs_grading' => $essayExists,
        ]);

        return redirect()->route('student.results.show', $session)->with('success', 'Ujian selesai!');
    }
}
