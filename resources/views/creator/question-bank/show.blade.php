@extends('layouts.app')
@section('title', $questionBank->name)
@section('header', 'Bank: ' . $questionBank->name)
@section('header-actions')
    <a href="{{ route('creator.question-bank.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">Kembali</a>
    <a href="{{ route('creator.question-bank.export', $questionBank) }}" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">Ekspor Excel</a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="get" class="flex flex-wrap gap-2">
            <input type="hidden" name="bank" value="{{ $questionBank->id }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari soal..." class="px-3 py-2 rounded-lg border border-slate-200 text-sm w-48">
            @if($allTags->isNotEmpty())
                <select name="tag" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
                    <option value="">Semua tag</option>
                    @foreach($allTags as $tag)
                        <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm">Cari</button>
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('creator.question-bank.questions.create', $questionBank) }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Tambah Soal</a>
            <a href="{{ route('creator.question-bank.edit', $questionBank) }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm hover:bg-slate-50">Edit Bank</a>
        </div>
    </div>

    {{-- Tambah ke Ujian (Pilih soal) --}}
    @php
        $myExams = \App\Models\Exam::where('created_by', auth()->id())->orderBy('title')->get();
        $preselectExamId = request('exam_id');
    @endphp
    @if($myExams->isNotEmpty() && $questionBank->questions()->count() > 0)
        <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
            <p class="text-sm font-medium text-indigo-800 mb-2">Tambah soal ke ujian</p>
            <p class="text-xs text-indigo-600 mb-3">Centang soal yang ingin ditambahkan, pilih ujian, lalu klik Tambah. Soal akan disalin ke ujian.</p>
            <form id="add-to-exam-form" method="POST" action="{{ route('creator.question-bank.add-selected-to-exam', $questionBank) }}">
                @csrf
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <select name="exam_id" required class="px-3 py-2 rounded-lg border border-indigo-200 text-sm w-64">
                        <option value="">Pilih ujian</option>
                        @foreach($myExams as $e)
                            <option value="{{ $e->id }}" {{ $preselectExamId == $e->id ? 'selected' : '' }}>{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" id="add-selected-btn" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">Tambah yang dipilih</button>
                </div>
            </form>
            <details class="mt-2">
                <summary class="text-xs text-indigo-600 cursor-pointer hover:underline">Atau: tambah N soal acak</summary>
                <form method="POST" action="{{ route('creator.question-bank.add-to-exam', $questionBank) }}" class="flex flex-wrap items-center gap-2 mt-2">
                    @csrf
                    <select name="exam_id" required class="px-3 py-2 rounded-lg border border-indigo-200 text-sm w-64">
                        <option value="">Pilih ujian</option>
                        @foreach($myExams as $e)
                            <option value="{{ $e->id }}" {{ $preselectExamId == $e->id ? 'selected' : '' }}>{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="count" value="10" min="1" max="100" class="px-3 py-2 rounded-lg border border-indigo-200 text-sm w-20">
                    <span class="text-sm text-slate-600">soal acak</span>
                    <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-500/80 text-white text-sm">Tambah acak</button>
                </form>
            </details>
        </div>
    @endif

    {{-- Import --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-sm font-medium text-slate-700 mb-2">Impor soal dari Excel</p>
        <form method="POST" action="{{ route('creator.question-bank.import', $questionBank) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="text-sm">
            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">Impor</button>
        </form>
        <a href="{{ route('creator.questions.template') }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">Download template</a>
    </div>

    @if(session('import_errors'))
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm">
            <p class="font-medium mb-1">Beberapa baris dilewati:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Daftar Soal --}}
    <div>
        <p class="text-sm text-slate-500 mb-4">Total: {{ $questions->total() }} soal</p>

        @forelse($questions as $i => $bq)
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-4 flex gap-3">
                <div class="pt-0.5 flex-shrink-0">
                    <label class="cursor-pointer" title="Centang untuk tambah ke ujian">
                        <input type="checkbox" name="question_ids[]" value="{{ $bq->id }}" form="add-to-exam-form" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    </label>
                </div>
                <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold">{{ ($questions->currentPage()-1)*$questions->perPage() + $i + 1 }}</span>
                        @php
                            $t = match($bq->question_type) {
                                'multiple_choice' => ['bg-blue-50 text-blue-600', 'Pilihan Ganda'],
                                'true_false' => ['bg-purple-50 text-purple-600', 'Benar/Salah'],
                                'essay' => ['bg-amber-50 text-amber-600', 'Essai'],
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $t[0] }}">{{ $t[1] }}</span>
                        <span class="text-xs text-slate-400">{{ $bq->points }} poin</span>
                        @if(!empty($bq->tags))
                            @foreach($bq->tags as $tag)
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">{{ $tag }}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('creator.question-bank.questions.edit', [$questionBank, $bq]) }}" class="p-1.5 rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Edit">✎</a>
                        <form method="POST" action="{{ route('creator.question-bank.questions.destroy', [$questionBank, $bq]) }}">
                            @csrf @method('DELETE')
                            <button type="button" onclick="confirmDelete(this.closest('form'), 'Soal akan dihapus dari bank.')" class="p-1.5 rounded text-slate-400 hover:text-red-500 hover:bg-red-50">🗑</button>
                        </form>
                    </div>
                </div>
                <p class="text-sm text-slate-700 math-content">{{ Str::limit(strip_tags($bq->question_text), 200) }}</p>
                @if($bq->question_image)
                    @php
                        $img = $bq->question_image;
                        $imgSrc = \Illuminate\Support\Str::startsWith($img, ['http://', 'https://'])
                            ? $img
                            : asset('storage/' . $img);
                    @endphp
                    <img src="{{ $imgSrc }}" alt="" class="mt-2 max-h-32 rounded-lg border border-slate-200">
                @endif
                @if($bq->question_type !== 'essay' && $bq->answers->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($bq->answers as $ai => $a)
                            <span class="px-2 py-1 rounded text-xs {{ $a->is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ chr(65+$ai) }}. {{ Str::limit($a->answer_text, 40) }}{{ $a->is_correct ? ' ✓' : '' }}</span>
                        @endforeach
                    </div>
                @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-xl border border-slate-100">
                <p class="text-slate-400 mb-4">Belum ada soal. Impor dari Excel atau tambah manual.</p>
            </div>
        @endforelse
    </div>

    @if($questions->hasPages())
        <div class="mt-4">{{ $questions->links() }}</div>
    @endif
</div>

@if($questionBank->questions()->count() > 0)
@push('scripts')
@vite(['resources/js/creator/question-bank-show.js'])
@endpush
@endif
@endsection
