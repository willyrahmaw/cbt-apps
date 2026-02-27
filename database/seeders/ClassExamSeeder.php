<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassExamSeeder extends Seeder
{
    public function run(): void
    {
        $classIds = DB::table('school_classes')->pluck('id')->toArray();
        $examIds = DB::table('exams')->pluck('id')->toArray();
        if (empty($classIds) || empty($examIds)) return;

        $pivots = [];
        $used = [];
        $now = now();

        for ($i = 0; $i < 180; $i++) {
            $cid = $classIds[array_rand($classIds)];
            $eid = $examIds[array_rand($examIds)];
            $key = "{$cid}_{$eid}";
            if (isset($used[$key])) continue;
            $used[$key] = true;
            $pivots[] = [
                'school_class_id' => $cid,
                'exam_id' => $eid,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($pivots, 50) as $chunk) {
            DB::table('class_exam')->insertOrIgnore($chunk);
        }
    }
}
