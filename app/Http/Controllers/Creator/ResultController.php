<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $myExamIds = Exam::where('created_by', auth()->id())->pluck('id');

        $query = ExamSession::with(['user.schoolClass', 'exam'])
            ->whereIn('exam_id', $myExamIds)
            ->where('status', 'completed');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('exam', fn($e) => $e->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('grading')) {
            $query->where('needs_grading', $request->grading === 'pending');
        }

        $sessions = $query->latest('finished_at')->paginate(20);
        $exams = Exam::where('created_by', auth()->id())->orderBy('title')->get();

        return view('creator.results.index', compact('sessions', 'exams'));
    }
}
