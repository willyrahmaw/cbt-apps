@extends('layouts.app')
@section('title', 'Edit Bank Soal')
@section('header', 'Edit: ' . $questionBank->name)
@section('header-actions')
    <a href="{{ route('creator.question-bank.show', $questionBank) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">Kembali</a>
@endsection

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('creator.question-bank.update', $questionBank) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Bank</label>
                <input type="text" name="name" value="{{ old('name', $questionBank->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                <select name="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none text-sm">
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $questionBank->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none text-sm resize-none">{{ old('description', $questionBank->description) }}</textarea>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Simpan</button>
        </form>
    </div>
</div>
@endsection
