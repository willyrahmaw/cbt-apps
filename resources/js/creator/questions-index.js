(() => {
    const cfg = window.creatorQuestionsConfig || {};
    const storeUrl = cfg.storeUrl;
    const updateUrlBase = cfg.updateUrlBase;
    const storageUrl = cfg.storageUrl || '';
    const questionsData = cfg.questions || {};

    if (!storeUrl || !updateUrlBase) return;

    let editingId = null;

    function $(id) { return document.getElementById(id); }

    function insertLatexAtCursor(latex) {
        const el = document.activeElement;
        if (!el || (el.tagName !== 'TEXTAREA' && el.tagName !== 'INPUT') || !el.closest('#form-card')) return;
        const start = el.selectionStart;
        const end = el.selectionEnd ?? start;
        const value = el.value || '';
        const text = value.substring(0, start) + '$' + latex + '$' + value.substring(end);
        el.value = text;
        const pos = start + 1 + latex.length + 1;
        el.selectionStart = el.selectionEnd = pos;
        el.focus();
    }

    function toggleLatexHelp() {
        const el = $('latex-help');
        if (el) el.classList.toggle('hidden');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    function updateAnswerFields() {
        const type = $('question-type')?.value;
        const container = $('answer-fields');
        const hint = $('answer-hint');
        if (!container || !hint || !type) return;

        const labels = ['A', 'B', 'C', 'D', 'E'];

        if (type === 'essay') {
            container.innerHTML = '';
            hint.textContent = 'Siswa akan menjawab dengan teks uraian. Penilaian dilakukan manual oleh guru.';
        } else if (type === 'true_false') {
            hint.textContent = 'Pilih radio button untuk jawaban yang benar';
            container.innerHTML = `
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_answer" value="0" checked class="w-4 h-4 text-indigo-600 border-slate-300">
                    <input type="text" name="answers[0][text]" value="Benar" required readonly class="flex-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_answer" value="1" class="w-4 h-4 text-indigo-600 border-slate-300">
                    <input type="text" name="answers[1][text]" value="Salah" required readonly class="flex-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm">
                </div>`;
        } else {
            hint.textContent = 'Pilih radio button untuk jawaban yang benar';
            container.innerHTML = '';
            for (let i = 0; i < 4; i++) {
                container.innerHTML += `
                    <div class="flex items-center gap-2">
                        <input type="radio" name="correct_answer" value="${i}" ${i === 0 ? 'checked' : ''} class="w-4 h-4 text-indigo-600 border-slate-300">
                        <input type="text" name="answers[${i}][text]" required placeholder="Jawaban ${labels[i]}" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                    </div>`;
            }
        }
    }

    function fillMultipleChoiceAnswers(answers) {
        const container = $('answer-fields');
        if (!container) return;
        const labels = ['A', 'B', 'C', 'D', 'E'];
        container.innerHTML = '';
        const count = Math.max(answers.length, 4);
        for (let i = 0; i < count; i++) {
            const ans = answers[i];
            const text = ans ? ans.text : '';
            const isCorrect = !!(ans && ans.is_correct);
            container.innerHTML += `
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_answer" value="${i}" ${isCorrect ? 'checked' : ''} class="w-4 h-4 text-indigo-600 border-slate-300">
                    <input type="text" name="answers[${i}][text]" value="${escapeHtml(text)}" required placeholder="Jawaban ${labels[i]}" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-sm">
                </div>`;
        }
    }

    function fillTrueFalseAnswers(answers) {
        const container = $('answer-fields');
        if (!container) return;
        let correctIdx = 0;
        (answers || []).forEach((a, i) => { if (a.is_correct) correctIdx = i; });
        container.innerHTML = `
            <div class="flex items-center gap-2">
                <input type="radio" name="correct_answer" value="0" ${correctIdx === 0 ? 'checked' : ''} class="w-4 h-4 text-indigo-600 border-slate-300">
                <input type="text" name="answers[0][text]" value="Benar" required readonly class="flex-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="radio" name="correct_answer" value="1" ${correctIdx === 1 ? 'checked' : ''} class="w-4 h-4 text-indigo-600 border-slate-300">
                <input type="text" name="answers[1][text]" value="Salah" required readonly class="flex-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm">
            </div>`;
    }

    function previewImage(input) {
        const placeholder = $('image-placeholder');
        const previewContainer = $('image-preview-container');
        const preview = $('image-preview');
        const filename = $('image-filename');
        const removeBtn = $('remove-image-btn');
        const flag = $('remove-image-flag');
        if (!placeholder || !previewContainer || !preview || !filename || !removeBtn || !flag) return;

        flag.value = '0';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                filename.textContent = input.files[0].name;
                placeholder.classList.add('hidden');
                previewContainer.classList.remove('hidden');
                removeBtn.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImagePreview() {
        const placeholder = $('image-placeholder');
        const previewContainer = $('image-preview-container');
        const removeBtn = $('remove-image-btn');
        const fileInput = document.querySelector('input[name="question_image"]');
        const flag = $('remove-image-flag');
        if (!placeholder || !previewContainer || !removeBtn || !fileInput || !flag) return;

        fileInput.value = '';
        placeholder.classList.remove('hidden');
        previewContainer.classList.add('hidden');
        removeBtn.classList.add('hidden');
        flag.value = editingId ? '1' : '0';
    }

    function applyHighlightToCard(id) {
        document.querySelectorAll('.question-card').forEach(c => c.classList.remove('ring-2', 'ring-indigo-400'));
        const card = document.getElementById('question-card-' + id);
        if (card) card.classList.add('ring-2', 'ring-indigo-400');
    }

    function switchFormToEdit(id, q) {
        const form = $('question-form');
        if (!form) return;
        editingId = id;
        form.action = `${updateUrlBase}/${id}`;
        $('form-method').value = 'PUT';
        $('form-title').textContent = 'Edit Soal';

        const submitBtn = $('submit-btn');
        if (submitBtn) {
            submitBtn.textContent = 'Simpan Perubahan';
            submitBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            submitBtn.classList.add('bg-amber-500', 'hover:bg-amber-600');
        }

        $('cancel-edit-btn')?.classList.remove('hidden');

        const card = $('form-card');
        if (card) {
            card.classList.remove('border-slate-100');
            card.classList.add('border-amber-300', 'ring-2', 'ring-amber-200');
        }

        $('field-question-text').value = q.question_text || '';
        $('field-points').value = q.points ?? 1;
        $('question-type').value = q.question_type || 'multiple_choice';
        $('remove-image-flag').value = '0';

        const placeholder = $('image-placeholder');
        const previewContainer = $('image-preview-container');
        const preview = $('image-preview');
        const filename = $('image-filename');
        const removeBtn = $('remove-image-btn');
        const fileInput = document.querySelector('input[name="question_image"]');

        if (fileInput) fileInput.value = '';

        if (q.question_image && preview && filename && placeholder && previewContainer && removeBtn) {
            if (q.question_image.startsWith('http://') || q.question_image.startsWith('https://')) {
                preview.src = q.question_image;
            } else {
                preview.src = `${storageUrl}/${q.question_image}`;
            }
            filename.textContent = 'Gambar saat ini';
            placeholder.classList.add('hidden');
            previewContainer.classList.remove('hidden');
            removeBtn.classList.remove('hidden');
        } else if (placeholder && previewContainer && removeBtn) {
            placeholder.classList.remove('hidden');
            previewContainer.classList.add('hidden');
            removeBtn.classList.add('hidden');
        }

        updateAnswerFields();
        if (q.question_type === 'multiple_choice') fillMultipleChoiceAnswers(q.answers || []);
        else if (q.question_type === 'true_false') fillTrueFalseAnswers(q.answers || []);

        $('form-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function cancelEdit() {
        const form = $('question-form');
        if (!form) return;
        editingId = null;

        document.querySelectorAll('.question-card').forEach(c => c.classList.remove('ring-2', 'ring-indigo-400'));

        form.action = storeUrl;
        $('form-method').value = 'POST';
        $('form-title').textContent = 'Tambah Soal';
        const submitBtn = $('submit-btn');
        if (submitBtn) {
            submitBtn.textContent = 'Tambah Soal';
            submitBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            submitBtn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
        }
        $('cancel-edit-btn')?.classList.add('hidden');
        const card = $('form-card');
        if (card) {
            card.classList.add('border-slate-100');
            card.classList.remove('border-amber-300', 'ring-2', 'ring-amber-200');
        }

        $('field-question-text').value = '';
        $('field-points').value = '1';
        $('question-type').value = 'multiple_choice';
        $('remove-image-flag').value = '0';

        removeImagePreview();
        updateAnswerFields();
    }

    window.editQuestion = function (id) {
        const q = questionsData[id];
        if (!q) return;
        applyHighlightToCard(id);
        switchFormToEdit(id, q);
    };

    window.updateAnswerFields = updateAnswerFields;
    window.previewImage = previewImage;
    window.removeImagePreview = removeImagePreview;
    window.cancelEdit = cancelEdit;
    window.insertLatexAtCursor = insertLatexAtCursor;
    window.toggleLatexHelp = toggleLatexHelp;

    document.addEventListener('DOMContentLoaded', () => {
        updateAnswerFields();
    });
})(); 

