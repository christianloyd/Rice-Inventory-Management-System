document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('itemTemplate').innerHTML;
    const addBtn = document.getElementById('addItemBtn');
    let itemIndex = 0;

    function addItem() {
        const html = template.replace(/{index}/g, itemIndex);
        const div = document.createElement('div');
        div.innerHTML = html;
        const row = div.firstElementChild;
        container.appendChild(row);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons({ root: row });
        }

        attachEvents(row);
        itemIndex++;
        updateTotals();
    }

    function attachEvents(row) {
        // Remove
        row.querySelector('.remove-btn').addEventListener('click', function () {
            if (container.children.length > 1) {
                row.remove();
                updateTotals();
            } else {
                alert('At least one line item is required.');
            }
        });

        // Variant change -> set unit price
        const variantSelect = row.querySelector('.variant-select');
        const priceInput = row.querySelector('.price-input');
        const qtyInput = row.querySelector('.qty-input');

        variantSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                priceInput.value = selected.getAttribute('data-price') || 0;
            } else {
                priceInput.value = '';
            }
            updateRowTotal(row);
        });

        // Re-calc triggers
        priceInput.addEventListener('input', () => updateRowTotal(row));
        qtyInput.addEventListener('input', () => updateRowTotal(row));

        // Remarks toggle -> TBT fields
        const remarkRadios = row.querySelectorAll('.remark-radio');
        const tbtFields = row.querySelector('.tbt-fields');
        const tbtReq = row.querySelector('.tbt-req');

        remarkRadios.forEach(r => {
            r.addEventListener('change', function () {
                if (this.value === 'tbt') {
                    tbtFields.classList.remove('hidden');
                    tbtReq.required = true;
                } else {
                    tbtFields.classList.add('hidden');
                    tbtReq.required = false;
                    tbtReq.value = ''; // clear out
                }
            });
        });
    }

    function updateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        row.querySelector('.subtotal-display').textContent = '₱' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateTotals();
    }

    function updateTotals() {
        let total = 0;
        container.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            total += (qty * price);
        });
        document.getElementById('grandTotal').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Initialize with 1 item
    addItem();

    addBtn.addEventListener('click', addItem);

    // Form loading state
    const form = document.getElementById('transactionForm');
    form.addEventListener('submit', function (e) {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin -ml-1 mr-2 h-4 w-4"></i> Processing...';
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    });
});
