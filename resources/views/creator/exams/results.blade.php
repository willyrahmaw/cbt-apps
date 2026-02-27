@extends('layouts.app')
@section('title', 'Hasil Ujian')
@section('header', 'Hasil: ' . $exam->title)
@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('creator.exams.export.excel', array_merge([$exam], request()->only(['class_id', 'search']))) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Excel
        </a>
        <a href="{{ route('creator.exams.export.pdf', array_merge([$exam], request()->only(['class_id', 'search']))) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            PDF
        </a>
        <a href="{{ route('creator.exams.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peserta atau email..."
                class="flex-1 min-w-[200px] px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
            <select name="class_id" class="px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                <option value="">Semua Kelas</option>
                @foreach($exam->schoolClasses as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
            @if(request()->hasAny(['search', 'class_id']))
                <a href="{{ route('creator.exams.results', $exam) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Reset</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Peserta</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Benar</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Waktu Selesai</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($sessions as $session)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $sessions->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                @if($session->user->avatar)
                                    <img src="{{ asset('storage/' . $session->user->avatar) }}" alt="Avatar {{ $session->user->name }}"
                                         class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold">
                                        {{ strtoupper(substr($session->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-slate-700">{{ $session->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $session->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            @if($session->user->schoolClass)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-600">{{ $session->user->schoolClass->name }}</span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold {{ $session->needs_grading ? 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-100' : ($session->score >= $exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200') }}">
                                {{ $session->score }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm text-slate-600">{{ $session->correct_answers }}/{{ $session->total_questions }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($session->status === 'terminated')
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-200">Dihentikan</span>
                            @elseif($session->needs_grading)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-100">Perlu Dinilai</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $session->score >= $exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200' }}">
                                    {{ $session->score >= $exam->passing_score ? 'Lulus' : 'Tidak Lulus' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-slate-400">{{ $session->finished_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($session->needs_grading)
                                <a href="{{ route('creator.grading.show', [$exam, $session]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-medium hover:bg-amber-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Nilai Essai
                                </a>
                            @else
                                <a href="{{ route('creator.grading.show', [$exam, $session]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">Belum ada peserta yang menyelesaikan ujian ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
