window.previewLogo = function (input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-placeholder');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
            const removeInput = document.querySelector('input[name=remove_logo]');
            if (removeInput) removeInput.value = '0';
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.previewFavicon = function (input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('favicon-preview');
            const placeholder = document.getElementById('favicon-placeholder');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
            const removeInput = document.querySelector('input[name=remove_favicon]');
            if (removeInput) removeInput.value = '0';
        };
        reader.readAsDataURL(input.files[0]);
    }
};

