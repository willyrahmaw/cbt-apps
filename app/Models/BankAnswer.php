<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAnswer extends Model
{
    protected $fillable = ['bank_question_id', 'answer_text', 'is_correct', 'order'];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function bankQuestion()
    {
        return $this->belongsTo(BankQuestion::class);
    }
}
