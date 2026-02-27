@extends('layouts.app')
@section('title', 'Daftar Ujian')
@section('header', 'Daftar Ujian')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($exams as $exam)
        <a href="{{ route('student.exams.show', $exam) }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group block">
            <div class="h-2 bg-indigo-600"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium">{{ $exam->category->name ?? 'Umum' }}</span>
                    <span class="text-xs text-slate-400">{{ $exam->questions_count }} soal</span>
                </div>
                <h4 class="font-semibold text-slate-800 mb-1 group-hover:text-indigo-600 transition">{{ $exam->title }}</h4>
                <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ $exam->description ?: 'Tidak ada deskripsi' }}</p>
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $exam->duration }} menit
                        </span>
                        <span>KKM: {{ $exam->passing_score }}</span>
                    </div>
                    <span class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-medium group-hover:bg-indigo-100 transition">Mulai</span>
                </div>
            </div>
        </a>
    @empty
        <div class="col-span-full text-center py-16">
            <svg class="w-20 h-20 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-slate-400">Belum ada ujian yang tersedia saat ini</p>
        </div>
    @endforelse
</div>

@if($exams->hasPages())
    <div class="mt-6">{{ $exams->links() }}</div>
@endif
@endsection
