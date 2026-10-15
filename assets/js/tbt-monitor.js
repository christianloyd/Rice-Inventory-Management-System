let pendingTbtId = null;

function releaseTbt(id) {
    pendingTbtId = id;
    const form = document.getElementById('confirmForm');

    form.onsubmit = function (e) {
        e.preventDefault();
        closeConfirmModal();
        proceedRelease(pendingTbtId);
    };

    showConfirmModal('#', 'Release TBT', 'Are you sure you want to mark this TBT as Released?', 'primary');
}

async function proceedRelease(id) {
    const csrfInput = document.querySelector('#csrfForm input[name="_csrf"]');
    const csrfToken = csrfInput ? csrfInput.value : '';

    const row = document.getElementById('row-' + id);
    const btn = row.querySelector('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin h-4 w-4 mr-1"></i>...';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    try {
        const formData = new FormData();
        formData.append('_csrf', csrfToken);

        // Normalize base URL — strip any trailing slash so we never produce a double slash
        const baseUrl = AppUrl.replace(/\/+$/, '');
        const response = await fetch(`${baseUrl}/tbt/${id}/release`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Always try to read the JSON body first — the backend returns a
        // meaningful {success, error} payload even on 400 responses.
        // Only fall back to a generic message if there's truly no JSON body
        // (e.g. a 500 from an unhandled server error, or a proxy/gateway error).
        let data = null;
        try {
            data = await response.json();
        } catch (parseError) {
            data = null;
        }

        if (data && typeof data.success !== 'undefined') {
            if (data.success) {
                showAlert('success', 'TBT successfully marked as Released.');

                // Get current filter from URL
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('status') === 'pending_pickup') {
                    // Remove row from pending view
                    row.remove();
                } else {
                    // Update badge and remove button for other views
                    const tdStatus = row.querySelectorAll('td')[4];
                    tdStatus.innerHTML = '<span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Released</span>';
                    const tdAction = row.querySelectorAll('td')[5];
                    tdAction.innerHTML = '';
                }
            } else {
                showAlert('error', data.error || `Request failed (HTTP ${response.status}).`);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check" class="h-4 w-4 mr-1"></i> Release';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }
        } else {
            // No parseable JSON body — genuinely unexpected server response
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
    } catch (e) {
        const msg = e.message && e.message.startsWith('HTTP ')
            ? `Release failed (${e.message}). Please contact support.`
            : 'Network error. Please check your connection and try again.';
        showAlert('error', msg);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check" class="h-4 w-4 mr-1"></i> Release';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
}

function showAlert(type, msg) {
    const container = document.getElementById('alertContainer');
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