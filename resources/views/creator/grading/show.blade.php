@extends('layouts.app')
@section('title', 'Penilaian Essai')
@section('header', 'Penilaian Essai')
@section('header-actions')
    <a href="{{ route('creator.exams.results', $exam) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Log Aktivitas (Cheating) --}}
    @if($session->activityLogs->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
        <h4 class="font-semibold text-amber-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Log Aktivitas Mencurigakan
        </h4>
        <ul class="space-y-1 text-sm">
            @foreach($session->activityLogs as $log)
            <li class="flex items-center gap-2 text-amber-700">
                <span class="text-amber-500">{{ $log->created_at->format('H:i:s') }}</span>
                <span>–</span>
                @switch($log->event)
                    @case('tab_switch') <span>Pindah fokus dari tab ujian</span> @break
                    @case('right_click') <span>Klik kanan di area ujian</span> @break
                    @case('copy_attempt') <span>Percobaan copy/cut di luar area yang diizinkan</span> @break
                    @case('paste_attempt') <span>Percobaan paste ke dalam ujian</span> @break
                    @case('rate_limit') <span>Pengajuan jawaban berulang dalam waktu sangat singkat (rate limit)</span> @break
                    @case('time_up_attempt') <span>Percobaan submit setelah waktu ujian berakhir</span> @break
                    @case('split_screen') <span>Perubahan ukuran layar yang mirip split screen / multi-window</span> @break
                    @case('window_blur') <span>Jendela ujian tertutup / tertimpa jendela lain</span> @break
                    @case('fullscreen_exit') <span>Keluar dari mode layar penuh selama ujian</span> @break
                    @case('screenshot_attempt') <span>Percobaan pengambilan screenshot (Print Screen)</span> @break
                    @case('print_attempt') <span>Percobaan print / simpan halaman (Ctrl+P)</span> @break
                    @default <span>{{ $log->event }}</span>
                @endswitch
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Info Peserta --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center text-white text-lg font-semibold">
                {{ strtoupper(substr($session->user->name, 0, 1)) }}
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">{{ $session->user->name }}</h3>
                <p class="text-sm text-slate-500">{{ $session->user->email }}</p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-sm text-slate-500">Ujian: <span class="font-medium text-slate-700">{{ $exam->title }}</span></p>
                <p class="text-sm text-slate-500">Skor saat ini: <span class="font-bold text-indigo-600">{{ $session->score }}</span></p>
            </div>
        </div>
    </div>

    @if($essayAnswers->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-slate-400">Tidak ada soal essai yang perlu dinilai.</p>
        </div>
    @else
        <form method="POST" action="{{ route('creator.grading.update', [$exam, $session]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @foreach($essayAnswers as $ua)
                <div class="bg-white rounded-2xl border {{ $ua->is_graded ? 'border-emerald-200' : 'border-amber-200' }} shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-sm font-bold">{{ $ua->question->order }}</span>
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-600">Essai</span>
                        <span class="text-xs text-slate-400">Maks. {{ $ua->question->points }} poin</span>
                        @if($ua->is_graded)
                            <span class="ml-auto px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-600">Sudah dinilai</span>
                        @else
                            <span class="ml-auto px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-600">Belum dinilai</span>
                        @endif
                        @if($ua->time_spent_seconds)
                            <span class="text-xs text-slate-400">Waktu: {{ floor($ua->time_spent_seconds / 60) }}m {{ $ua->time_spent_seconds % 60 }}s</span>
                        @endif
                    </div>

                    <p class="text-sm text-slate-700 mb-3 font-medium math-content">{{ $ua->question->question_text }}</p>

                    @if($ua->question->question_image)
                        @php
                            $img = $ua->question->question_image;
                            $imgSrc = \Illuminate\Support\Str::startsWith($img, ['http://', 'https://'])
                                ? $img
                                : asset('storage/' . $img);
                        @endphp
                        <div class="mb-3">
                            <img src="{{ $imgSrc }}" alt="Gambar soal" class="img-previewable max-h-48 rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition">
                        </div>
                    @endif

                    <div class="px-4 py-3 rounded-lg bg-slate-50 border border-slate-200 mb-4">
                        <p class="text-xs font-medium text-slate-500 mb-1">Jawaban Peserta:</p>
                        <p class="text-sm text-slate-700 whitespace-pre-wrap math-content">{{ $ua->essay_text ?? '-' }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-slate-700">Skor:</label>
                        <input type="number" name="scores[{{ $ua->id }}]" value="{{ $ua->essay_score ?? 0 }}"
                            min="0" max="{{ $ua->question->points }}"
                            class="w-24 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm text-center font-semibold">
                        <span class="text-sm text-slate-400">/ {{ $ua->question->points }}</span>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                Simpan Penilaian
            </button>
        </form>
    @endif
</div>
@endsection
