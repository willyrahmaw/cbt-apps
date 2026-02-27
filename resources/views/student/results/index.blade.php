@extends('layouts.app')
@section('title', 'Hasil Ujian')
@section('header', 'Riwayat Ujian Saya')

@section('content')
@if(($totalCompleted ?? 0) > 0)
<div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-sm text-slate-500 mb-1">Total Ujian Selesai</p>
        <p class="text-2xl font-bold text-slate-800">{{ $totalCompleted ?? $sessions->total() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-sm text-slate-500 mb-1">Rata-rata Skor</p>
        <p class="text-2xl font-bold text-indigo-600">{{ number_format($categoryStats->avg('avg_score') ?? 0, 1) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <p class="text-sm text-slate-500 mb-1">Per Kategori</p>
        <p class="text-lg font-semibold text-slate-800">{{ $categoryStats->count() }} mata pelajaran</p>
    </div>
</div>

@if($categoryStats->isNotEmpty())
<div class="mb-6 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h3 class="font-semibold text-slate-800 mb-4">Statistik per Mata Pelajaran</h3>
    <div class="space-y-4">
        @foreach($categoryStats as $stat)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-slate-700">{{ $stat['name'] }}</span>
                    <span class="text-sm text-slate-500">{{ $stat['count'] }} ujian · rata-rata {{ $stat['avg_score'] }} · lulus {{ $stat['passed'] }}/{{ $stat['count'] }}</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $stat['avg_score']) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endif

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Ujian</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kategori</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Benar</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($sessions as $session)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $sessions->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-slate-700">{{ $session->exam->title }}</td>
                        <td class="px-6 py-3 text-sm text-slate-500">{{ $session->exam->category->name ?? '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200' }}">
                                {{ $session->score }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm text-slate-600">{{ $session->correct_answers }}/{{ $session->total_questions }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($session->status === 'terminated')
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Dihentikan</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $session->score >= $session->exam->passing_score ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-200' : 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-200' }}">
                                    {{ $session->score >= $session->exam->passing_score ? 'Lulus' : 'Tidak Lulus' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-slate-400">{{ $session->finished_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-3 text-center">
                            <a href="{{ route('student.results.show', $session) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">Belum ada hasil ujian</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
