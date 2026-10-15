let pendingTbtId = null;
let maxReleaseQty = 0;

function openReleaseModal(id, maxQty) {
    pendingTbtId = id;
    maxReleaseQty = parseFloat(maxQty);

    document.getElementById('modalMaxQty').innerText = maxReleaseQty.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    const qtyInput = document.getElementById('releaseQty');
    qtyInput.value = maxReleaseQty;
    qtyInput.max = maxReleaseQty;

    document.getElementById('releaseModal').classList.remove('hidden');
}

function closeReleaseModal() {
    document.getElementById('releaseModal').classList.add('hidden');
    pendingTbtId = null;
    maxReleaseQty = 0;
}

async function submitRelease() {
    if (!pendingTbtId) return;

    const qtyInput = document.getElementById('releaseQty');
    const quantity = parseFloat(qtyInput.value);

    if (isNaN(quantity) || quantity <= 0 || quantity > maxReleaseQty) {
        alert('Please enter a valid quantity between 0.01 and ' + maxReleaseQty);
        return;
    }

    const csrfInput = document.querySelector('#csrfForm input[name="_csrf"]');
    const csrfToken = csrfInput ? csrfInput.value : '';

    const btn = document.getElementById('btnSubmitRelease');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin h-4 w-4 mr-1"></i>...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const formData = new FormData();
        formData.append('_csrf', csrfToken);
        formData.append('quantity', quantity);

        const baseUrl = AppUrl.replace(/\/+$/, '');
        const response = await fetch(`${baseUrl}/tbt/${pendingTbtId}/release`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        let data = null;
        try { data = await response.json(); } catch (e) { }

        if (data && data.success) {
            showAlert('success', 'Successfully released ' + data.quantity_released + ' items.');
            closeReleaseModal();

            // Reload the page to simply reflect all the new statuses and pending counts accurately
            // This is safer than DOM manipulation when dealing with partials and statuses changing
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } else {
            const errorMsg = (data && data.error) ? data.error : `Request failed (HTTP ${response.status}).`;
            showAlert('error', errorMsg);
            btn.disabled = false;
            btn.innerText = originalText;
        }
    } catch (e) {
        showAlert('error', 'Network error. Please try again.');
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

function showAlert(type, msg) {
    const container = document.getElementById('alertContainer');
    if (!container) return; // safety
    container.classList.remove('hidden');

    const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
    const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';

    container.innerHTML = `
        <div class="rounded-md ${bgColor} p-4">
            <h3 class="text-sm font-medium ${textColor}">${msg}</h3>
        </div>
    `;
    setTimeout(() => {
        container.classList.add('hidden');
        container.innerHTML = '';
    }, 4000);
}