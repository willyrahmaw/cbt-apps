window.editCategory = function (id, name, desc) {
    const formTitle = document.getElementById('form-title');
    const nameInput = document.getElementById('cat-name');
    const descInput = document.getElementById('cat-desc');
    const form = document.getElementById('category-form');
    const methodField = document.getElementById('method-field');

    if (formTitle) formTitle.textContent = 'Edit Kategori';
    if (nameInput) nameInput.value = name || '';
    if (descInput) descInput.value = desc || '';
    if (form) form.action = `/admin/categories/${id}`;
    if (methodField) methodField.innerHTML = '<input type="hidden" name=\"_method\" value=\"PUT\">';
};

