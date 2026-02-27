@extends('layouts.app')
@section('title', 'Analytics & Pelaporan')
@section('header', 'Analytics & Pelaporan')

@section('header-actions')
    <div class="flex items-center gap-3">
        <form method="get" class="flex items-center gap-3">
            <select name="period" onchange="this.form.submit()" class="rounded-lg border-slate-200 text-sm py-2 px-3">
                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>1 Bulan</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>1 Minggu</option>
            </select>
        </form>
        <a href="{{ route('admin.analytics.export', ['period' => $period]) }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition">
            Ekspor CSV
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['totalExams']) }}</p>
                    <p class="text-sm text-slate-500">Total Ujian</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['totalStudents']) }}</p>
                    <p class="text-sm text-slate-500">Total Siswa</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['periodSessions']) }}</p>
                    <p class="text-sm text-slate-500">Ujian Selesai (periode)</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ $stats['passRate'] }}%</p>
                    <p class="text-sm text-slate-500">Tingkat Kelulusan</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm col-span-2 lg:col-span-1">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['avgScore'], 1) }}</p>
                    <p class="text-sm text-slate-500">Rata-rata Skor</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8">
            <h3 class="font-semibold text-slate-800 mb-6">Performa per Kategori</h3>
            @if($byCategory->isEmpty())
                <p class="text-sm text-slate-400 py-12 text-center">Belum ada data</p>
            @else
                <canvas id="chart-category" height="220"></canvas>
            @endif
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8">
            <h3 class="font-semibold text-slate-800 mb-6">Performa per Kelas (Top 10)</h3>
            @if($byClass->isEmpty())
                <p class="text-sm text-slate-400 py-12 text-center">Belum ada data</p>
            @else
                <canvas id="chart-class" height="220"></canvas>
            @endif
        </div>
    </div>

    {{-- Analisis Kesulitan Soal --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-8">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Analisis Kesulitan Soal</h3>
            <p class="text-xs text-slate-500 mt-1">20 soal tersulit (min. 3 jawaban, hanya pilihan ganda)</p>
        </div>
        <div class="overflow-x-auto">
            @if($questionDifficulty->isEmpty())
                <p class="text-sm text-slate-400 py-12 text-center">Belum ada data cukup</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-6 py-4 font-medium">Soal / Ujian</th>
                            <th class="text-center px-6 py-4 font-medium">Dijawab</th>
                            <th class="text-center px-6 py-4 font-medium">Benar</th>
                            <th class="text-center px-6 py-4 font-medium">% Benar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($questionDifficulty as $qd)
                            @php $q = $qd['question']; @endphp
                            @if(!$q) @continue @endif
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="text-slate-800 line-clamp-2">{{ Str::limit(strip_tags($q->question_text ?? ''), 60) }}</span>
                                    <span class="text-xs text-slate-400 block mt-1">{{ $q->exam?->title ?? '-' }} / {{ $q->exam?->category?->name ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">{{ $qd['total'] }}</td>
                                <td class="px-6 py-4 text-center">{{ $qd['correct'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        {{ $qd['pct_correct'] >= 70 ? 'bg-emerald-100 text-emerald-700' : ($qd['pct_correct'] >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $qd['pct_correct'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.adminAnalyticsConfig = {
        byCategory: @json($byCategory->values()),
        byClass: @json($byClass->values()),
    };
</script>
@vite(['resources/js/admin/analytics.js'])
@endpush
@endsection
