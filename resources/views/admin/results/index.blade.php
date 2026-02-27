@extends('layouts.app')
@section('title', 'Semua Hasil Ujian')
@section('header', 'Semua Hasil Ujian')

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peserta atau ujian..."
                class="flex-1 px-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Cari</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Peserta</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Ujian</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Benar</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
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
                                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs">
                                        {{ strtoupper(substr($session->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-slate-700">{{ $session->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-600">{{ $session->exam->title }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200' }}">
                                {{ $session->score }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm text-slate-600">{{ $session->correct_answers }}/{{ $session->total_questions }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200' }}">
                                {{ $session->score >= $session->exam->passing_score ? 'Lulus' : 'Tidak Lulus' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-slate-400">{{ $session->finished_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400">Belum ada hasil ujian</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
