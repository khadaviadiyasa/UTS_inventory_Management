(() => {
    const products = Array.isArray(window.inventoryProducts) ? window.inventoryProducts : [];
    const currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

    document.querySelectorAll('[data-transaction-form]').forEach((form) => {
        const mode = form.dataset.mode;
        const lines = form.querySelector('[data-lines]');
        const empty = form.querySelector('[data-line-empty]');
        const addButton = form.querySelector('[data-add-row]');
        const totalNode = form.querySelector('[data-total]');
        let nextIndex = 0;

        const findProduct = (id) => products.find((product) => String(product.id) === String(id));

        function recalculate() {
            let total = 0;
            lines.querySelectorAll('[data-transaction-line]').forEach((row) => {
                const product = findProduct(row.querySelector('[data-product-select]').value);
                const quantity = Math.max(0, Number.parseInt(row.querySelector('[data-quantity]').value || '0', 10));
                const costInput = row.querySelector('[data-unit-cost]');
                const unitPrice = mode === 'purchase' ? Number(costInput?.value || 0) : Number(product?.selling_price || 0);
                const subtotal = quantity * unitPrice;
                total += subtotal;
                row.querySelector('[data-unit-label]').textContent = currency.format(unitPrice);
                row.querySelector('[data-subtotal]').textContent = currency.format(subtotal);
                const stockHint = row.querySelector('[data-stock-hint]');
                if (stockHint && product) {
                    stockHint.textContent = mode === 'sale' ? `Stok tersedia: ${product.stock}` : `Stok saat ini: ${product.stock}`;
                    stockHint.classList.toggle('text-danger', mode === 'sale' && quantity > product.stock);
                }
            });
            totalNode.textContent = currency.format(total);
            empty.hidden = lines.children.length > 0;
        }

        function addRow() {
            const index = nextIndex++;
            const row = document.createElement('div');
            row.className = 'transaction-line';
            row.dataset.transactionLine = 'true';

            const productField = document.createElement('div');
            productField.className = 'line-product form-field';
            const productLabel = document.createElement('label');
            productLabel.textContent = 'Produk';
            productLabel.htmlFor = `item-product-${index}`;
            const select = document.createElement('select');
            select.id = `item-product-${index}`;
            select.name = `items[${index}][product_id]`;
            select.required = true;
            select.dataset.productSelect = 'true';
            const placeholder = new Option('Pilih produk', '');
            select.add(placeholder);
            products.forEach((product) => {
                const option = new Option(`${product.name} · ${product.sku}`, String(product.id));
                option.dataset.stock = String(product.stock);
                select.add(option);
            });
            productField.append(productLabel, select);

            const qtyField = document.createElement('div');
            qtyField.className = 'line-qty form-field';
            const qtyLabel = document.createElement('label');
            qtyLabel.textContent = 'Kuantitas';
            qtyLabel.htmlFor = `item-qty-${index}`;
            const qty = document.createElement('input');
            qty.id = `item-qty-${index}`;
            qty.name = `items[${index}][quantity]`;
            qty.type = 'number';
            qty.min = '1';
            qty.max = '1000000';
            qty.step = '1';
            qty.value = '1';
            qty.required = true;
            qty.dataset.quantity = 'true';
            qtyField.append(qtyLabel, qty);

            const priceField = document.createElement('div');
            priceField.className = 'line-price form-field';
            const priceLabel = document.createElement('label');
            priceLabel.textContent = mode === 'purchase' ? 'Harga modal / unit' : 'Harga jual / unit';
            const priceValue = document.createElement('strong');
            priceValue.className = 'line-price-value';
            priceValue.dataset.unitLabel = 'true';
            priceValue.textContent = currency.format(0);
            const stockHint = document.createElement('small');
            stockHint.dataset.stockHint = 'true';
            stockHint.className = 'field-hint';
            priceField.append(priceLabel, priceValue, stockHint);

            if (mode === 'purchase') {
                const cost = document.createElement('input');
                cost.type = 'number';
                cost.min = '0';
                cost.max = '999999999999.99';
                cost.step = '0.01';
                cost.required = true;
                cost.name = `items[${index}][unit_cost]`;
                cost.dataset.unitCost = 'true';
                cost.className = 'line-cost-input';
                priceField.append(cost);
                cost.addEventListener('input', recalculate);
            }

            const subtotalField = document.createElement('div');
            subtotalField.className = 'line-subtotal form-field';
            const subtotalLabel = document.createElement('label');
            subtotalLabel.textContent = 'Subtotal';
            const subtotal = document.createElement('strong');
            subtotal.className = 'line-subtotal-value';
            subtotal.dataset.subtotal = 'true';
            subtotal.textContent = currency.format(0);
            subtotalField.append(subtotalLabel, subtotal);

            const actionField = document.createElement('div');
            actionField.className = 'line-remove-wrap';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'line-remove';
            remove.textContent = '×';
            remove.title = 'Hapus item';
            remove.setAttribute('aria-label', 'Hapus item');
            remove.addEventListener('click', () => { row.remove(); recalculate(); });
            actionField.append(remove);

            select.addEventListener('change', () => {
                const selectedProduct = findProduct(select.value);
                if (mode === 'purchase') {
                    const cost = row.querySelector('[data-unit-cost]');
                    if (selectedProduct && cost.value === '') cost.value = selectedProduct.purchase_price;
                    if (selectedProduct) cost.value = selectedProduct.purchase_price;
                }
                recalculate();
            });
            qty.addEventListener('input', recalculate);
            row.append(productField, qtyField, priceField, subtotalField, actionField);
            lines.append(row);
            if (mode === 'purchase') {
                // Use the product's saved purchase price as a convenient starting point.
                const cost = row.querySelector('[data-unit-cost]');
                select.addEventListener('change', () => {
                    const selectedProduct = findProduct(select.value);
                    cost.value = selectedProduct ? selectedProduct.purchase_price : '';
                    recalculate();
                });
            }
            recalculate();
        }

        addButton?.addEventListener('click', addRow);
        if (products.length > 0) addRow();
        else recalculate();
    });
})();
