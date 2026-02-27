<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'all'); // all, month, week
        $baseQuery = ExamSession::where('status', 'completed');

        if ($period === 'month') {
            $baseQuery->where('finished_at', '>=', now()->subMonth());
        } elseif ($period === 'week') {
            $baseQuery->where('finished_at', '>=', now()->subWeek());
        }

        $totalSessions = (clone $baseQuery)->count();
        $passedSessions = (clone $baseQuery)->whereHas('exam', function ($q) {
            $q->select('id', 'passing_score');
        })->get()->filter(fn ($s) => $s->score >= $s->exam->passing_score)->count();
        $passRate = $totalSessions > 0 ? round(($passedSessions / $totalSessions) * 100, 1) : 0;

        $stats = [
            'totalExams' => Exam::count(),
            'totalStudents' => User::where('role', 'pengguna')->count(),
            'totalSessions' => ExamSession::where('status', 'completed')->count(),
            'periodSessions' => $totalSessions,
            'periodPassed' => $passedSessions,
            'passRate' => $passRate,
            'avgScore' => (clone $baseQuery)->avg('score') ?? 0,
        ];

        $byCategory = Category::all()->map(function ($c) use ($period) {
            $sessions = ExamSession::where('status', 'completed')
                ->whereHas('exam', fn ($q) => $q->where('category_id', $c->id));
            if ($period === 'month') {
                $sessions->where('finished_at', '>=', now()->subMonth());
            } elseif ($period === 'week') {
                $sessions->where('finished_at', '>=', now()->subWeek());
            }
            $sessions = $sessions->get();
            return [
                'name' => $c->name,
                'icon' => $c->icon ?? '',
                'sessions' => $sessions->count(),
                'avg_score' => $sessions->isEmpty() ? 0 : round($sessions->avg('score'), 1),
            ];
        })->filter(fn ($c) => $c['sessions'] > 0)->values();

        $byClass = SchoolClass::withCount('students')
            ->get()
            ->map(function ($cls) use ($period) {
                $sessions = ExamSession::where('status', 'completed')
                    ->whereHas('user', fn ($q) => $q->where('school_class_id', $cls->id));
                if ($period === 'month') {
                    $sessions->where('finished_at', '>=', now()->subMonth());
                } elseif ($period === 'week') {
                    $sessions->where('finished_at', '>=', now()->subWeek());
                }
                $sessions = $sessions->get();
                $avgScore = $sessions->avg('score') ?? 0;
                return [
                    'name' => $cls->name,
                    'students' => $cls->students_count,
                    'sessions' => $sessions->count(),
                    'avg_score' => round($avgScore, 1),
                ];
            })
            ->filter(fn ($c) => $c['sessions'] > 0)
            ->sortByDesc('sessions')
            ->take(10)
            ->values();

        $questionDifficulty = UserAnswer::query()
            ->select('question_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->whereHas('examSession', fn ($q) => $q->where('status', 'completed'))
            ->whereHas('question', fn ($q) => $q->where('question_type', '!=', 'essay'))
            ->groupBy('question_id')
            ->havingRaw('COUNT(*) >= 3')
            ->with(['question.exam.category'])
            ->orderByRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*) ASC')
            ->limit(20)
            ->get()
            ->map(function ($ua) {
                $pct = $ua->total > 0 ? round(($ua->correct / $ua->total) * 100, 1) : 0;
                return [
                    'question' => $ua->question,
                    'total' => $ua->total,
                    'correct' => $ua->correct,
                    'pct_correct' => $pct,
                ];
            });

        return view('admin.analytics.index', compact(
            'stats',
            'byCategory',
            'byClass',
            'questionDifficulty',
            'period'
        ));
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'all');
        $sessions = ExamSession::with(['user.schoolClass', 'exam.category'])
            ->where('status', 'completed');

        if ($period === 'month') {
            $sessions->where('finished_at', '>=', now()->subMonth());
        } elseif ($period === 'week') {
            $sessions->where('finished_at', '>=', now()->subWeek());
        }

        $sessions = $sessions->orderBy('finished_at', 'desc')->get();

        $filename = 'laporan-analytics-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama', 'Email', 'Kelas', 'Ujian', 'Kategori', 'Skor', 'KKM', 'Status', 'Selesai']);
            foreach ($sessions as $s) {
                $passed = $s->score >= $s->exam->passing_score;
                fputcsv($file, [
                    $s->user->name,
                    $s->user->email,
                    $s->user->schoolClass?->name ?? '-',
                    $s->exam->title,
                    $s->exam->category?->name ?? '-',
                    $s->score,
                    $s->exam->passing_score,
                    $passed ? 'Lulus' : 'Tidak Lulus',
                    $s->finished_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
