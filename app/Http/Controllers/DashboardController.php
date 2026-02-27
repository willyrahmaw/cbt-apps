<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return view('dashboard.superadmin', [
                'totalUsers' => User::count(),
                'totalExams' => Exam::count(),
                'totalCategories' => Category::count(),
                'totalSessions' => ExamSession::where('status', 'completed')->count(),
                'recentUsers' => User::latest()->take(5)->get(),
                'recentSessions' => ExamSession::with(['user', 'exam'])->where('status', 'completed')->latest()->take(10)->get(),
            ]);
        }

        if ($user->isPembuatSoal()) {
            return view('dashboard.pembuat_soal', [
                'totalExams' => Exam::where('created_by', $user->id)->count(),
                'activeExams' => Exam::where('created_by', $user->id)->where('is_active', true)->count(),
                'totalSessions' => ExamSession::whereHas('exam', fn($q) => $q->where('created_by', $user->id))->where('status', 'completed')->count(),
                'needsGrading' => ExamSession::whereHas('exam', fn($q) => $q->where('created_by', $user->id))->where('needs_grading', true)->count(),
                'recentExams' => Exam::where('created_by', $user->id)->with('category')->latest()->take(5)->get(),
                'recentSessions' => ExamSession::with(['user', 'exam'])->whereHas('exam', fn($q) => $q->where('created_by', $user->id))->where('status', 'completed')->latest()->take(10)->get(),
            ]);
        }

        $availableQuery = Exam::where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('schoolClasses')
                    ->orWhereHas('schoolClasses', fn($q) => $q->where('school_classes.id', $user->school_class_id));
            })
            ->where(function ($query) {
                $query->whereNull('end_time')->orWhere('end_time', '>=', now());
            });

        $upcomingQuery = Exam::where('is_active', true)
            ->whereNotNull('start_time')
            ->where('start_time', '>', now())
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('schoolClasses')
                    ->orWhereHas('schoolClasses', fn($q) => $q->where('school_classes.id', $user->school_class_id));
            });

        return view('dashboard.pengguna', [
            'availableExams' => (clone $availableQuery)->with(['category'])->withCount('questions')->latest()->take(6)->get(),
            'upcomingExams' => $upcomingQuery->with(['category'])->withCount('questions')->orderBy('start_time')->take(5)->get(),
            'completedSessions' => ExamSession::where('user_id', $user->id)->where('status', 'completed')->with('exam.category')->latest()->take(5)->get(),
            'inProgressSession' => ExamSession::where('user_id', $user->id)->where('status', 'in_progress')->with('exam')->first(),
            'totalCompleted' => ExamSession::where('user_id', $user->id)->where('status', 'completed')->count(),
            'avgScore' => ExamSession::where('user_id', $user->id)->where('status', 'completed')->avg('score') ?? 0,
        ]);
    }
}
