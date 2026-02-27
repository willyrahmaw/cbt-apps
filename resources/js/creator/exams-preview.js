window.goToPreviewQuestion = function (idx) {
    const panels = document.querySelectorAll('.question-panel');
    const buttons = document.querySelectorAll('.question-nav-btn');

    panels.forEach((p, i) => {
        p.classList.toggle('hidden', i !== idx);
    });

    buttons.forEach((b, i) => {
        const isActive = i === idx;
        b.classList.toggle('bg-indigo-100', isActive);
        b.classList.toggle('border-indigo-300', isActive);
        b.classList.toggle('text-indigo-600', isActive);
        b.classList.toggle('bg-slate-100', !isActive);
        b.classList.toggle('border-slate-200', !isActive);
        b.classList.toggle('text-slate-500', !isActive);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    // Render math if helper from layout is available
    if (typeof window.renderMathInPage === 'function') {
        window.renderMathInPage();
    } else if (typeof window.renderMathInElement !== 'undefined') {
        document.querySelectorAll('.math-content').forEach(el => {
            if (!el.dataset.mathRendered) {
                try {
                    window.renderMathInElement(el, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false },
                        ],
                        throwOnError: false,
                    });
                    el.dataset.mathRendered = '1';
                } catch (e) {}
            }
        });
    }
});

