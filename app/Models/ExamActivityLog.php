<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamActivityLog extends Model
{
    protected $fillable = ['exam_session_id', 'user_id', 'event', 'meta'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(int $examSessionId, string $event, ?array $meta = null): void
    {
        $session = ExamSession::find($examSessionId);
        if (!$session) return;

        static::create([
            'exam_session_id' => $examSessionId,
            'user_id' => $session->user_id,
            'event' => $event,
            'meta' => $meta,
        ]);
    }
}
