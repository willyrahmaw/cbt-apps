<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;

class ResultController extends Controller
{
    public function index()
    {
        $sessions = ExamSession::where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'terminated'])
            ->with('exam.category')
            ->latest()
            ->paginate(15);

        $categoryStats = ExamSession::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->with('exam.category')
            ->get()
            ->groupBy(fn ($s) => $s->exam->category_id ?? 0)
            ->map(function ($group) {
                $first = $group->first();
                $catName = $first->exam->category->name ?? 'Lainnya';
                return [
                    'name' => $catName,
                    'count' => $group->count(),
                    'avg_score' => round($group->avg('score'), 1),
                    'passed' => $group->filter(fn ($s) => $s->score >= $s->exam->passing_score)->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $totalCompleted = ExamSession::where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'terminated'])
            ->count();

        return view('student.results.index', compact('sessions', 'categoryStats', 'totalCompleted'));
    }

    public function show(ExamSession $session)
    {
        if ($session->user_id !== auth()->id() && !auth()->user()->isSuperadmin()) {
            abort(403);
        }

        $session->load(['exam.questions.answers', 'userAnswers']);

        return view('student.results.show', compact('session'));
    }
}
