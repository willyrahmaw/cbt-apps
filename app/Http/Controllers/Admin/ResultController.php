<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamSession::with(['user', 'exam'])->where('status', 'completed');

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%"))
                  ->orWhereHas('exam', fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        }

        $sessions = $query->latest()->paginate(20);

        return view('admin.results.index', compact('sessions'));
    }

    public function show(ExamSession $session)
    {
        $session->load(['user', 'exam.questions.answers', 'userAnswers']);
        return view('admin.results.show', compact('session'));
    }
}
