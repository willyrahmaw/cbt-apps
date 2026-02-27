function switchTab(tab) {
    const panels = document.querySelectorAll('.tab-panel');
    const tabs = document.querySelectorAll('.vtab');
    panels.forEach(p => p.classList.add('hidden'));
    tabs.forEach(b => b.classList.remove('active'));
    const panel = document.getElementById('panel-' + tab);
    const tabBtn = document.getElementById('tab-' + tab);
    if (panel) panel.classList.remove('hidden');
    if (tabBtn) tabBtn.classList.add('active');
}

function toggleClassField() {
    const roleSelect = document.getElementById('role-select');
    const classField = document.getElementById('class-field');
    if (!roleSelect || !classField) return;
    const role = roleSelect.value;
    classField.style.display = role === 'pengguna' ? '' : 'none';
}

window.switchTab = switchTab;
window.toggleClassField = toggleClassField;

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi default tab & field kelas
    toggleClassField();

    const root = document.querySelector('[data-open-password-tab]');
    if (root && root.getAttribute('data-open-password-tab') === '1') {
        switchTab('password');
    }
});

