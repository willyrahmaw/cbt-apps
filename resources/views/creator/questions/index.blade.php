@extends('layouts.app')
@section('title', 'Kelola Soal')
@section('header', 'Soal: ' . $exam->title)
@section('header-actions')
    <a href="{{ route('creator.exams.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Form Tambah / Edit Soal --}}
    <div class="lg:col-span-1">
        <div id="form-card" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sticky top-20">
            <div class="flex items-center justify-between mb-4">
                <h3 id="form-title" class="font-semibold text-slate-800">Tambah Soal</h3>
                <button type="button" id="cancel-edit-btn" class="hidden px-3 py-1 rounded-lg bg-slate-100 text-slate-500 text-xs font-medium hover:bg-slate-200 transition" onclick="cancelEdit()">
                    Batal Edit
                </button>
            </div>
            <form id="question-form" method="POST" action="{{ route('creator.questions.store', $exam) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="remove_image" id="remove-image-flag" value="0">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Pertanyaan</label>
                    @if($isMathOrPhysics ?? false)
                        <div class="mb-2">
                            <p class="text-xs text-indigo-600 font-medium mb-1.5">Simbol Matematika/Fisika: gunakan $...$ untuk inline, $$...$$ untuk blok</p>
                            <div class="flex flex-wrap gap-1" id="latex-toolbar">
                                <button type="button" onclick="insertLatexAtCursor('\\\\frac{a}{b}')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700" title="Pecahan">a/b</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\sqrt{x}')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700" title="Akar">√</button>
                                <button type="button" onclick="insertLatexAtCursor('x^2')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700" title="Pangkat">x²</button>
                                <button type="button" onclick="insertLatexAtCursor('x_i')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700" title="Subscript">xᵢ</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\pi')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">π</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\alpha')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">α</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\theta')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">θ</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\sum_{i=1}^{n}')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">Σ</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\int_{a}^{b}')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">∫</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\leq')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">≤</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\geq')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">≥</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\times')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">×</button>
                                <button type="button" onclick="insertLatexAtCursor('\\\\neq')" class="px-2 py-1 rounded text-xs bg-slate-100 hover:bg-slate-200 text-slate-700">≠</button>
                                <button type="button" onclick="toggleLatexHelp()" class="px-2 py-1 rounded text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700">?</button>
                            </div>
                            <div id="latex-help" class="hidden mt-2 p-2 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-0.5">
                                <p><strong>Contoh:</strong> Tulis $\frac{1}{2}$ untuk ½, $\sqrt{2}$ untuk √2, $x^2$ untuk x²</p>
                                <p>\frac{a}{b} pecahan | \sqrt{x} akar | ^ pangkat | _ subscript | \pi \alpha \theta \sum \int</p>
                            </div>
                        </div>
                    @endif
                    <textarea name="question_text" id="field-question-text" rows="3" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm resize-none"
                        placeholder="{{ ($isMathOrPhysics ?? false) ? 'Tulis pertanyaan. Untuk simbol: $\\frac{1}{2}$ atau $x^2$' : 'Tulis pertanyaan di sini...' }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <label class="block cursor-pointer">
                        <div id="image-upload-area" class="border-2 border-dashed border-slate-200 rounded-xl p-3 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition">
                            <div id="image-placeholder">
                                <svg class="w-6 h-6 mx-auto text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-xs text-slate-400">Klik untuk pilih gambar</p>
                                <p class="text-xs text-slate-300">JPG, PNG, GIF, WebP (maks. 2MB)</p>
                            </div>
                            <div id="image-preview-container" class="hidden">
                                <img id="image-preview" class="max-h-32 mx-auto rounded-lg" alt="Preview">
                                <p id="image-filename" class="text-xs text-slate-500 mt-1 truncate"></p>
                            </div>
                        </div>
                        <input type="file" name="question_image" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="previewImage(this)">
                    </label>
                    <button type="button" id="remove-image-btn" class="hidden mt-1 text-xs text-red-500 hover:text-red-700" onclick="removeImagePreview()">Hapus gambar</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                        <select name="question_type" id="question-type" onchange="updateAnswerFields()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="true_false">Benar/Salah</option>
                            <option value="essay">Essai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Poin</label>
                        <input type="number" name="points" id="field-points" value="1" min="1"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    </div>
                </div>

                <div id="answers-container">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban</label>
                    @if($isMathOrPhysics ?? false)
                        <p class="text-xs text-slate-500 mb-1.5">Klik di field pertanyaan atau jawaban, lalu gunakan tombol di atas untuk menyisipkan simbol. Atau ketik manual: $...$</p>
                    @endif
                    <div class="space-y-2" id="answer-fields">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_answer" value="0" checked class="w-4 h-4 text-indigo-600 border-slate-300">
                            <input type="text" name="answers[0][text]" required placeholder="Jawaban A"
                                class="flex-1 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_answer" value="1" class="w-4 h-4 text-indigo-600 border-slate-300">
                            <input type="text" name="answers[1][text]" required placeholder="Jawaban B"
                                class="flex-1 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_answer" value="2" class="w-4 h-4 text-indigo-600 border-slate-300">
                            <input type="text" name="answers[2][text]" required placeholder="Jawaban C"
                                class="flex-1 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_answer" value="3" class="w-4 h-4 text-indigo-600 border-slate-300">
                            <input type="text" name="answers[3][text]" required placeholder="Jawaban D"
                                class="flex-1 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-2" id="answer-hint">Pilih radio button untuk jawaban yang benar</p>
                </div>

                <button type="submit" id="submit-btn" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Tambah Soal
                </button>
            </form>

            {{-- Tambah dari Bank Soal --}}
            @php $banks = \App\Models\QuestionBank::where('created_by', auth()->id())->withCount('questions')->having('questions_count', '>', 0)->get(); @endphp
            @if($banks->isNotEmpty())
                <div class="mb-6 pb-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 mb-3">Tambah dari Bank Soal</h3>
                    <p class="text-xs text-slate-500 mb-2">Pilih soal dari bank yang sudah Anda buat. Buka bank, centang soal, pilih ujian, lalu tambah.</p>
                    <div class="space-y-1">
                        @foreach($banks as $b)
                            <a href="{{ route('creator.question-bank.show', $b) }}?exam_id={{ $exam->id }}" class="flex items-center justify-between gap-2 py-2 px-3 rounded-lg hover:bg-indigo-50 text-slate-700 text-sm group">
                                <span>{{ $b->name }}</span>
                                <span class="text-slate-400 text-xs">{{ $b->questions_count }} soal</span>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Import Excel --}}
            <div class="mt-6 pt-6 border-t border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">Impor dari Excel</h3>
                <form method="POST" action="{{ route('creator.questions.import', $exam) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block w-full cursor-pointer">
                            <div id="drop-zone" class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition">
                                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-sm text-slate-500" id="file-label">Pilih file .xlsx / .xls</p>
                                <p class="text-xs text-slate-400 mt-1">Maks. 5MB</p>
                            </div>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required
                                onchange="document.getElementById('file-label').textContent = this.files[0]?.name || 'Pilih file .xlsx / .xls'">
                        </label>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition">
                        Impor Soal
                    </button>
                </form>
                <a href="{{ route('creator.questions.template') }}" class="mt-2 w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Template
                </a>
            </div>
        </div>
    </div>

    {{-- Daftar Soal --}}
    <div class="lg:col-span-2 space-y-4">
        @if(session('import_errors'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm">
                <p class="font-medium mb-1">Beberapa baris dilewati saat impor:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-between mb-2">
            <p class="text-sm text-slate-500">Total: <span class="font-semibold text-slate-700">{{ $exam->questions->count() }} soal</span></p>
        </div>

        @forelse($exam->questions as $index => $question)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 question-card" id="question-card-{{ $question->id }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold">{{ $index + 1 }}</span>
                        @php
                            $typeBadge = match($question->question_type) {
                                'multiple_choice' => ['bg-blue-50 text-blue-600', 'Pilihan Ganda'],
                                'true_false' => ['bg-purple-50 text-purple-600', 'Benar/Salah'],
                                'essay' => ['bg-amber-50 text-amber-600', 'Essai'],
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $typeBadge[0] }}">
                            {{ $typeBadge[1] }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $question->points }} poin</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('creator.questions.duplicate', [$exam, $question]) }}" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Duplikasi soal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </form>
                        <button type="button" onclick='editQuestion(@json($question->id))' class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit soal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('creator.questions.destroy', [$exam, $question]) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="button" onclick="confirmDelete(this.closest('form'), 'Soal ini akan dihapus beserta jawabannya.')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition" title="Hapus soal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-sm text-slate-700 mb-3 math-content">{{ $question->question_text }}</p>

                @if($question->question_image)
                    @php
                        $img = $question->question_image;
                        $imgSrc = \Illuminate\Support\Str::startsWith($img, ['http://', 'https://'])
                            ? $img
                            : asset('storage/' . $img);
                    @endphp
                    <div class="mb-3">
                        <img src="{{ $imgSrc }}" alt="Gambar soal" class="img-previewable max-h-48 rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition">
                    </div>
                @endif

                @if($question->question_type === 'essay')
                    <div class="px-3 py-3 rounded-lg bg-amber-50 border border-amber-100 text-sm text-amber-700">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Jawaban berupa teks uraian (dinilai manual oleh guru)
                        </div>
                    </div>
                @else
                    <div class="space-y-1.5">
                        @foreach($question->answers as $ai => $answer)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $answer->is_correct ? 'bg-emerald-50 border border-emerald-200 text-emerald-700 font-medium' : 'bg-slate-50 text-slate-600' }}">
                                <span class="w-6 h-6 rounded-full {{ $answer->is_correct ? 'bg-emerald-200 text-emerald-800' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-semibold">
                                    {{ chr(65 + $ai) }}
                                </span>
                                <span class="math-content">{{ $answer->answer_text }}</span>
                                @if($answer->is_correct)
                                    <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-slate-400">Belum ada soal. Tambahkan soal pertama di form sebelah kiri.</p>
            </div>
        @endforelse
    </div>
</div>

@php
    $questionsJson = $exam->questions->mapWithKeys(function($q) {
        return [$q->id => [
            'id' => $q->id,
            'question_text' => $q->question_text,
            'question_type' => $q->question_type,
            'question_image' => $q->question_image,
            'points' => $q->points,
            'answers' => $q->answers->map(fn($a) => [
                'text' => $a->answer_text,
                'is_correct' => $a->is_correct,
            ])->values(),
        ]];
    });
@endphp

@push('scripts')
<script>
    window.creatorQuestionsConfig = {
        storeUrl: "{{ route('creator.questions.store', $exam) }}",
        updateUrlBase: "{{ url('creator/exams/' . $exam->id . '/questions') }}",
        storageUrl: "{{ asset('storage') }}",
        questions: @json($questionsJson),
    };
</script>
@vite(['resources/js/creator/questions-index.js'])
@endpush
@endsection
