<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchoolClassSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            QuestionBankSeeder::class,
            BankQuestionSeeder::class,
            BankAnswerSeeder::class,
            ExamSeeder::class,
            ClassExamSeeder::class,
            QuestionSeeder::class,
            AnswerSeeder::class,
            ExamSessionSeeder::class,
            UserAnswerSeeder::class,
            ExamActivityLogSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
