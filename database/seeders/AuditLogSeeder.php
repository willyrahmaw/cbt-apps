<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->whereIn('role', ['superadmin', 'pembuat_soal'])->pluck('id')->toArray();
        if (empty($userIds)) return;

        $logs = [];
        $actions = ['created', 'updated', 'deleted'];
        $auditables = ['Exam', 'Question', 'Category', 'User', 'SchoolClass', 'QuestionBank'];
        $now = now();

        for ($i = 0; $i < 80; $i++) {
            $logs[] = [
                'user_id' => $userIds[array_rand($userIds)],
                'auditable_type' => $auditables[array_rand($auditables)],
                'auditable_id' => rand(1, 100),
                'action' => $actions[array_rand($actions)],
                'description' => 'Seed audit log ' . ($i + 1),
                'old_values' => null,
                'new_values' => json_encode(['field' => 'value']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($logs, 50) as $chunk) {
            DB::table('audit_logs')->insert($chunk);
        }
    }
}
