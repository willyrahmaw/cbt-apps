<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankQuestion extends Model
{
    protected $fillable = [
        'question_bank_id',
        'question_text',
        'question_image',
        'question_type',
        'points',
        'order',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function answers()
    {
        return $this->hasMany(BankAnswer::class, 'bank_question_id')->orderBy('order');
    }
}
