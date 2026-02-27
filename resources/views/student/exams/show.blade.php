@extends('layouts.app')
@section('title', $exam->title)
@section('header', 'Detail Ujian')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="h-2 bg-indigo-600"></div>
        <div class="p-8">
            <div class="text-center mb-6">
                <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-medium">{{ $exam->category->name ?? 'Umum' }}</span>
                <h2 class="text-2xl font-bold text-slate-800 mt-3">{{ $exam->title }}</h2>
                @if($exam->description)
                    <p class="text-slate-500 mt-2">{{ $exam->description }}</p>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="text-center p-4 rounded-xl bg-slate-50">
                    <p class="text-2xl font-bold text-indigo-600">{{ $exam->questions_count }}</p>
                    <p class="text-xs text-slate-500 mt-1">Soal</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-slate-50">
                    <p class="text-2xl font-bold text-indigo-600">{{ $exam->duration }}</p>
                    <p class="text-xs text-slate-500 mt-1">Menit</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-slate-50">
                    <p class="text-2xl font-bold text-indigo-600">{{ $exam->passing_score }}</p>
                    <p class="text-xs text-slate-500 mt-1">KKM</p>
                </div>
            </div>

            @if($exam->start_time || $exam->end_time)
            <div class="mb-6 p-3 rounded-xl bg-indigo-50 border border-indigo-100 text-center text-sm text-indigo-800">
                @if($exam->start_time && $exam->end_time)
                    Waktu ujian: <strong>{{ $exam->start_time->format('d/m/Y H:i') }}</strong> s.d. <strong>{{ $exam->end_time->format('d/m/Y H:i') }}</strong>
                @elseif($exam->start_time)
                    Ujian bisa dimulai mulai <strong>{{ $exam->start_time->format('d/m/Y H:i') }}</strong>
                @else
                    Ujian ditutup paling lambat <strong>{{ $exam->end_time->format('d/m/Y H:i') }}</strong>
                @endif
            </div>
            @endif

            @if($completedCount > 0)
                {{-- Sudah pernah mengerjakan --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 mb-6 text-center">
                    <svg class="w-10 h-10 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-semibold text-emerald-800">Anda sudah mengerjakan ujian ini</p>
                    <p class="text-sm text-emerald-600 mt-1">Setiap ujian hanya bisa dikerjakan 1 kali.</p>
                </div>

                <div class="text-center">
                    <a href="{{ route('student.results.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Lihat Hasil
                    </a>
                </div>
            @else
                @if(isset($canStart) && !$canStart && $timeMessage)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6 text-center">
                    <svg class="w-10 h-10 mx-auto text-amber-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-semibold text-amber-800">{{ $timeMessage }}</p>
                </div>
                @endif
                @if($hasTerminated ?? false)
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-rose-800"><strong>Mengulang ujian:</strong> Masukkan token baru dari pengawas. Waktu ujian akan berlanjut dari sisa waktu sebelumnya.</p>
                </div>
                @endif
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <h4 class="font-semibold text-amber-800 mb-2">Peraturan Ujian:</h4>
                    <ul class="text-sm text-amber-700 space-y-1">
                        <li>- Ujian hanya bisa dikerjakan <strong>1 kali</strong></li>
                        <li>- Waktu ujian {{ $exam->duration }} menit dan dimulai saat Anda klik "Mulai Ujian"</li>
                        <li>- Jawaban tersimpan otomatis saat Anda memilih opsi</li>
                        <li>- Ujian akan otomatis berakhir saat waktu habis</li>
                        <li>- Pastikan koneksi internet Anda stabil</li>
                    </ul>
                </div>

                <div class="text-center">
                    @if($existingSession)
                        <a href="{{ route('student.exams.take', $existingSession) }}" class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                            Lanjutkan Ujian
                        </a>
                    @elseif(!isset($canStart) || $canStart)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Token Ujian</label>
                            <p class="text-xs text-slate-500 mb-2">Masukkan token yang diberikan oleh pengawas/guru untuk memulai ujian.</p>
                            <form method="POST" action="{{ route('student.exams.start', $exam) }}" id="start-exam-form" class="flex flex-col sm:flex-row gap-3">
                                @csrf
                                <input type="text" name="exam_token" id="exam-token-input" required maxlength="8" placeholder="Contoh: ABC123" oninput="this.value=this.value.toUpperCase()"
                                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm font-mono uppercase"
                                    autocomplete="off">
                                <button type="button" onclick="const f=document.getElementById('start-exam-form');if(!document.getElementById('exam-token-input').value.trim()){Swal.fire({icon:'error',title:'Token kosong',text:'Masukkan token terlebih dahulu.'});return;}confirmAction(f,'Mulai Ujian?','Timer akan berjalan segera. Ujian hanya bisa dikerjakan 1 kali.','Ya, mulai!','warning')"
                                    class="inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    Mulai Ujian
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
