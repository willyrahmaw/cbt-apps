@extends('layouts.app')
@section('title', 'Bank Soal')
@section('header', 'Bank Soal')
@section('header-actions')
    <a href="{{ route('creator.question-bank.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Bank Soal
    </a>
@endsection

@section('content')
<form method="get" class="mb-6 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bank..." class="px-4 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none text-sm w-48">
    <select name="category_id" class="px-4 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 text-sm">
        <option value="">Semua Kategori</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200">Filter</button>
    @if(request()->hasAny(['search','category_id']))
        <a href="{{ route('creator.question-bank.index') }}" class="px-4 py-2 rounded-lg text-slate-500 text-sm hover:bg-slate-100">Reset</a>
    @endif
</form>

@php $examIdParam = request('exam_id') ? '?exam_id=' . request('exam_id') : ''; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($banks as $bank)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="h-1.5 bg-indigo-500"></div>
            <div class="p-5">
                <span class="px-2 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-medium">{{ $bank->category->name ?? '-' }}</span>
                <h4 class="font-semibold text-slate-800 mt-2 mb-1">{{ $bank->name }}</h4>
                <p class="text-sm text-slate-500 line-clamp-2 mb-4">{{ $bank->description ?: 'Tidak ada deskripsi' }}</p>
                <p class="text-xs text-slate-400 mb-4">{{ $bank->questions_count }} soal</p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('creator.question-bank.show', $bank) }}{{ $examIdParam }}" class="flex-1 py-2 rounded-lg bg-indigo-50 text-indigo-600 text-sm font-medium text-center hover:bg-indigo-100">Kelola</a>
                    <a href="{{ route('creator.question-bank.edit', $bank) }}" class="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50" title="Edit">✎</a>
                    <form method="POST" action="{{ route('creator.question-bank.destroy', $bank) }}">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this.closest('form'), 'Bank soal dan semua soalnya akan dihapus.')" class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50">🗑</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-16">
            <svg class="w-20 h-20 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
            <p class="text-slate-400 mb-4">Belum ada bank soal</p>
            <a href="{{ route('creator.question-bank.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-sm font-medium hover:bg-indigo-100">Buat Bank Soal Pertama</a>
        </div>
    @endforelse
</div>

@if($banks->hasPages())
    <div class="mt-6">{{ $banks->links() }}</div>
@endif
@endsection
