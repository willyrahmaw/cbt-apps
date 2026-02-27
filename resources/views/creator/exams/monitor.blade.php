@extends('layouts.app')
@section('title', 'Monitoring Ujian')
@section('header', 'Monitoring: ' . $exam->title)
@section('header-actions')
    <div class="flex items-center gap-2">
        <span class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-mono font-medium">Token: {{ $exam->token }}</span>
        <a href="{{ route('creator.exams.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Kembali</a>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-slate-500">Live monitoring (refresh otomatis 5 detik)</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span id="stat-belum" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600"><span class="font-semibold">0</span> Belum mulai</span>
            <span id="stat-ujian" class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700"><span class="font-semibold">0</span> Sedang ujian</span>
            <span id="stat-selesai" class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700"><span class="font-semibold">0</span> Selesai</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Peserta</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Sisa Waktu</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Skor</th>
                </tr>
            </thead>
            <tbody id="monitor-tbody" class="divide-y divide-slate-50">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-2 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        Memuat data...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    window.creatorMonitorConfig = {
        dataUrl: "{{ route('creator.exams.monitor.data', $exam) }}",
    };
</script>
@vite(['resources/js/creator/exams-monitor.js'])
@endpush
@endsection
