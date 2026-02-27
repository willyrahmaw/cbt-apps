<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Creator\ExamController as CreatorExamController;
use App\Http\Controllers\Creator\QuestionController;
use App\Http\Controllers\Creator\GradingController;
use App\Http\Controllers\Creator\ResultController as CreatorResultController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\ResultController as StudentResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Superadmin routes
    Route::middleware('role:superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users/template', [UserController::class, 'template'])->name('users.template');
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');
        Route::resource('users', UserController::class)->except('show');
        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
        Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
        Route::get('classes/template', [ClassController::class, 'template'])->name('classes.template');
        Route::post('classes/import', [ClassController::class, 'import'])->name('classes.import');
        Route::get('classes/promote', [ClassController::class, 'promote'])->name('classes.promote');
        Route::post('classes/promote', [ClassController::class, 'promoteStore'])->name('classes.promote.store');
        Route::resource('classes', ClassController::class);
        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings/academic-year', [\App\Http\Controllers\Admin\SettingController::class, 'updateAcademicYear'])->name('settings.academic-year');
        Route::get('settings/website', [\App\Http\Controllers\Admin\SettingController::class, 'website'])->name('settings.website');
        Route::put('settings/website', [\App\Http\Controllers\Admin\SettingController::class, 'updateWebsite'])->name('settings.website.update');
        Route::post('classes/{class}/students', [ClassController::class, 'addStudent'])->name('classes.addStudent');
        Route::delete('classes/{class}/students/{user}', [ClassController::class, 'removeStudent'])->name('classes.removeStudent');
        Route::get('results', [AdminResultController::class, 'index'])->name('results.index');
        Route::get('results/{session}', [AdminResultController::class, 'show'])->name('results.show');
        Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'export'])->name('analytics.export');
        Route::get('audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-log.index');
        Route::get('backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup.index');
        Route::get('backup/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backup.download');
        Route::get('backup/download-database', [\App\Http\Controllers\Admin\BackupController::class, 'downloadDatabase'])->name('backup.download-database');
        Route::post('backup/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backup.restore');
    });

    // Pembuat Soal routes
    Route::middleware('role:pembuat_soal,superadmin')->prefix('creator')->name('creator.')->group(function () {
        Route::resource('exams', CreatorExamController::class)->except('show');
        Route::post('exams/{exam}/toggle', [CreatorExamController::class, 'toggleActive'])->name('exams.toggle');
        Route::post('exams/{exam}/regenerate-token', [CreatorExamController::class, 'regenerateToken'])->name('exams.regenerate-token');
        Route::get('exams/{exam}/preview', [CreatorExamController::class, 'preview'])->name('exams.preview');
        Route::get('exams/{exam}/monitor', [CreatorExamController::class, 'monitor'])->name('exams.monitor');
        Route::get('exams/{exam}/monitor/data', [CreatorExamController::class, 'monitorData'])->name('exams.monitor.data');
        Route::get('exams/{exam}/results', [CreatorExamController::class, 'results'])->name('exams.results');
        Route::get('exams/{exam}/export/excel', [CreatorExamController::class, 'exportExcel'])->name('exams.export.excel');
        Route::get('exams/{exam}/export/pdf', [CreatorExamController::class, 'exportPdf'])->name('exams.export.pdf');
        Route::get('exams/{exam}/questions', [QuestionController::class, 'index'])->name('exams.questions');
        Route::post('exams/{exam}/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::put('exams/{exam}/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('exams/{exam}/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::get('questions/template', [QuestionController::class, 'template'])->name('questions.template');
        Route::post('exams/{exam}/questions/import', [QuestionController::class, 'import'])->name('questions.import');
        Route::post('exams/{exam}/duplicate', [CreatorExamController::class, 'duplicate'])->name('exams.duplicate');
        Route::get('exams/{exam}/export/questions', [CreatorExamController::class, 'exportQuestions'])->name('exams.export.questions');
        Route::post('exams/{exam}/questions/{question}/duplicate', [QuestionController::class, 'duplicate'])->name('questions.duplicate');

        Route::get('question-bank', [\App\Http\Controllers\Creator\QuestionBankController::class, 'index'])->name('question-bank.index');
        Route::get('question-bank/create', [\App\Http\Controllers\Creator\QuestionBankController::class, 'create'])->name('question-bank.create');
        Route::post('question-bank', [\App\Http\Controllers\Creator\QuestionBankController::class, 'store'])->name('question-bank.store');
        Route::get('question-bank/{questionBank}', [\App\Http\Controllers\Creator\QuestionBankController::class, 'show'])->name('question-bank.show');
        Route::get('question-bank/{questionBank}/edit', [\App\Http\Controllers\Creator\QuestionBankController::class, 'edit'])->name('question-bank.edit');
        Route::put('question-bank/{questionBank}', [\App\Http\Controllers\Creator\QuestionBankController::class, 'update'])->name('question-bank.update');
        Route::delete('question-bank/{questionBank}', [\App\Http\Controllers\Creator\QuestionBankController::class, 'destroy'])->name('question-bank.destroy');
        Route::post('question-bank/{questionBank}/questions', [\App\Http\Controllers\Creator\QuestionBankController::class, 'storeQuestion'])->name('question-bank.questions.store');
        Route::get('question-bank/{questionBank}/questions/create', [\App\Http\Controllers\Creator\QuestionBankController::class, 'createQuestion'])->name('question-bank.questions.create');
        Route::get('question-bank/{questionBank}/questions/{bankQuestion}/edit', [\App\Http\Controllers\Creator\QuestionBankController::class, 'editQuestion'])->name('question-bank.questions.edit');
        Route::put('question-bank/{questionBank}/questions/{bankQuestion}', [\App\Http\Controllers\Creator\QuestionBankController::class, 'updateQuestion'])->name('question-bank.questions.update');
        Route::delete('question-bank/{questionBank}/questions/{bankQuestion}', [\App\Http\Controllers\Creator\QuestionBankController::class, 'destroyQuestion'])->name('question-bank.questions.destroy');
        Route::post('question-bank/{questionBank}/add-to-exam', [\App\Http\Controllers\Creator\QuestionBankController::class, 'addToExam'])->name('question-bank.add-to-exam');
        Route::post('question-bank/{questionBank}/add-selected-to-exam', [\App\Http\Controllers\Creator\QuestionBankController::class, 'addSelectedToExam'])->name('question-bank.add-selected-to-exam');
        Route::post('question-bank/{questionBank}/import', [\App\Http\Controllers\Creator\QuestionBankController::class, 'import'])->name('question-bank.import');
        Route::get('question-bank/{questionBank}/export', [\App\Http\Controllers\Creator\QuestionBankController::class, 'export'])->name('question-bank.export');

        Route::get('results', [CreatorResultController::class, 'index'])->name('results.index');

        // Grading essai
        Route::get('exams/{exam}/grading/{session}', [GradingController::class, 'show'])->name('grading.show');
        Route::put('exams/{exam}/grading/{session}', [GradingController::class, 'update'])->name('grading.update');
    });

    // Pengguna routes
    Route::middleware('role:pengguna')->prefix('student')->name('student.')->group(function () {
        Route::get('exams', [StudentExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
        Route::post('exams/{exam}/start', [StudentExamController::class, 'start'])->name('exams.start');
        Route::get('session/{session}', [StudentExamController::class, 'take'])->name('exams.take');
        Route::get('session/{session}/remaining', [StudentExamController::class, 'remainingTime'])->name('exams.remaining');
        Route::post('session/{session}/answer', [StudentExamController::class, 'saveAnswer'])->name('exams.answer')->middleware(['log.exam.ratelimit', 'throttle:30,1']);
        Route::post('session/{session}/log', [StudentExamController::class, 'logActivity'])->name('exams.log');
        Route::post('session/{session}/finish', [StudentExamController::class, 'finish'])->name('exams.finish');
        Route::get('results', [StudentResultController::class, 'index'])->name('results.index');
        Route::get('results/{session}', [StudentResultController::class, 'show'])->name('results.show');
    });
});
