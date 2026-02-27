/* Layout: sidebar, dark mode, dialogs, image preview */

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('-translate-x-full');
    if (overlay) overlay.classList.toggle('hidden');
}

function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

window.toggleSidebar = toggleSidebar;
window.toggleDarkMode = toggleDarkMode;

const Toast = window.Swal?.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = window.Swal.stopTimer;
        toast.onmouseleave = window.Swal.resumeTimer;
    }
});

window.Toast = Toast;

function confirmDelete(form, text = 'Data yang dihapus tidak bisa dikembalikan.') {
    window.Swal.fire({
        title: 'Yakin hapus?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
}

function confirmAction(form, title, text, confirmText = 'Ya, lanjutkan', icon = 'question') {
    window.Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#64748b',
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) form.submit();
    });
}

window.confirmDelete = confirmDelete;
window.confirmAction = confirmAction;

/* Image preview modal */
let previewZoom = 1;
let previewNaturalW = 0;
let previewNaturalH = 0;
let isPanning = false;
let startX = 0;
let startY = 0;
let startScrollLeft = 0;
let startScrollTop = 0;

function openImagePreview(src) {
    const modal = document.getElementById('image-preview-modal');
    const img = document.getElementById('preview-img');
    const loading = document.getElementById('preview-loading');
    if (!modal || !img) return;
    if (loading) loading.classList.remove('hidden');
    img.onload = function () {
        previewNaturalW = img.naturalWidth;
        previewNaturalH = img.naturalHeight;
        updatePreviewTransform();
        if (loading) loading.classList.add('hidden');
    };
    img.onerror = function () { if (loading) loading.classList.add('hidden'); };
    img.src = src;
    previewZoom = 1;
    previewNaturalW = img.naturalWidth || 0;
    previewNaturalH = img.naturalHeight || 0;
    const container = document.getElementById('preview-container');
    if (container) {
        container.scrollLeft = 0;
        container.scrollTop = 0;
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    if (img.complete && img.naturalWidth) {
        previewNaturalW = img.naturalWidth;
        previewNaturalH = img.naturalHeight;
        updatePreviewTransform();
    }
}

function closeImagePreview(e) {
    const modal = document.getElementById('image-preview-modal');
    if (!modal) return;
    if (e && e.target !== modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function previewZoomIn() {
    previewZoom = Math.min(previewZoom + 0.25, 5);
    updatePreviewTransform();
}

function previewZoomOut() {
    previewZoom = Math.max(previewZoom - 0.25, 0.25);
    updatePreviewTransform();
}

function previewZoomReset() {
    previewZoom = 1;
    updatePreviewTransform();
}

function updatePreviewTransform() {
    const wrapper = document.getElementById('preview-wrapper');
    const img = document.getElementById('preview-img');
    const label = document.getElementById('preview-zoom-label');
    if (!wrapper || !img) return;
    const w = previewNaturalW || img.naturalWidth || img.width || 1;
    const h = previewNaturalH || img.naturalHeight || img.height || 1;
    wrapper.style.width = Math.round(w * previewZoom) + 'px';
    wrapper.style.height = Math.round(h * previewZoom) + 'px';
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'contain';
    if (label) label.textContent = Math.round(previewZoom * 100) + '%';
}

function previewPanStart(e) {
    const c = document.getElementById('preview-container');
    if (!c || !c.contains(e.target)) return;
    isPanning = true;
    startX = e.clientX;
    startY = e.clientY;
    startScrollLeft = c.scrollLeft;
    startScrollTop = c.scrollTop;
}

function previewPan(e) {
    if (!isPanning) return;
    const c = document.getElementById('preview-container');
    c.scrollLeft = startScrollLeft - (e.clientX - startX);
    c.scrollTop = startScrollTop - (e.clientY - startY);
}

function previewPanEnd() {
    isPanning = false;
}

window.openImagePreview = openImagePreview;
window.closeImagePreview = closeImagePreview;
window.previewZoomIn = previewZoomIn;
window.previewZoomOut = previewZoomOut;
window.previewZoomReset = previewZoomReset;
window.previewPanStart = previewPanStart;
window.previewPan = previewPan;
window.previewPanEnd = previewPanEnd;

document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape') {
        const modal = document.getElementById('image-preview-modal');
        if (modal && !modal.classList.contains('hidden')) closeImagePreview();
    }
});

document.getElementById('preview-container')?.addEventListener('wheel', function (e) {
    const modal = document.getElementById('image-preview-modal');
    if (!modal || modal.classList.contains('hidden')) return;
    e.preventDefault();
    if (e.deltaY < 0) previewZoomIn();
    else previewZoomOut();
}, { passive: false });

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.img-previewable').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            if (this.tagName === 'IMG' && this.src) openImagePreview(this.src);
        });
    });
    if (typeof renderMathInElement !== 'undefined') {
        document.querySelectorAll('.math-content').forEach(el => {
            if (!el.dataset.mathRendered) {
                try {
                    renderMathInElement(el, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false }
                        ],
                        throwOnError: false
                    });
                    el.dataset.mathRendered = '1';
                } catch (e) {}
            }
        });
    }
});

window.renderMathInPage = function () {
    if (typeof renderMathInElement !== 'undefined') {
        document.querySelectorAll('.math-content').forEach(el => {
            if (!el.dataset.mathRendered) {
                try {
                    renderMathInElement(el, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false }
                        ],
                        throwOnError: false
                    });
                    el.dataset.mathRendered = '1';
                } catch (e) {}
            }
        });
    }
};
