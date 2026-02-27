<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Imports\BankQuestionsImport;
use App\Models\AuditLog;
use App\Models\Answer;
use App\Models\BankAnswer;
use App\Models\BankQuestion;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $query = QuestionBank::with(['category', 'creator'])->withCount('questions')
            ->where('created_by', auth()->id());

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $banks = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('creator.question-bank.index', compact('banks', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('creator.question-bank.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $bank = QuestionBank::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('created', 'QuestionBank', $bank->id, "Membuat bank soal {$bank->name}", null, ['name' => $bank->name]);

        return redirect()->route('creator.question-bank.index')->with('success', 'Bank soal berhasil dibuat.');
    }

    public function show(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $query = $questionBank->questions()->with('answers');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('question_text', 'like', "%{$s}%");
        }
        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->whereJsonContains('tags', $tag);
        }

        $questions = $query->orderBy('order')->paginate(15)->withQueryString();
        $allTags = BankQuestion::where('question_bank_id', $questionBank->id)
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return view('creator.question-bank.show', compact('questionBank', 'questions', 'allTags'));
    }

    public function edit(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);
        $categories = Category::all();
        return view('creator.question-bank.edit', compact('questionBank', 'categories'));
    }

    public function update(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $old = $questionBank->only(['name', 'category_id']);
        $questionBank->update($request->only('name', 'category_id', 'description'));
        AuditLog::log('updated', 'QuestionBank', $questionBank->id, "Mengedit bank soal {$questionBank->name}", $old, ['name' => $questionBank->name]);

        return redirect()->route('creator.question-bank.show', $questionBank)->with('success', 'Bank soal berhasil diupdate.');
    }

    public function destroy(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $name = $questionBank->name;
        foreach ($questionBank->questions as $bq) {
            if ($bq->question_image) {
                Storage::disk('public')->delete($bq->question_image);
            }
        }
        $questionBank->delete();
        AuditLog::log('deleted', 'QuestionBank', null, "Menghapus bank soal {$name}", ['name' => $name], null);

        return redirect()->route('creator.question-bank.index')->with('success', 'Bank soal berhasil dihapus.');
    }

    public function createQuestion(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);
        return view('creator.question-bank.questions.create', compact('questionBank'));
    }

    public function storeQuestion(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $rules = [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,essay',
            'points' => 'required|integer|min:1',
            'tags' => 'nullable|string',
            'question_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];
        if ($request->question_type !== 'essay') {
            $rules['answers'] = 'required|array|min:2';
            $rules['answers.*.text'] = 'required|string';
            $rules['correct_answer'] = 'required|integer';
        }
        $request->validate($rules);

        $order = $questionBank->questions()->max('order') + 1;
        $tags = $request->filled('tags')
            ? array_map('trim', array_filter(explode(',', $request->tags)))
            : null;

        $data = [
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'points' => $request->points,
            'order' => $order,
            'tags' => $tags,
        ];
        if ($request->hasFile('question_image')) {
            $data['question_image'] = $request->file('question_image')->store('bank-questions', 'public');
        }

        $bq = $questionBank->questions()->create($data);

        if ($request->question_type !== 'essay' && $request->has('answers')) {
            foreach ($request->answers as $i => $a) {
                $bq->answers()->create([
                    'answer_text' => $a['text'],
                    'is_correct' => (int) $i === (int) $request->correct_answer,
                    'order' => $i,
                ]);
            }
        }

        return back()->with('success', 'Soal berhasil ditambahkan ke bank.');
    }

    public function editQuestion(QuestionBank $questionBank, BankQuestion $bankQuestion)
    {
        $this->authorizeBank($questionBank);
        if ($bankQuestion->question_bank_id !== $questionBank->id) abort(404);
        $bankQuestion->load('answers');
        return view('creator.question-bank.questions.edit', compact('questionBank', 'bankQuestion'));
    }

    public function updateQuestion(Request $request, QuestionBank $questionBank, BankQuestion $bankQuestion)
    {
        $this->authorizeBank($questionBank);
        if ($bankQuestion->question_bank_id !== $questionBank->id) abort(404);

        $rules = [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,essay',
            'points' => 'required|integer|min:1',
            'tags' => 'nullable|string',
            'question_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ];
        if ($request->question_type !== 'essay') {
            $rules['answers'] = 'required|array|min:2';
            $rules['answers.*.text'] = 'required|string';
            $rules['correct_answer'] = 'required|integer';
        }
        $request->validate($rules);

        $tags = $request->filled('tags')
            ? array_map('trim', array_filter(explode(',', $request->tags)))
            : null;

        $data = ['question_text' => $request->question_text, 'question_type' => $request->question_type, 'points' => $request->points, 'tags' => $tags];
        if ($request->hasFile('question_image')) {
            if ($bankQuestion->question_image) Storage::disk('public')->delete($bankQuestion->question_image);
            $data['question_image'] = $request->file('question_image')->store('bank-questions', 'public');
        }
        if ($request->boolean('remove_image') && !$request->hasFile('question_image')) {
            if ($bankQuestion->question_image) Storage::disk('public')->delete($bankQuestion->question_image);
            $data['question_image'] = null;
        }

        $bankQuestion->update($data);
        $bankQuestion->answers()->delete();

        if ($request->question_type !== 'essay' && $request->has('answers')) {
            foreach ($request->answers as $i => $a) {
                $bankQuestion->answers()->create([
                    'answer_text' => $a['text'],
                    'is_correct' => (int) $i === (int) $request->correct_answer,
                    'order' => $i,
                ]);
            }
        }

        return back()->with('success', 'Soal berhasil diupdate.');
    }

    public function destroyQuestion(QuestionBank $questionBank, BankQuestion $bankQuestion)
    {
        $this->authorizeBank($questionBank);
        if ($bankQuestion->question_bank_id !== $questionBank->id) abort(404);

        if ($bankQuestion->question_image) {
            Storage::disk('public')->delete($bankQuestion->question_image);
        }
        $bankQuestion->delete();
        return back()->with('success', 'Soal berhasil dihapus dari bank.');
    }

    public function addToExam(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'count' => 'required|integer|min:1|max:100',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $this->authorizeExam($exam);

        $count = (int) $request->count;
        $pool = $questionBank->questions()->with('answers')->inRandomOrder()->limit($count * 2)->get();

        if ($pool->count() < $count) {
            return back()->with('error', "Bank hanya memiliki {$pool->count()} soal. Tidak cukup untuk mengambil {$count} soal.");
        }

        $selected = $pool->shuffle()->take($count);
        $order = $exam->questions()->max('order') ?? 0;

        foreach ($selected as $bq) {
            $order++;
            $imagePath = null;
            if ($bq->question_image && Storage::disk('public')->exists($bq->question_image)) {
                $ext = pathinfo($bq->question_image, PATHINFO_EXTENSION) ?: 'jpg';
                $imagePath = 'questions/' . \Illuminate\Support\Str::uuid() . '.' . $ext;
                Storage::disk('public')->copy($bq->question_image, $imagePath);
            }
            $q = $exam->questions()->create([
                'question_text' => $bq->question_text,
                'question_image' => $imagePath,
                'question_type' => $bq->question_type,
                'points' => $bq->points,
                'order' => $order,
            ]);
            foreach ($bq->answers as $ba) {
                $q->answers()->create([
                    'answer_text' => $ba->answer_text,
                    'is_correct' => $ba->is_correct,
                    'order' => $ba->order,
                ]);
            }
        }

        return redirect()->route('creator.exams.questions', $exam)
            ->with('success', "{$count} soal acak dari bank berhasil ditambahkan ke ujian.");
    }

    public function addSelectedToExam(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:bank_questions,id',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $this->authorizeExam($exam);

        $ids = array_filter(array_map('intval', $request->question_ids));
        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal 1 soal.');
        }
        $bankQuestions = $questionBank->questions()->with('answers')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($q) => array_search($q->id, $ids))
            ->values();

        if ($bankQuestions->isEmpty()) {
            return back()->with('error', 'Pilih minimal 1 soal.');
        }

        $order = $exam->questions()->max('order') ?? 0;
        foreach ($bankQuestions as $bq) {
            $order++;
            $imagePath = null;
            if ($bq->question_image && Storage::disk('public')->exists($bq->question_image)) {
                $ext = pathinfo($bq->question_image, PATHINFO_EXTENSION) ?: 'jpg';
                $imagePath = 'questions/' . \Illuminate\Support\Str::uuid() . '.' . $ext;
                Storage::disk('public')->copy($bq->question_image, $imagePath);
            }
            $q = $exam->questions()->create([
                'question_text' => $bq->question_text,
                'question_image' => $imagePath,
                'question_type' => $bq->question_type,
                'points' => $bq->points,
                'order' => $order,
            ]);
            foreach ($bq->answers as $ba) {
                $q->answers()->create([
                    'answer_text' => $ba->answer_text,
                    'is_correct' => $ba->is_correct,
                    'order' => $ba->order,
                ]);
            }
        }

        $count = $bankQuestions->count();
        return redirect()->route('creator.exams.questions', $exam)
            ->with('success', "{$count} soal berhasil ditambahkan ke ujian.");
    }

    public function import(Request $request, QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);

        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $import = new BankQuestionsImport($questionBank->id);
            Excel::import($import, $request->file('file'));
            $count = $import->getImportedCount();
            $errors = $import->getErrors();
            $msg = "{$count} soal berhasil diimpor ke bank.";
            if (!empty($errors)) {
                $msg .= ' ' . count($errors) . ' baris dilewati.';
            }
            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        }
    }

    public function export(QuestionBank $questionBank)
    {
        $this->authorizeBank($questionBank);
        return Excel::download(
            new \App\Exports\BankQuestionsExport($questionBank),
            'bank-soal-' . \Str::slug($questionBank->name) . '.xlsx'
        );
    }

    private function authorizeBank(QuestionBank $bank): void
    {
        if ($bank->created_by !== auth()->id()) abort(403);
    }

    private function authorizeExam(Exam $exam): void
    {
        if ($exam->created_by !== auth()->id()) abort(403);
    }
}
