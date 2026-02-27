@extends('layouts.app')
@section('title', 'Edit Ujian')
@section('header', 'Edit Ujian')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('creator.exams.update', $exam) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul Ujian</label>
                <input type="text" name="title" value="{{ old('title', $exam->title) }}" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm resize-none">{{ old('description', $exam->description) }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <select name="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $exam->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Durasi (menit)</label>
                    <input type="number" name="duration" value="{{ old('duration', $exam->duration) }}" min="1" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Waktu Mulai <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time', $exam->start_time?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    <p class="text-xs text-slate-500 mt-0.5">Ujian baru bisa dimulai dari jam ini</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Waktu Akhir <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time', $exam->end_time?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    <p class="text-xs text-slate-500 mt-0.5">Ujian otomatis berakhir paling lambat jam ini</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nilai KKM (0-100)</label>
                <input type="number" name="passing_score" value="{{ old('passing_score', $exam->passing_score) }}" min="0" max="100" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
            </div>
            @php $selectedClassIds = old('class_ids', $exam->schoolClasses->pluck('id')->toArray()); @endphp
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas Tujuan <span class="text-slate-400 font-normal">(kosongkan = semua kelas)</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 border border-slate-200 rounded-xl max-h-40 overflow-y-auto">
                    @forelse($classes as $cls)
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}"
                                {{ in_array($cls->id, $selectedClassIds) ? 'checked' : '' }}
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
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $exam->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Aktifkan ujian</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions', $exam->shuffle_questions) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Acak urutan soal per siswa</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="shuffle_answers" value="1" {{ old('shuffle_answers', $exam->shuffle_answers) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Acak urutan opsi jawaban (A,B,C,D) per siswa</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_result" value="1" {{ old('show_result', $exam->show_result) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700">Tampilkan hasil setelah ujian selesai</span>
                </label>
            </div>
            @php $terminateEvents = old('terminate_on_events', $exam->terminate_on_events ?? []); @endphp
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Akhiri ujian & regenerasi token jika pelanggaran:</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Jika dicentang, ujian akan dihentikan dan siswa harus minta token baru untuk mengulang (waktu mengulang berlanjut).</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-700 dark:text-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="tab_switch" {{ in_array('tab_switch', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Pindah tab / keluar halaman</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="right_click" {{ in_array('right_click', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Klik kanan</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="copy_attempt" {{ in_array('copy_attempt', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Percobaan copy/cut</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="paste_attempt" {{ in_array('paste_attempt', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Percobaan paste</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="rate_limit" {{ in_array('rate_limit', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Submit terlalu cepat (rate limit)</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="time_up_attempt" {{ in_array('time_up_attempt', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Submit setelah waktu habis</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="split_screen" {{ in_array('split_screen', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Split screen / multi-window (mobile)</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="window_blur" {{ in_array('window_blur', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Jendela lain menimpa / kehilangan fokus</span></label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="terminate_on_events[]" value="fullscreen_exit" {{ in_array('fullscreen_exit', (array)$terminateEvents) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"><span>Keluar dari mode fullscreen</span></label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
                    Update Ujian
                </button>
                <a href="{{ route('creator.exams.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition">Batal</a>
            </div>
        </form>

        <div class="mt-6 p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-slate-600">
            <label class="block text-sm font-medium text-slate-700 mb-2">Token Ujian</label>
            <p class="text-xs text-slate-500 mb-2">Berikan token ini ke peserta sebelum ujian dimulai.</p>
            <form method="POST" action="{{ route('creator.exams.regenerate-token', $exam) }}" id="regenerate-token-form" class="flex flex-wrap items-center gap-2">
                @csrf
                <code id="exam-token" class="flex-1 min-w-[120px] px-4 py-2.5 rounded-lg bg-white dark:bg-slate-700 border border-indigo-200 dark:border-slate-600 font-mono text-lg font-bold text-indigo-700 dark:text-indigo-300">{{ $exam->token }}</code>
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('exam-token').textContent); Swal.fire({icon:'success',title:'Disalin',text:'Token berhasil disalin.'})" class="px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Salin</button>
                <button type="button" onclick="confirmAction(document.getElementById('regenerate-token-form'), 'Regenerasi Token?', 'Token lama tidak akan berlaku. Peserta harus menggunakan token baru.', 'Ya, regenerasi', 'warning')" class="px-4 py-2.5 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition">Regenerasi</button>
            </form>
        </div>
    </div>
</div>
@endsection
