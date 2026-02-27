@extends('layouts.app')
@section('title', 'Buat Ujian')
@section('header', 'Buat Ujian Baru')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('creator.exams.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul Ujian</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm"
                    placeholder="Contoh: Ujian Tengah Semester Matematika">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm resize-none"
                    placeholder="Deskripsi ujian (opsional)">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <select name="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Durasi (menit)</label>
                    <input type="number" name="duration" value="{{ old('duration', 60) }}" min="1" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Waktu Mulai <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    <p class="text-xs text-slate-500 mt-0.5">Ujian baru bisa dimulai dari jam ini</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Waktu Akhir <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    <p class="text-xs text-slate-500 mt-0.5">Ujian otomatis berakhir paling lambat jam ini</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nilai KKM (0-100)</label>
                <input type="number" name="passing_score" value="{{ old('passing_score', 60) }}" min="0" max="100" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas Tujuan <span class="text-slate-400 font-normal">(kosongkan = semua kelas)</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 border border-slate-200 rounded-xl max-h-40 overflow-y-auto">
                    @forelse($classes as $cls)
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}"
                                {{ in_array($cls->id, old('class_ids', [])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-slate-700">{{ $cls->name }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 col-span-3">Belum ada kelas. Buat dulu di menu Admin.</p>
                    @endforelse
                </div>
            </div>
            <div class="space-y-3 pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Acak urutan soal per siswa</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="shuffle_answers" value="1" {{ old('shuffle_answers', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Acak urutan opsi jawaban (A,B,C,D) per siswa</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_result" value="1" checked
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Tampilkan hasil setelah ujian selesai</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Buat Ujian
                </button>
                <a href="{{ route('creator.exams.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
