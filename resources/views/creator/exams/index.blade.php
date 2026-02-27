@extends('layouts.app')
@section('title', 'Kelola Ujian')
@section('header', 'Kelola Ujian')
@section('header-actions')
    <a href="{{ route('creator.exams.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Ujian
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($exams as $exam)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="h-2 {{ $exam->is_active ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium">{{ $exam->category->name ?? 'Umum' }}</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $exam->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $exam->is_active ? 'Aktif' : 'Draft' }}
                    </span>
                </div>
                <h4 class="font-semibold text-slate-800 mb-1">{{ $exam->title }}</h4>
                <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ $exam->description ?: 'Tidak ada deskripsi' }}</p>

                <div class="flex items-center gap-4 text-xs text-slate-400 mb-4">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $exam->questions_count }} soal
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $exam->duration }} menit
                    </span>
                    <span>KKM: {{ $exam->passing_score }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('creator.exams.monitor', $exam) }}" class="p-2 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Monitoring ujian">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-5l1.5 2.5a1 1 0 01-.85 1.5h-5.3a1 1 0 01-.85-1.5L9 16H6a2 2 0 01-2-2V5z" />
                        </svg>
                    </a>
                    <a href="{{ route('creator.exams.preview', $exam) }}" class="p-2 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition" title="Preview tampilan siswa">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <a href="{{ route('creator.exams.questions', $exam) }}" class="flex-1 py-2 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-medium text-center hover:bg-indigo-100 transition">Soal</a>
                    <form method="POST" action="{{ route('creator.exams.duplicate', $exam) }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Duplikasi ujian">📋</button>
                    </form>
                    <a href="{{ route('creator.exams.export.questions', $exam) }}" class="p-2 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Ekspor soal">⬇</a>
                    <a href="{{ route('creator.exams.edit', $exam) }}" class="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('creator.exams.toggle', $exam) }}" id="toggle-{{ $exam->id }}">
                        @csrf
                        <button type="button" onclick="confirmAction(document.getElementById('toggle-{{ $exam->id }}'), '{{ $exam->is_active ? 'Nonaktifkan Ujian?' : 'Aktifkan Ujian?' }}', '{{ $exam->is_active ? 'Siswa tidak akan bisa mengakses ujian ini.' : 'Ujian akan aktif dan bisa diakses siswa.' }}', '{{ $exam->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan' }}', '{{ $exam->is_active ? 'warning' : 'question' }}')"
                            class="p-2 rounded-lg transition {{ $exam->is_active ? 'text-emerald-500 hover:text-red-500 hover:bg-red-50' : 'text-slate-400 hover:text-emerald-500 hover:bg-emerald-50' }}" title="{{ $exam->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                            @if($exam->is_active)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </button>
                    </form>
                    <a href="{{ route('creator.exams.results', $exam) }}" class="p-2 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition" title="Hasil">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('creator.exams.destroy', $exam) }}">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this.closest('form'), 'Ujian beserta soal dan hasilnya akan dihapus permanen.')" class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-16">
            <svg class="w-20 h-20 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-slate-400 mb-4">Belum ada ujian</p>
            <a href="{{ route('creator.exams.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-sm font-medium hover:bg-indigo-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Ujian Pertama
            </a>
        </div>
    @endforelse
</div>

@if($exams->hasPages())
    <div class="mt-6">{{ $exams->links() }}</div>
@endif
@endsection
