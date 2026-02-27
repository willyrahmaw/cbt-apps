@extends('layouts.app')
@section('title', 'Backup & Restore')
@section('header', 'Backup & Restore')
@section('header-actions')
    <p class="text-sm text-slate-500">Cadangan dan pemulihan data ujian</p>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Backup Database --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-2">Backup Database</h3>
        <p class="text-sm text-slate-500 mb-4">Unduh dump penuh database (SQL/SQLite). Ideal untuk backup server atau migrasi. Berisi semua tabel: user, ujian, soal, hasil, dll.</p>
        <a href="{{ route('admin.backup.download-database') }}" class="block w-full py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            Unduh Backup Database
        </a>
    </div>

    {{-- Backup JSON --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-2">Backup Data (JSON)</h3>
        <p class="text-sm text-slate-500 mb-4">Unduh salinan ujian, soal, dan bank soal ke file JSON. Dapat di-restore lewat form di bawah.</p>
        <form method="GET" action="{{ route('admin.backup.download') }}" class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cakupan</label>
                <select name="scope" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                    <option value="all">Semua (ujian + bank soal)</option>
                    <option value="exams_only">Ujian saja</option>
                </select>
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Backup
            </button>
        </form>
    </div>

    {{-- Restore --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
        <h3 class="font-semibold text-slate-800 mb-2">Restore dari Backup</h3>
        <p class="text-sm text-slate-500 mb-4">Upload file JSON hasil backup untuk memulihkan ujian dan bank soal. Data yang di-restore akan ditambahkan (tidak menggantikan data lama).</p>
        <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">File backup (.json)</label>
                <input type="file" name="file" accept=".json" required
                    class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4V4"/></svg>
                Restore dari File
            </button>
        </form>
    </div>
</div>
@endsection
