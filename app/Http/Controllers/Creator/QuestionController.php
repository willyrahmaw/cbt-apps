<?php

namespace App\Http\Controllers\Creator;

use App\Exports\QuestionTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\QuestionsImport;
use App\Models\AuditLog;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    public function index(Exam $exam)
    {
        $this->authorizeExam($exam);
        $exam->load(['questions.answers', 'category']);
        $isMathOrPhysics = $exam->category?->isMathOrPhysics() ?? false;
        return view('creator.questions.index', compact('exam', 'isMathOrPhysics'));
    }

    public function store(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);

        $rules = [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,essay',
            'points' => 'required|integer|min:1',
            'question_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];

        if ($request->question_type !== 'essay') {
            $rules['answers'] = 'required|array|min:2';
            $rules['answers.*.text'] = 'required|string';
            $rules['correct_answer'] = 'required|integer';
        }

        $request->validate($rules);

        $order = $exam->questions()->max('order') + 1;

        $data = [
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'points' => $request->points,
            'order' => $order,
        ];

        if ($request->hasFile('question_image')) {
            $data['question_image'] = $request->file('question_image')->store('questions', 'public');
        }

        $question = $exam->questions()->create($data);

        if ($request->question_type !== 'essay' && $request->has('answers')) {
            foreach ($request->answers as $index => $answer) {
                $question->answers()->create([
                    'answer_text' => $answer['text'],
                    'is_correct' => $index == $request->correct_answer,
                    'order' => $index,
                ]);
            }
        }

        AuditLog::log('created', 'Question', $question->id, "Menambah soal ke ujian {$exam->title}", null, ['exam_id' => $exam->id, 'question_type' => $question->question_type]);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function update(Request $request, Exam $exam, Question $question)
    {
        $this->authorizeExam($exam);

        $rules = [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,essay',
            'points' => 'required|integer|min:1',
            'question_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];

        if ($request->question_type !== 'essay') {
            $rules['answers'] = 'required|array|min:2';
            $rules['answers.*.text'] = 'required|string';
            $rules['correct_answer'] = 'required|integer';
        }

        $request->validate($rules);

        $data = [
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'points' => $request->points,
        ];

        if ($request->hasFile('question_image')) {
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $data['question_image'] = $request->file('question_image')->store('questions', 'public');
        }

        if ($request->boolean('remove_image') && !$request->hasFile('question_image')) {
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $data['question_image'] = null;
        }

        $old = $question->only(['question_type', 'points']);
        $question->update($data);
        $question->answers()->delete();

        AuditLog::log('updated', 'Question', $question->id, "Mengedit soal di ujian {$exam->title}", $old, ['question_type' => $question->question_type, 'points' => $question->points]);

        if ($request->question_type !== 'essay' && $request->has('answers')) {
            foreach ($request->answers as $index => $answer) {
                $question->answers()->create([
                    'answer_text' => $answer['text'],
                    'is_correct' => $index == $request->correct_answer,
                    'order' => $index,
                ]);
            }
        }

        return back()->with('success', 'Soal berhasil diupdate.');
    }

    public function destroy(Exam $exam, Question $question)
    {
        $this->authorizeExam($exam);

        if ($question->question_image) {
            Storage::disk('public')->delete($question->question_image);
        }

        $examTitle = $exam->title;
        $question->delete();
        AuditLog::log('deleted', 'Question', null, "Menghapus soal dari ujian {$examTitle}", ['exam_id' => $exam->id], null);

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function duplicate(Exam $exam, Question $question)
    {
        $this->authorizeExam($exam);
        if ($question->exam_id !== $exam->id) abort(404);

        $order = $exam->questions()->max('order') + 1;
        $imagePath = null;
        if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
            $ext = pathinfo($question->question_image, PATHINFO_EXTENSION) ?: 'jpg';
            $imagePath = 'questions/' . \Illuminate\Support\Str::uuid() . '.' . $ext;
            Storage::disk('public')->copy($question->question_image, $imagePath);
        }

        $newQ = $exam->questions()->create([
            'question_text' => $question->question_text . ' (Salinan)',
            'question_image' => $imagePath,
            'question_type' => $question->question_type,
            'points' => $question->points,
            'order' => $order,
        ]);

        foreach ($question->answers as $a) {
            $newQ->answers()->create([
                'answer_text' => $a->answer_text,
                'is_correct' => $a->is_correct,
                'order' => $a->order,
            ]);
        }

        return back()->with('success', 'Soal berhasil diduplikasi.');
    }

    public function template()
    {
        return Excel::download(new QuestionTemplateExport, 'template_soal.xlsx');
    }

    public function import(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new QuestionsImport($exam->id);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }

        $count = $import->getImportedCount();
        $errors = $import->getErrors();

        $message = "{$count} soal berhasil diimpor.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' baris dilewati.';
        }

        return back()
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    private function authorizeExam(Exam $exam): void
    {
        if ($exam->created_by !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke ujian ini.');
        }
    }
}
