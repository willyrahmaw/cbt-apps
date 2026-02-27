@extends('layouts.app')
@section('title', 'Mengerjakan Ujian')
@section('header', $exam->title)
@section('header-actions')
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5">
            <button type="button" id="cbt-font-dec" class="px-2.5 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition text-xs font-semibold" title="Kecilkan font soal (Ctrl+←)" aria-label="Kecilkan font soal">
                A-
            </button>
            <button type="button" id="cbt-font-reset" class="px-2.5 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition text-xs font-semibold" title="Reset font (Ctrl+Home)" aria-label="Reset font soal">
                A
            </button>
            <button type="button" id="cbt-font-inc" class="px-2.5 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition text-xs font-semibold" title="Besarkan font soal (Ctrl+→)" aria-label="Besarkan font soal">
                A+
            </button>
            <button type="button" id="cbt-contrast-toggle" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition text-xs font-semibold" aria-pressed="false" title="Mode kontras tinggi (Ctrl+Shift+H)" aria-label="Toggle mode kontras tinggi">
                Kontras
            </button>
        </div>
        <button type="button" id="fullscreen-btn" onclick="toggleFullscreen()" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" title="Mode layar penuh">
            <svg id="fullscreen-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
        </button>
        <div id="timer" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 font-mono font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="timer-display">--:--</span>
        </div>
        <form method="POST" action="{{ route('student.exams.finish', $session) }}" id="finish-form">
            @csrf
            <button type="button" onclick="tryFinishExam()"
                class="px-4 py-2 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">
                Selesai
            </button>
        </form>
    </div>
@endsection

@section('content')
@php
    $wmUser = $session->user;
    $wmBase = trim(($wmUser->name ?? 'Peserta') . ' • ' . ($wmUser->email ?? '') . ' • Sesi #' . $session->id);
@endphp
    <div id="exam-page" class="relative z-10 flex flex-col lg:flex-row gap-6 select-none" data-session-id="{{ $session->id }}">

    {{-- Info Pengguna (kiri) --}}
    <div class="lg:w-64 lg:flex-shrink-0 order-1">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full shrink-0 overflow-hidden bg-indigo-500 flex items-center justify-center border border-slate-200">
                @if($session->user->avatar)
                    <img src="{{ asset('storage/' . $session->user->avatar) }}" alt="{{ $session->user->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-white font-semibold text-base">{{ strtoupper(substr($session->user->name ?? 'P', 0, 1)) }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-base font-semibold text-slate-800 truncate">{{ $session->user->name }}</p>
                <p class="text-sm text-slate-500">
                    NIS: <span class="font-semibold text-slate-700">{{ $session->user->nis ?? '-' }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Soal (tengah) + Navigasi (kanan) --}}
    <div class="flex-1 order-2 flex flex-col lg:flex-row gap-6 items-start min-w-0">
        {{-- Soal (tengah) --}}
        <div class="flex-1 w-full min-w-0">
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

                    <p class="text-slate-800 text-base mb-4 leading-relaxed math-content cbt-qtext">{{ $question->question_text }}</p>

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
                        <div>
                            <textarea
                                id="essay-{{ $question->id }}"
                                class="cbt-essay w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm resize-y min-h-[120px] select-text"
                                placeholder="Tulis jawaban uraian Anda di sini (otomatis tersimpan, paste dinonaktifkan)..."
                                data-session="{{ $session->id }}"
                                data-question="{{ $question->id }}"
                                onpaste="event.preventDefault()">{{ $essayAnswers[$question->id] ?? '' }}</textarea>
                            <p class="mt-1.5 text-xs text-slate-400">Jawaban tersimpan otomatis saat Anda mengetik</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($question->answers as $ai => $answer)
                                @php $state = $answerStates[$question->id] ?? null; $isChecked = $state && $state->answer_id == $answer->id; @endphp
                                <label class="answer-option flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                                    {{ in_array($question->id, $answeredIds) ? '' : 'border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50' }} {{ $isChecked ? 'border-indigo-500 bg-indigo-50' : 'border-slate-100' }}"
                                    data-session="{{ $session->id }}" data-question="{{ $question->id }}" data-answer="{{ $answer->id }}">
                                    <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}"
                                        class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                        {{ $isChecked ? 'checked' : '' }}
                                        onchange="saveMcAnswer({{ $session->id }}, {{ $question->id }}, {{ $answer->id }}, this)">
                                    <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-sm font-semibold text-slate-500 shrink-0">{{ chr(65 + $ai) }}</span>
                                    <span class="text-sm text-slate-700 math-content cbt-atext">{{ $answer->answer_text }}</span>
                                </label>
                            @endforeach
                            <div class="mt-3 pt-3 border-t border-slate-100">
                                <label class="flex items-center gap-2 cursor-pointer select-text">
                                    <input type="checkbox" id="ragu-{{ $question->id }}" class="ragu-checkbox w-4 h-4 text-amber-500 border-slate-300 rounded focus:ring-amber-500"
                                        data-session="{{ $session->id }}" data-question="{{ $question->id }}"
                                        {{ in_array($question->id, $raguQuestionIds ?? []) ? 'checked' : '' }}
                                        onchange="saveRagu({{ $session->id }}, {{ $question->id }}, this)"
                                        {{ !in_array($question->id, $answeredIds) ? 'disabled' : '' }}>
                                    <span class="text-sm text-amber-700 cbt-atext">Ragu-ragu dengan jawaban ini</span>
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- Navigation Buttons --}}
                    <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="goToQuestion({{ $qi - 1 }})" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition {{ $qi === 0 ? 'invisible' : '' }}">
                            Sebelumnya
                        </button>
                        @if($qi < $questions->count() - 1)
                            <button type="button" onclick="goToQuestion({{ $qi + 1 }})" class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-sm font-medium hover:bg-indigo-100 transition">
                                Selanjutnya
                            </button>
                        @else
                            <button type="button" onclick="tryFinishExam()" class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition">
                                Selesaikan Ujian
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Watermark di bawah soal, di luar card --}}
            <div class="exam-watermark mt-4 py-8 text-center text-slate-300 text-xs select-none pointer-events-none" aria-hidden="true">
                <span>{{ $wmBase }}</span>
                <span class="wm-time ml-1"></span>
            </div>
        </div>

        {{-- Navigasi Soal (kanan) --}}
        <div class="lg:w-48 lg:flex-shrink-0 order-3 lg:sticky lg:top-24">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3">
                <h4 class="font-semibold text-slate-700 mb-2 text-xs">Navigasi Soal</h4>
                <div class="flex flex-wrap gap-1.5" id="question-nav">
                    @foreach($questions as $qi => $q)
                        @php
                            $status = in_array($q->id, $raguQuestionIds ?? [])
                                ? 'ragu'
                                : (in_array($q->id, $answeredIds) ? 'answered' : 'unanswered');
                        @endphp
                        <button type="button" onclick="goToQuestion({{ $qi }})"
                            class="question-nav-btn w-8 h-8 shrink-0 rounded-md text-[10px] font-semibold transition flex items-center justify-center
                            {{ $status === 'ragu' ? 'bg-amber-100 text-amber-700 border border-amber-200' : ($status === 'answered' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200') }}"
                            data-index="{{ $qi }}" data-qid="{{ $q->id }}"
                            data-is-ragu="{{ $status === 'ragu' ? '1' : '0' }}"
                            data-status="{{ $status }}">
                            {{ $qi + 1 }}
                        </button>
                    @endforeach
                </div>
                <div class="mt-2 space-y-0.5 text-[10px] text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded shrink-0 bg-emerald-100 border border-emerald-200"></span> Sudah dijawab</div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded shrink-0 bg-amber-100 border border-amber-200"></span> Ragu-ragu</div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded shrink-0 bg-slate-100 border border-slate-200"></span> Belum dijawab</div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded shrink-0 bg-indigo-100 border border-indigo-300"></span> Sedang dilihat</div>
                </div>
            </div>
        </div>
    </div>
    </div>

@push('scripts')
<script>
window.examTakeConfig = {
    remainingSeconds: {{ $session->remaining_time }},
    sessionId: {{ $session->id }},
    logUrl: "/student/session/{{ $session->id }}/log",
    remainingUrl: "/student/session/{{ $session->id }}/remaining",
    finishFormId: "finish-form"
};
</script>
@vite(['resources/js/pages/exam-take.js'])
@endpush
@endsection
