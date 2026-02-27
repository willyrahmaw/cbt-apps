@extends('layouts.app')
@section('title', 'Hasil Ujian')
@section('header', 'Hasil Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Score Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
        <div class="h-2 {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
        <div class="p-8 text-center">
            <div class="w-24 h-24 mx-auto rounded-full {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-300' }} flex items-center justify-center text-4xl font-bold mb-4">
                {{ $session->score }}
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-1">{{ $session->exam->title }}</h2>
            <p class="text-slate-500 mb-4">{{ $session->exam->category->name ?? '' }}</p>

            @if($session->status === 'terminated')
                <span class="inline-block px-4 py-2 rounded-xl text-sm font-semibold bg-rose-100 text-rose-700 mb-2">
                    UJIAN DIAKHIRI KARENA PELANGGARAN
                </span>
                <p class="text-xs text-slate-400">Mintalah token baru ke pengawas jika ingin mengulang ujian. Waktu sisa akan berlanjut.</p>
            @elseif($session->needs_grading)
                <span class="inline-block px-4 py-2 rounded-xl text-sm font-semibold bg-amber-100 text-amber-700 mb-2">
                    MENUNGGU PENILAIAN ESSAI
                </span>
                <p class="text-xs text-slate-400">Skor saat ini berdasarkan soal pilihan ganda & benar/salah. Skor final akan diperbarui setelah guru menilai soal essai.</p>
            @else
                <span class="inline-block px-4 py-2 rounded-xl text-sm font-semibold {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' }}">
                    {{ $session->score >= $session->exam->passing_score ? 'LULUS' : 'TIDAK LULUS' }}
                </span>
            @endif

            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                    <p class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $session->correct_answers }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Benar</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                    <p class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $session->total_questions - $session->correct_answers }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Salah/Belum Dinilai</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                    <p class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $session->total_questions }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Soal</p>
                </div>
            </div>
        </div>
    </div>

    @if($session->exam->show_result)
        {{-- Review Questions --}}
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Pembahasan Soal</h3>
        <div class="space-y-4">
            @foreach($session->exam->questions as $qi => $question)
                @php
                    $userAnswer = $session->userAnswers->firstWhere('question_id', $question->id);
                    $isEssay = $question->question_type === 'essay';
                    $isCorrect = $userAnswer && $userAnswer->is_correct;
                @endphp
                <div class="bg-white rounded-2xl border {{ $isEssay ? ($userAnswer && $userAnswer->is_graded ? ($userAnswer->essay_score > 0 ? 'border-emerald-200' : 'border-red-200') : 'border-amber-200') : ($isCorrect ? 'border-emerald-200' : 'border-red-200') }} shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-3">
                        @if($isEssay)
                            <span class="w-8 h-8 rounded-lg {{ $userAnswer && $userAnswer->is_graded ? ($userAnswer->essay_score > 0 ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-300') : 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-300' }} flex items-center justify-center text-sm font-bold">{{ $qi + 1 }}</span>
                            @if($userAnswer && $userAnswer->is_graded)
                                <span class="text-xs font-medium text-slate-500">Skor: {{ $userAnswer->essay_score }}/{{ $question->points }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-600">Belum dinilai</span>
                            @endif
                        @else
                            <span class="w-8 h-8 rounded-lg {{ $isCorrect ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-300' }} flex items-center justify-center text-sm font-bold">{{ $qi + 1 }}</span>
                            @if($isCorrect)
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        @endif
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $isEssay ? 'bg-amber-50 text-amber-600' : '' }}">
                            {{ $isEssay ? 'Essai' : '' }}
                        </span>
                        @if($userAnswer && $userAnswer->time_spent_seconds)
                            <span class="ml-auto text-xs text-slate-400">Waktu: {{ floor($userAnswer->time_spent_seconds / 60) }}m {{ $userAnswer->time_spent_seconds % 60 }}s</span>
                        @endif
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

                    @if($isEssay)
                        <div class="space-y-2">
                            <div class="px-4 py-3 rounded-lg bg-slate-50 border border-slate-200">
                                <p class="text-xs font-medium text-slate-500 mb-1">Jawaban Anda:</p>
                                <p class="text-sm text-slate-700 whitespace-pre-wrap math-content">{{ $userAnswer->essay_text ?? '-' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="space-y-1.5">
                            @foreach($question->answers as $ai => $answer)
                                @php
                                    $isUserChoice = $userAnswer && $userAnswer->answer_id === $answer->id;
                                    $classes = 'bg-slate-50 text-slate-600';
                                    if ($answer->is_correct) {
                                        $classes = 'bg-emerald-50 border border-emerald-200 text-emerald-700 font-medium';
                                    } elseif ($isUserChoice) {
                                        $classes = 'bg-red-50 border border-red-200 text-red-700';
                                    }
                                @endphp
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $classes }}">
                                    <span class="w-6 h-6 rounded-full {{ $answer->is_correct ? 'bg-emerald-200 text-emerald-800' : ($isUserChoice ? 'bg-red-200 text-red-800' : 'bg-slate-200 text-slate-500') }} flex items-center justify-center text-xs font-semibold">
                                        {{ chr(65 + $ai) }}
                                    </span>
                                    <span class="math-content">{{ $answer->answer_text }}</span>
                                    @if($answer->is_correct)
                                        <svg class="w-4 h-4 ml-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @elseif($isUserChoice)
                                        <svg class="w-4 h-4 ml-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
