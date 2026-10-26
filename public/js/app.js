document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.alert')?.remove());
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });
});
