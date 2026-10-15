let pendingUtangId = null;
let currentMaxBalance = 0;

function openPaymentModal(id, maxBalance) {
    pendingUtangId = id;
    currentMaxBalance = parseFloat(maxBalance);

    document.getElementById('modalRemainingBalance').textContent = '₱' + currentMaxBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const input = document.getElementById('paymentAmount');
    input.max = currentMaxBalance;
    input.value = '';

    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    pendingUtangId = null;
}

document.getElementById('paymentForm').onsubmit = function (e) {
    e.preventDefault();
    const amount = parseFloat(document.getElementById('paymentAmount').value);

    // Add small tolerance for floating point errors
    if (!isNaN(amount) && amount > 0 && amount <= (currentMaxBalance + 0.01)) {
        const idToPay = pendingUtangId;
        closePaymentModal();
        proceedPayment(idToPay, amount);
    } else {
        showAlert('error', "Invalid payment amount. It must be greater than 0 and not exceed the remaining balance.");
    }
};

async function proceedPayment(id, amount) {
    const csrfInput = document.querySelector('#csrfForm input[name="_csrf"]');
    const csrfToken = csrfInput ? csrfInput.value : '';

    const btn = document.getElementById('action-' + id).querySelector('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin h-4 w-4 mr-1"></i>...';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    try {
        const formData = new FormData();
        formData.append('_csrf', csrfToken);
        formData.append('amount', amount);

        const baseUrl = AppUrl.replace(/\/+$/, '');
        const response = await fetch(`${baseUrl}/utang/${id}/pay`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        let data = null;
        try {
            data = await response.json();
        } catch (parseError) {
            data = null;
        }

        if (data && typeof data.success !== 'undefined') {
            if (data.success) {
                showAlert('success', 'Payment recorded successfully.');

                const newBalance = parseFloat(data.new_balance);

                // Update UI visually
                const urlParams = new URLSearchParams(window.location.search);
                const currentStatus = urlParams.get('status') || 'outstanding';

                if (newBalance <= 0) {
                    // Auto-redirect to the printable control sheet receipt upon full payment
                    window.location.href = baseUrl + '/sales/' + id + '/receipt?autoprint=1';
                } else {
                    // Update balance text and paid amount blindly (need original strings to parse old paid. We'll just reload the page for safety to reflect exact numbers)
                    window.location.reload();
                }
            } else {
                showAlert('error', data.error || `Request failed.`);
                window.location.reload(); // Reload to reset state safely
            }
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
    } catch (e) {
        showAlert('error', 'Network error. Please check your connection and try again.');
        window.location.reload();
    }
}

function showAlert(type, msg) {
    const container = document.getElementById('alertContainer');
    container.classList.remove('hidden');

    const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
    const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';

    container.innerHTML = `
        <div class="rounded-md ${bgColor} p-4 text-left border ${type === 'success' ? 'border-green-200' : 'border-red-200'}">
            <p class="text-sm font-medium ${textColor}">${msg}</p>
        </div>
    `;
    setTimeout(() => {
        container.classList.add('hidden');
        container.innerHTML = '';
    }, 4000);
}
