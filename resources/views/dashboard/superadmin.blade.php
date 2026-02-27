@extends('layouts.app')
@section('title', 'Dashboard Superadmin')
@section('header', 'Dashboard Superadmin')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $totalUsers }}</p>
                <p class="text-sm text-slate-500">Total User</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $totalExams }}</p>
                <p class="text-sm text-slate-500">Total Ujian</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $totalCategories }}</p>
                <p class="text-sm text-slate-500">Kategori</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">{{ $totalSessions }}</p>
                <p class="text-sm text-slate-500">Ujian Selesai</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent Users --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">User Terbaru</h3>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Lihat Semua</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentUsers as $user)
                <div class="px-6 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400">{{ $user->email }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $user->role === 'superadmin' ? 'bg-red-100 text-red-700' : ($user->role === 'pembuat_soal' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-slate-400">Belum ada user</div>
            @endforelse
        </div>
    </div>

    {{-- Recent Results --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Hasil Ujian Terbaru</h3>
            <a href="{{ route('admin.results.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Lihat Semua</a>
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
                <div class="px-6 py-8 text-center text-sm text-slate-400">Belum ada hasil ujian</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
