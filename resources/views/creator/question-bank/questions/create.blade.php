@extends('layouts.app')
@section('title', 'Tambah Soal ke Bank')
@section('header', 'Tambah Soal: ' . $questionBank->name)
@section('header-actions')
    <a href="{{ route('creator.question-bank.show', $questionBank) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">Batal</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('creator.question-bank.questions.store', $questionBank) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pertanyaan</label>
                <textarea name="question_text" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none text-sm" placeholder="Tulis pertanyaan..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tag <span class="text-slate-400">(pisah koma, opsional)</span></label>
                <input type="text" name="tags" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 text-sm" placeholder="matematika, aljabar, kelas-x">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar</label>
                <input type="file" name="question_image" accept="image/*" class="text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                    <select name="question_type" id="qtype" onchange="toggleAnswers()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        <option value="multiple_choice">Pilihan Ganda</option>
                        <option value="true_false">Benar/Salah</option>
                        <option value="essay">Essai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Poin</label>
                    <input type="number" name="points" value="1" min="1" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>
            </div>
            <div id="answers-wrap">
                <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban</label>
                <div id="answer-fields" class="space-y-2">
                    @foreach(['A','B','C','D'] as $i => $l)
                        <div class="flex items-center gap-2 ans-row">
                            <input type="radio" name="correct_answer" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                            <input type="text" name="answers[{{ $i }}][text]" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-sm ans-input" placeholder="Opsi {{ $l }}">
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Simpan Soal</button>
        </form>
    </div>
</div>
@push('scripts')
@vite(['resources/js/creator/qb-question-create.js'])
@endpush
@endsection
