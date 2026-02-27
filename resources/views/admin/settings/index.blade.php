@extends('layouts.app')
@section('title', 'Pengaturan')
@section('header', 'Pengaturan')

@section('content')
<div class="max-w-2xl space-y-6">
    {{-- Tahun Ajaran --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-semibold text-slate-800 mb-4">Tahun Ajaran Aktif</h3>
        <p class="text-sm text-slate-500 mb-4">Tahun ajaran ini digunakan sebagai default saat membuat kelas baru. Diperbarui otomatis saat proses kenaikan kelas.</p>
        <form method="POST" action="{{ route('admin.settings.academic-year') }}" class="flex items-end gap-3">
            @csrf @method('PUT')
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Ajaran</label>
                <input type="text" name="academic_year" value="{{ old('academic_year', $academicYear) }}" required
                    pattern="\d{4}/\d{4}"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                    placeholder="2025/2026">
                @error('academic_year')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Simpan</button>
        </form>
    </div>
</div>
@endsection
