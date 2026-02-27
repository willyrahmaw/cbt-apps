(() => {
    const form = document.getElementById('add-to-exam-form');
    const btn = document.getElementById('add-selected-btn');
    if (!form || !btn) return;

    const checkboxes = form.querySelectorAll('input[name="question_ids[]"]');

    function updateBtn() {
        const n = form.querySelectorAll('input[name="question_ids[]"]:checked').length;
        btn.disabled = n === 0;
        btn.textContent = n ? `Tambah yang dipilih (${n})` : 'Tambah yang dipilih';
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateBtn));
    updateBtn();

    form.addEventListener('submit', e => {
        if (form.querySelectorAll('input[name="question_ids[]"]:checked').length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 soal.');
        }
    });
})(); 

