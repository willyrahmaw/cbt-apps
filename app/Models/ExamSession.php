<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'finished_at',
        'score',
        'correct_answers',
        'total_questions',
        'status',
        'needs_grading',
        'remaining_seconds_on_termination',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'needs_grading' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ExamActivityLog::class)->orderBy('created_at', 'desc');
    }

    public function isPassed(): bool
    {
        return $this->score >= $this->exam->passing_score;
    }

    public function getRemainingTimeAttribute(): int
    {
        if ($this->status !== 'in_progress') return 0;
        $endByDuration = $this->started_at->addMinutes($this->exam->duration);
        $endTime = $this->exam->end_time && $this->exam->end_time->lt($endByDuration)
            ? $this->exam->end_time
            : $endByDuration;
        return max(0, now()->diffInSeconds($endTime, false));
    }

    public function recalculateScore(): void
    {
        $exam = $this->exam;
        $totalPoints = $exam->questions()->sum('points');
        if ($totalPoints === 0) return;

        $earnedPoints = 0;

        foreach ($this->userAnswers()->with('question')->get() as $ua) {
            $q = $ua->question;
            if ($q->question_type === 'essay') {
                if ($ua->is_graded && $ua->essay_score !== null) {
                    $earnedPoints += $ua->essay_score;
                }
            } else {
                if ($ua->is_correct) {
                    $earnedPoints += $q->points;
                }
            }
        }

        $this->update([
            'score' => round(($earnedPoints / $totalPoints) * 100),
            'correct_answers' => $this->userAnswers()->where('is_correct', true)->count(),
            'needs_grading' => $this->userAnswers()
                ->whereHas('question', fn($q) => $q->where('question_type', 'essay'))
                ->where('is_graded', false)
                ->exists(),
        ]);
    }

    public function terminateForViolation(): void
    {
        if ($this->status !== 'in_progress') return;

        $remainingSeconds = $this->remaining_time;
        $this->recalculateScore();
        $this->update([
            'finished_at' => now(),
            'status' => 'terminated',
            'remaining_seconds_on_termination' => max(0, $remainingSeconds),
        ]);
        $this->exam->update(['token' => strtoupper(\Illuminate\Support\Str::random(6))]);
    }
}
