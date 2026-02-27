@extends('layouts.app')
@section('title', 'Kelola Kelas')
@section('header', 'Kelola Kelas')
@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.classes.template') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Template
        </a>
        <form method="POST" action="{{ route('admin.classes.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <label class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Impor
                <input type="file" name="file" accept=".xlsx,.xls" class="hidden" onchange="if(this.files.length) this.form.submit()">
            </label>
        </form>
        <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kelas
        </a>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($classes as $class)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('admin.classes.edit', $class) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('admin.classes.destroy', $class) }}">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this.closest('form'), 'Siswa di kelas ini akan menjadi tanpa kelas.')" class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <a href="{{ route('admin.classes.show', $class) }}" class="block">
                <h3 class="font-semibold text-slate-800 mb-1">{{ $class->name }}</h3>
                <div class="flex flex-wrap gap-2 mb-2">
                    @if($class->grade_level)
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-600">{{ $class->grade_level }}</span>
                    @endif
                    @if($class->academic_year)
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500">{{ $class->academic_year }}</span>
                    @endif
                </div>
                @if($class->description)
                    <p class="text-xs text-slate-400 mb-3 line-clamp-2">{{ $class->description }}</p>
                @endif
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                    <span class="font-medium">{{ $class->students_count }}</span> siswa
                </div>
            </a>
        </div>
    @empty
        <div class="sm:col-span-2 lg:col-span-3 text-center py-16 bg-white rounded-2xl border border-slate-100">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-slate-400">Belum ada kelas. Klik tombol "Tambah Kelas" untuk mulai.</p>
        </div>
    @endforelse
</div>

@if($classes->hasPages())
    <div class="mt-6">{{ $classes->links() }}</div>
@endif
@endsection
