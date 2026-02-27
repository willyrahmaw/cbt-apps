<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'created_by',
        'duration',
        'passing_score',
        'is_active',
        'token',
        'terminate_on_events',
        'shuffle_questions',
        'shuffle_answers',
        'show_result',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'terminate_on_events' => 'array',
            'is_active' => 'boolean',
            'shuffle_questions' => 'boolean',
            'shuffle_answers' => 'boolean',
            'show_result' => 'boolean',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_exam');
    }
}
