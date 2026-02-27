window.toggleQbCreateAnswers = function () {
    const typeSelect = document.getElementById('qtype');
    const wrap = document.getElementById('answers-wrap');
    if (!typeSelect || !wrap) return;

    const t = typeSelect.value;
    wrap.style.display = t === 'essay' ? 'none' : 'block';

    const inputs = document.querySelectorAll('.ans-input');
    inputs.forEach((el, i) => {
        const row = el.closest('.ans-row');
        if (!row) return;
        if (t === 'true_false') {
            if (i < 2) {
                el.value = i === 0 ? 'Benar' : 'Salah';
                el.readOnly = true;
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        } else if (t === 'multiple_choice') {
            el.readOnly = false;
            row.style.display = '';
        } else {
            // essay: handled above by wrap.display, rows stay but hidden
            el.readOnly = false;
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.toggleQbCreateAnswers();
}); 

