<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_session_id',
        'question_id',
        'answer_id',
        'essay_text',
        'essay_score',
        'is_correct',
        'is_graded',
        'is_ragu',
        'time_spent_seconds',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'is_graded' => 'boolean',
            'is_ragu' => 'boolean',
        ];
    }

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
