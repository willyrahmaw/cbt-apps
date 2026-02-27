@extends('layouts.app')
@section('title', 'Preview: ' . $exam->title)
@section('header', 'Preview: ' . $exam->title)
@section('header-actions')
    <div class="flex items-center gap-3">
        <span class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-800 text-sm font-medium">Tampilan Siswa</span>
        <a href="{{ route('creator.exams.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
    </div>
@endsection

@section('content')
<div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200">
    <p class="text-sm text-amber-800">Ini tampilan ujian seperti yang akan dilihat siswa. Timer dan penyimpanan jawaban dinonaktifkan.</p>
</div>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1 order-2 lg:order-1">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 sticky top-20">
            <h4 class="font-semibold text-slate-700 mb-3 text-sm">Navigasi Soal</h4>
            <div class="grid grid-cols-5 gap-2" id="question-nav">
                @foreach($questions as $qi => $q)
                    <button onclick="goToPreviewQuestion({{ $qi }})"
                        class="question-nav-btn w-full aspect-square rounded-lg text-sm font-medium transition flex items-center justify-center bg-slate-100 text-slate-500 border border-slate-200"
                        data-index="{{ $qi }}">
                        {{ $qi + 1 }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    <div class="lg:col-span-3 order-1 lg:order-2">
        @foreach($questions as $qi => $question)
            <div class="question-panel bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-4 {{ $qi === 0 ? '' : 'hidden' }}" data-index="{{ $qi }}">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">{{ $qi + 1 }}</span>
                    <div>
                        <span class="text-xs text-slate-400">Soal {{ $qi + 1 }} dari {{ $questions->count() }}</span>
                        <span class="text-xs text-slate-400 ml-2">{{ $question->points }} poin</span>
                        @if($question->question_type === 'essay')
                            <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-600">Essai</span>
                        @endif
                    </div>
                </div>
                <p class="text-slate-800 text-base mb-4 leading-relaxed math-content">{{ $question->question_text }}</p>
                @if($question->question_image)
                    @php
                        $img = $question->question_image;
                        $imgSrc = \Illuminate\Support\Str::startsWith($img, ['http://', 'https://'])
                            ? $img
                            : asset('storage/' . $img);
                    @endphp
                    <div class="mb-5">
                        <img src="{{ $imgSrc }}" alt="Gambar soal" class="img-previewable max-h-64 rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition">
                    </div>
                @endif
                @if($question->question_type === 'essay')
                    <div class="px-4 py-3 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 text-slate-500 text-sm">
                        [Area jawaban essai - siswa mengetik di sini]
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($question->answers as $ai => $answer)
                            <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-100">
                                <input type="radio" disabled class="w-4 h-4 text-indigo-600 border-slate-300">
                                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-sm font-semibold text-slate-500 shrink-0">{{ chr(65 + $ai) }}</span>
                                <span class="text-sm text-slate-700 math-content">{{ $answer->answer_text }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                    <button onclick="goToPreviewQuestion({{ $qi - 1 }})" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition {{ $qi === 0 ? 'invisible' : '' }}">Sebelumnya</button>
                    @if($qi < $questions->count() - 1)
                        <button onclick="goToPreviewQuestion({{ $qi + 1 }})" class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-sm font-medium hover:bg-indigo-100 transition">Selanjutnya</button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@push('scripts')
@vite(['resources/js/creator/exams-preview.js'])
@endpush
@endsection
