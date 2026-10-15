window.showToast = function (title, message, type = 'info') {
    const template = document.getElementById('toastTemplate');
    if (!template) return;

    const container = document.getElementById('toastContainer');
    const clone = template.content.cloneNode(true);
    const wrapper = clone.querySelector('div');

    const titleEl = clone.querySelector('.toast-title');
    const msgEl = clone.querySelector('.toast-msg');
    const icon = clone.querySelector('.toast-icon');

    titleEl.textContent = title;
    msgEl.textContent = message;

    if (type === 'success') {
        wrapper.querySelector('.border-l-4').classList.add('border-success');
        icon.setAttribute('data-lucide', 'check-circle-2');
        icon.classList.add('text-success');
    } else if (type === 'error') {
        wrapper.querySelector('.border-l-4').classList.add('border-error');
        icon.setAttribute('data-lucide', 'alert-circle');
        icon.classList.add('text-error');
    } else {
        wrapper.querySelector('.border-l-4').classList.add('border-info');
        icon.classList.add('text-info');
    }

    container.appendChild(clone);
    lucide.createIcons(); // Re-render newly inserted icon

    const toast = container.lastElementChild;
    const closeBtn = toast.querySelector('.toast-close');

    // Enter animation
    requestAnimationFrame(() => {
        toast.classList.remove('scale-95', 'opacity-0');
        toast.classList.add('scale-100', 'opacity-100');
    });

    const hide = () => {
        toast.classList.remove('scale-100', 'opacity-100');
        toast.classList.add('scale-95', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    };

    closeBtn.addEventListener('click', hide);
    setTimeout(hide, 5000);
};
