window.toggleQbEditAnswers = function () {
    const typeSelect = document.getElementById('qtype');
    const wrap = document.getElementById('answers-wrap');
    if (!typeSelect || !wrap) return;

    const t = typeSelect.value;
    wrap.style.display = t === 'essay' ? 'none' : 'block';

    const inputs = document.querySelectorAll('[name^="answers["]');
    inputs.forEach((el, i) => {
        const row = document.getElementById('ans-row-' + i);
        if (!row) return;
        if (t === 'true_false') {
            row.style.display = i < 2 ? '' : 'none';
            if (i < 2) {
                el.value = i === 0 ? 'Benar' : 'Salah';
                el.readOnly = true;
            }
        } else {
            row.style.display = '';
            el.readOnly = false;
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.toggleQbEditAnswers();
}); 

