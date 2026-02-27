@extends('layouts.app')
@section('title', 'Dashboard Pembuat Soal')
@section('header', 'Dashboard Pembuat Soal')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $totalExams }}</p>
                <p class="text-sm text-slate-500">Total Ujian</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $activeExams }}</p>
                <p class="text-sm text-slate-500">Ujian Aktif</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $totalSessions }}</p>
                <p class="text-sm text-slate-500">Peserta Selesai</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $needsGrading }}</p>
                <p class="text-sm text-slate-500">Perlu Dinilai</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Ujian Terbaru</h3>
            <a href="{{ route('creator.exams.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-medium hover:bg-indigo-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Ujian
            </a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentExams as $exam)
                <div class="px-6 py-3 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $exam->title }}</p>
                        <p class="text-xs text-slate-400">{{ $exam->category->name ?? '-' }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $exam->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $exam->is_active ? 'Aktif' : 'Draft' }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">Belum ada ujian</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Hasil Terbaru</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentSessions as $session)
                <div class="px-6 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200' }} flex items-center justify-center font-bold text-xs">
                        {{ $session->score }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $session->user->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $session->exam->title }}</p>
                    </div>
                    <span class="text-xs text-slate-400">{{ $session->finished_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">Belum ada hasil</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
