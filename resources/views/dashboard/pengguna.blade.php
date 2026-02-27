@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
{{-- Welcome Banner --}}
<div class="bg-indigo-600 rounded-2xl p-6 lg:p-8 mb-8 text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-1/2 w-32 h-32 bg-white/5 rounded-full translate-y-1/2"></div>
    <div class="relative">
        <h2 class="text-2xl font-bold mb-1">Halo, {{ auth()->user()->name }}!</h2>
        <p class="text-white/80">Siap untuk mengerjakan ujian hari ini?</p>
        <div class="flex gap-6 mt-4">
            <div>
                <p class="text-3xl font-bold">{{ $totalCompleted }}</p>
                <p class="text-sm text-white/70">Ujian Selesai</p>
            </div>
            <div>
                <p class="text-3xl font-bold">{{ number_format($avgScore, 0) }}</p>
                <p class="text-sm text-white/70">Rata-rata Skor</p>
            </div>
        </div>
    </div>
</div>

@if($inProgressSession)
    <div class="mb-8 p-5 rounded-2xl bg-amber-50 border border-amber-200">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-amber-800">Ujian Belum Selesai</p>
                <p class="text-sm text-amber-600">{{ $inProgressSession->exam->title }}</p>
            </div>
            <a href="{{ route('student.exams.take', $inProgressSession) }}" class="px-4 py-2 rounded-xl bg-amber-500 text-white font-medium text-sm hover:bg-amber-600 transition">
                Lanjutkan
            </a>
        </div>
    </div>
@endif

@if($upcomingExams->count())
{{-- Pengingat Ujian Akan Datang --}}
<div class="mb-6 p-5 rounded-2xl bg-indigo-50 border border-indigo-200">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-indigo-800 mb-1">Pengingat: Ada {{ $upcomingExams->count() }} ujian akan datang</h3>
            <p class="text-sm text-indigo-600 mb-4">Siapkan diri untuk ujian berikutnya. Cek jadwal dan pastikan Anda siap mengerjakan.</p>
        </div>
    </div>
</div>
{{-- Upcoming Exams --}}
<div class="mb-6">
    <h3 class="text-lg font-semibold text-slate-800 mb-4">Ujian Akan Datang</h3>
    <div class="space-y-3 mb-6">
        @foreach($upcomingExams as $exam)
            <a href="{{ route('student.exams.show', $exam) }}" class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-100 hover:border-indigo-200 hover:shadow-sm transition">
                <div>
                    <p class="font-medium text-slate-800">{{ $exam->title }}</p>
                    <p class="text-sm text-slate-500">{{ $exam->category->name ?? '-' }} · {{ $exam->questions_count }} soal</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-amber-600">{{ $exam->start_time->format('d M Y, H:i') }}</p>
                    <p class="text-xs text-slate-400">Mulai bisa dikerjakan</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Available Exams --}}
<div class="mb-6 flex items-center justify-between">
    <h3 class="text-lg font-semibold text-slate-800">Ujian Tersedia</h3>
    <a href="{{ route('student.exams.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Lihat Semua</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @forelse($availableExams as $exam)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
            <div class="h-2 bg-indigo-600"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium">{{ $exam->category->name ?? 'Umum' }}</span>
                    <span class="text-xs text-slate-400">{{ $exam->questions_count }} soal</span>
                </div>
                <h4 class="font-semibold text-slate-800 mb-1 group-hover:text-indigo-600 transition">{{ $exam->title }}</h4>
                <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ $exam->description ?: 'Tidak ada deskripsi' }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">{{ $exam->duration }} menit</span>
                    <a href="{{ route('student.exams.show', $exam) }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-medium hover:bg-indigo-100 transition">
                        Mulai
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p>Belum ada ujian tersedia</p>
        </div>
    @endforelse
</div>

{{-- Recent Results --}}
@if($completedSessions->count())
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-800">Riwayat Ujian</h3>
        <a href="{{ route('student.results.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Lihat Semua Riwayat</a>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Ujian</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Skor</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($completedSessions as $session)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3">
                                <a href="{{ route('student.results.show', $session) }}" class="text-sm font-medium text-slate-700 hover:text-indigo-600">{{ $session->exam->title }}</a>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $session->score }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $session->score >= $session->exam->passing_score ? 'Lulus' : 'Tidak Lulus' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right text-sm text-slate-400">{{ $session->finished_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
