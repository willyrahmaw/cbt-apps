<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class GradingController extends Controller
{
    public function show(Exam $exam, ExamSession $session)
    {
        $this->authorizeExam($exam);

        if ($session->exam_id !== $exam->id) {
            abort(404);
        }

        $session->load(['user', 'userAnswers.question', 'userAnswers.answer', 'activityLogs']);

        $essayAnswers = $session->userAnswers->filter(
            fn($ua) => $ua->question->question_type === 'essay'
        );

        return view('creator.grading.show', compact('exam', 'session', 'essayAnswers'));
    }

    public function update(Request $request, Exam $exam, ExamSession $session)
    {
        $this->authorizeExam($exam);

        if ($session->exam_id !== $exam->id) {
            abort(404);
        }

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|integer|min:0',
        ]);

        foreach ($request->scores as $userAnswerId => $score) {
            $ua = UserAnswer::where('id', $userAnswerId)
                ->where('exam_session_id', $session->id)
                ->first();

            if (!$ua) continue;

            $maxPoints = $ua->question->points;
            $score = min($score, $maxPoints);

            $ua->update([
                'essay_score' => $score,
                'is_graded' => true,
                'is_correct' => $score >= ($maxPoints / 2),
            ]);
        }

        $session->recalculateScore();

        return redirect()->route('creator.exams.results', $exam)
            ->with('success', "Penilaian essai untuk {$session->user->name} berhasil disimpan.");
    }

    private function authorizeExam(Exam $exam): void
    {
        if ($exam->created_by !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke ujian ini.');
        }
    }
}
