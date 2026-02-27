@extends('layouts.app')
@section('title', 'Edit Soal')
@section('header', 'Edit Soal')
@section('header-actions')
    <a href="{{ route('creator.question-bank.show', $questionBank) }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200">Batal</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('creator.question-bank.questions.update', [$questionBank, $bankQuestion]) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="remove_image" value="0" id="remove-image-flag">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pertanyaan</label>
                <textarea name="question_text" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 text-sm">{{ old('question_text', $bankQuestion->question_text) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tag (pisah koma)</label>
                <input type="text" name="tags" value="{{ old('tags', is_array($bankQuestion->tags) ? implode(', ', $bankQuestion->tags) : '') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Gambar</label>
                @if($bankQuestion->question_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $bankQuestion->question_image) }}" alt="" class="max-h-24 rounded border">
                        <label class="ml-2"><input type="checkbox" name="remove_image" value="1" onchange="document.getElementById('remove-image-flag').value=this.checked?1:0"> Hapus gambar</label>
                    </div>
                @endif
                <input type="file" name="question_image" accept="image/*" class="text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                    <select name="question_type" id="qtype" onchange="toggleAnswers()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        <option value="multiple_choice" {{ $bankQuestion->question_type === 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                        <option value="true_false" {{ $bankQuestion->question_type === 'true_false' ? 'selected' : '' }}>Benar/Salah</option>
                        <option value="essay" {{ $bankQuestion->question_type === 'essay' ? 'selected' : '' }}>Essai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Poin</label>
                    <input type="number" name="points" value="{{ old('points', $bankQuestion->points) }}" min="1" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>
            </div>
            @php
                $ans = $bankQuestion->answers->values()->all();
                $correctIdx = collect($bankQuestion->answers)->search(fn($a) => $a->is_correct);
                if ($correctIdx === false) $correctIdx = 0;
                $isTf = $bankQuestion->question_type === 'true_false';
            @endphp
            <div id="answers-wrap" style="{{ $bankQuestion->question_type === 'essay' ? 'display:none' : '' }}">
                <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban</label>
                <div class="space-y-2">
                    @foreach(['A','B','C','D','E'] as $i => $l)
                        <div class="flex items-center gap-2" id="ans-row-{{ $i }}">
                            <input type="radio" name="correct_answer" value="{{ $i }}" {{ $correctIdx === $i ? 'checked' : '' }} class="w-4 h-4 text-indigo-600">
                            <input type="text" name="answers[{{ $i }}][text]" value="{{ ($ans[$i] ?? null)?->answer_text ?? ($isTf && $i === 0 ? 'Benar' : ($isTf && $i === 1 ? 'Salah' : '')) }}" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-sm" placeholder="Opsi {{ $l }}" {{ $isTf && $i < 2 ? 'readonly' : '' }}>
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Simpan Perubahan</button>
        </form>
    </div>
</div>
@push('scripts')
@vite(['resources/js/creator/qb-question-edit.js'])
@endpush
@endsection
