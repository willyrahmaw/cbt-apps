<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = DB::table('exam_sessions')->limit(100)->get(['id', 'user_id']);
        if ($sessions->isEmpty()) return;

        $events = ['tab_switch', 'right_click', 'copy_attempt', 'paste_attempt', 'focus_out', 'visibility_change'];
        $logs = [];
        $now = now();

        foreach ($sessions as $s) {
            for ($i = 0; $i < rand(0, 3); $i++) {
                $logs[] = [
                    'exam_session_id' => $s->id,
                    'user_id' => $s->user_id,
                    'event' => $events[array_rand($events)],
                    'meta' => json_encode(['count' => rand(1, 5)]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($logs)) {
            foreach (array_chunk($logs, 100) as $chunk) {
                DB::table('exam_activity_logs')->insert($chunk);
            }
        }
    }
}
