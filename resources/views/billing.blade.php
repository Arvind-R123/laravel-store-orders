<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Billing - New Order</title>
    <style>
        :root{--ink:#17212b;--muted:#66737f;--line:#d9e0e6;--panel:#fff;--page:#f4f6f8;--orange:#fff0d8;--orange-ink:#854400;--green:#198754;--red:#bd2727}
        *{box-sizing:border-box}body{margin:0;background:var(--page);color:var(--ink);font:15px/1.45 Arial,sans-serif}.topbar{background:#1d2935;color:#fff;padding:20px 28px}.topbar h1{margin:0;font-size:22px}.page{width:min(1180px,calc(100% - 32px));margin:26px auto 50px}.layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:20px;align-items:start}.card{background:var(--panel);border:1px solid var(--line);border-radius:8px;padding:22px;box-shadow:0 2px 8px #17212b0b}.card+ .card{margin-top:18px}h2{font-size:18px;margin:0 0 17px}.customer-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}label{display:block;color:var(--muted);font-size:13px;font-weight:bold;margin-bottom:6px}input,select{width:100%;border:1px solid #bfccd5;border-radius:5px;padding:10px;background:#fff;color:var(--ink)}input:disabled{background:#eef1f3;color:#66737f}:focus{outline:3px solid #8fc8ff;outline-offset:1px}.hint{min-height:20px;color:var(--muted);font-size:13px;margin:7px 0 0}.table-wrap{overflow-x:auto}table{border-collapse:collapse;width:100%;min-width:690px}th,td{border-bottom:1px solid var(--line);padding:10px 8px;text-align:left;vertical-align:middle}th{background:#f3f5f7;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.04em}th:nth-child(n+3),td:nth-child(n+3){text-align:right;white-space:nowrap}.qty{min-width:78px}.remove{border:0;background:none;color:var(--red);cursor:pointer;font-size:20px}.add{margin-top:15px;padding:9px 13px;border:1px solid #71808c;border-radius:5px;background:#fff;font-weight:bold;cursor:pointer}.low{background:var(--orange);border-color:#efc176;color:var(--orange-ink)}.low ul{margin:0;padding-left:20px}.low li+li{margin-top:7px}.empty{color:var(--muted)}.payment{margin-top:18px}.payment-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}.payment-row{display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:9px 0}.grand{font-size:18px}.amount{margin-top:0}.balance{font-weight:bold;margin-top:15px}.balance.error{color:var(--red)}.denominations{color:var(--muted);font-size:13px;margin-top:9px}.submit{width:100%;margin-top:20px;padding:13px;border:0;border-radius:5px;background:var(--green);color:#fff;font-size:16px;font-weight:bold;cursor:pointer}.submit:disabled{opacity:.6;cursor:wait}.notice{margin-bottom:18px}.notice.error{color:var(--red);border-color:#e5a5a5;background:#fff3f3}.notice:empty{display:none}.receipt{background:#effaf3;border-color:#9bd3b4}.receipt dl{display:grid;grid-template-columns:145px 1fr;gap:7px 14px}.receipt dt{color:var(--muted)}.receipt dd{margin:0;font-weight:bold}.receipt ul{padding-left:20px}.receipt-actions{display:flex;gap:10px;margin-top:17px}.print{padding:9px 14px;border:1px solid var(--green);border-radius:5px;background:#fff;color:var(--green);cursor:pointer;font-weight:bold}.field-error{color:var(--red);font-size:13px;margin:5px 0 0}.stock-error{display:block;color:var(--red);font-size:13px;margin-top:5px}@media(max-width:800px){.layout,.customer-grid,.payment-grid{grid-template-columns:1fr}.page{width:calc(100% - 20px);margin-top:15px}.topbar{padding:17px 18px}}@media print{.topbar,.low,.customer-card,.products-card,.payment,.notice,.print{display:none!important}.page{width:100%;margin:0}.receipt{box-shadow:none;border:0}}
    </style>
</head>
<body>
<header class="topbar"><h1>Store Billing - New Order</h1></header>
<main class="page">
    <section id="notice" class="card notice" role="alert" hidden></section>
    <section id="receipt" class="card receipt" hidden></section>
    <div class="layout">
        <div>
            <section class="card customer-card">
                <h2>Customer</h2>
                <div class="customer-grid">
                    <div><label for="email">Email</label><input id="email" type="email" autocomplete="email" required><p id="email-error" class="field-error"></p></div>
                    <div><label for="name">Name</label><input id="name" type="text" autocomplete="name" disabled><p id="name-error" class="field-error"></p></div>
                </div>
                <p id="customer-hint" class="hint">Enter an email address to look up an existing customer.</p>
            </section>
            <section class="card products-card">
                <h2>Products</h2>
                <div class="table-wrap"><table><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>GST</th><th>Line Total</th><th></th></tr></thead><tbody id="rows"></tbody></table></div>
                <button id="add" class="add" type="button">+ Add Product</button>
                <div id="product-errors"></div>
            </section>
            <section class="card payment">
                <h2>Payment</h2>
                <div class="payment-grid"><div><div class="payment-row"><span>Subtotal</span><strong id="subtotal">₹0.00</strong></div><div class="payment-row"><span>Tax</span><strong id="tax">₹0.00</strong></div><div class="payment-row grand"><span>Grand Total</span><strong id="total">₹0.00</strong></div></div><div><label for="given">Amount Given</label><input id="given" type="number" min="0" step=".01" inputmode="decimal"><div id="balance" class="balance">Balance to Return: -</div><div id="denominations" class="denominations"></div></div></div>
                <button id="submit" class="submit" type="button">Generate Bill</button>
            </section>
        </div>
        <aside class="card low"><h2>Low Stock</h2><ul id="low-stock"><li class="empty">Loading...</li></ul></aside>
    </div>
</main>
<script>
(() => {
    'use strict';
    const api = '/api/v1';
    const state = { products: [], rows: [], customer: null, lookupTimer: null };
    const $ = id => document.getElementById(id);
    const money = paise => '₹' + (Number(paise) / 100).toFixed(2);
    const paiseFromPrice = price => Math.round(parseFloat(price) * 100);
    const roundHalfUp = value => Math.floor(value + 0.5);
    const product = id => state.products.find(item => Number(item.id) === Number(id));

    function totals() {
        return state.rows.reduce((sum, row) => {
            const item = product(row.productId);
            const quantity = Math.max(1, Math.min(Number(item?.stock || 1), parseInt(row.quantity, 10) || 1));
            if (!item) return sum;
            const subtotal = paiseFromPrice(item.price) * quantity;
            const tax = roundHalfUp(subtotal * (parseFloat(item.tax_percent) / 100));
            return { subtotal: sum.subtotal + subtotal, tax: sum.tax + tax };
        }, { subtotal: 0, tax: 0 });
    }

    function renderRows() {
        const selected = state.rows.map(row => Number(row.productId));
        $('rows').innerHTML = '';
        state.rows.forEach((row, index) => {
            const item = product(row.productId);
            const tr = document.createElement('tr');
            tr.innerHTML = '<td><select aria-label="Product"></select><span class="stock-error"></span></td><td><input class="qty" type="number" min="1" max="' + (item?.stock || 1) + '" value="' + row.quantity + '" aria-label="Quantity"></td><td class="price"></td><td class="gst"></td><td class="line-total"></td><td><button class="remove" type="button" aria-label="Remove product">×</button></td>';
            const select = tr.querySelector('select');
            select.innerHTML = state.products.map(option => {
                const unavailable = Number(option.stock) < 1;
                const duplicate = selected.includes(Number(option.id)) && Number(option.id) !== Number(row.productId);
                return '<option value="' + option.id + '" ' + (unavailable || duplicate ? 'disabled' : '') + '>' + option.name + (unavailable ? ' (out of stock)' : '') + '</option>';
            }).join('');
            select.value = row.productId;
            select.addEventListener('change', event => {
                if (state.rows.some((other, otherIndex) => otherIndex !== index && Number(other.productId) === Number(event.target.value))) {
                    event.target.value = row.productId;
                    return;
                }
                row.productId = event.target.value;
                row.quantity = Math.min(Math.max(1, Number(row.quantity) || 1), Number(product(row.productId)?.stock || 1));
                renderRows();
            });
            tr.querySelector('.qty').addEventListener('input', event => {
                row.quantity = Math.min(Math.max(1, parseInt(event.target.value, 10) || 1), Number(product(row.productId)?.stock || 1));
                event.target.value = row.quantity;
                calculate();
            });
            tr.querySelector('.remove').addEventListener('click', () => { state.rows.splice(index, 1); renderRows(); });
            $('rows').appendChild(tr);
        });
        calculate();
    }

    function calculate() {
        const result = totals();
        const total = result.subtotal + result.tax;
        [...$('rows').children].forEach((tr, index) => {
            const item = product(state.rows[index].productId);
            const quantity = Math.max(1, Math.min(Number(item?.stock || 1), parseInt(state.rows[index].quantity, 10) || 1));
            const subtotal = paiseFromPrice(item.price) * quantity;
            const tax = roundHalfUp(subtotal * (parseFloat(item.tax_percent) / 100));
            tr.querySelector('.price').textContent = money(paiseFromPrice(item.price));
            tr.querySelector('.gst').textContent = parseFloat(item.tax_percent).toFixed(2) + '%';
            tr.querySelector('.line-total').textContent = money(subtotal + tax);
        });
        $('subtotal').textContent = money(result.subtotal);
        $('tax').textContent = money(result.tax);
        $('total').textContent = money(total);
        renderBalance(total);
    }

    function renderBalance(total) {
        const raw = $('given').value.trim();
        $('balance').classList.remove('error');
        $('denominations').textContent = '';
        if (!raw) { $('balance').textContent = 'Balance to Return: -'; return; }
        const change = Math.round(parseFloat(raw) * 100) - total;
        if (change < 0) { $('balance').textContent = 'Short by ' + money(-change); $('balance').classList.add('error'); return; }
        let whole = Math.floor(change / 100);
        const paise = change % 100;
        const parts = [];
        [500, 200, 100, 50, 20, 10, 5, 2, 1].forEach(note => { const count = Math.floor(whole / note); if (count) parts.push(count + 'x' + note); whole %= note; });
        $('balance').textContent = 'Balance to Return: ' + money(change);
        $('denominations').textContent = '₹' + Math.floor(change / 100) + ' = ' + (parts.join(' + ') || 'no whole rupees') + ' and ' + paise + ' paise';
    }

    function renderLowStock(items) {
        items.sort((a, b) => Number(a.stock) - Number(b.stock));
        $('low-stock').innerHTML = items.length ? items.map(item => '<li>' + item.name + ' - ' + item.stock + ' left</li>').join('') : '<li class="empty">All stocked up</li>';
    }

    async function loadProducts() {
        const response = await fetch(api + '/products');
        if (!response.ok) throw new Error('Unable to load products.');
        state.products = (await response.json()).data || [];
        const valid = state.rows.filter(row => product(row.productId) && Number(product(row.productId).stock) > 0);
        state.rows = valid.length ? valid : (state.products.find(item => Number(item.stock) > 0) ? [{ productId: state.products.find(item => Number(item.stock) > 0).id, quantity: 1 }] : []);
        renderRows();
    }

    async function loadLowStock() {
        const response = await fetch(api + '/products/low-stock');
        if (!response.ok) throw new Error('Unable to load low-stock products.');
        renderLowStock((await response.json()).data || []);
    }

    function showNotice(message) { $('notice').textContent = message; $('notice').hidden = false; }
    function clearNotice() { $('notice').textContent = ''; $('notice').hidden = true; }
    function showValidation() {
        $('email-error').textContent = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('email').value.trim()) ? '' : 'Enter a valid email address.';
        $('name-error').textContent = state.customer ? '' : ($('name').value.trim() ? '' : 'Name is required for a new customer.');
        return !$('email-error').textContent && !$('name-error').textContent && state.rows.length > 0;
    }
    function lookupCustomer() {
        clearTimeout(state.lookupTimer);
        state.lookupTimer = setTimeout(async () => {
            const email = $('email').value.trim();
            if (!email) return;
            try {
                const response = await fetch(api + '/customers?email=' + encodeURIComponent(email));
                state.customer = response.ok ? await response.json() : null;
                state.customerEmail = email;
                $('name').disabled = Boolean(state.customer);
                $('name').value = state.customer ? state.customer.name : '';
                $('customer-hint').textContent = state.customer ? 'Existing customer found, name locked' : 'New customer, enter name';
            } catch { state.customer = null; $('name').disabled = false; $('customer-hint').textContent = 'New customer, enter name'; }
        }, 250);
    }
    function receipt(order, amount) {
        $('receipt').innerHTML = '<h2>Bill / Receipt</h2><dl><dt>Order number</dt><dd>' + order.order_number + '</dd><dt>Customer</dt><dd>' + $('name').value + ' (' + $('email').value + ')</dd><dt>Subtotal</dt><dd>' + money(parseFloat(order.subtotal) * 100) + '</dd><dt>Tax</dt><dd>' + money(parseFloat(order.tax_total) * 100) + '</dd><dt>Grand total</dt><dd>' + money(parseFloat(order.grand_total) * 100) + '</dd><dt>Amount given</dt><dd>' + money(amount) + '</dd><dt>Balance</dt><dd>' + money(Math.max(0, amount - Math.round(parseFloat(order.grand_total) * 100))) + '</dd></dl><ul>' + order.items.map(item => '<li>' + item.product.name + ': ' + item.quantity + ' × ' + money(parseFloat(item.unit_price) * 100) + ' = ' + money(parseFloat(item.line_total) * 100) + '</li>').join('') + '</ul><div class="receipt-actions"><button class="print" type="button" onclick="window.print()">Print</button></div>';
        $('receipt').hidden = false;
    }
    $('email').addEventListener('input', () => {
        state.customer = null;
        state.customerEmail = '';
        $('name').disabled = false;
        lookupCustomer();
    });
    $('email').addEventListener('blur', lookupCustomer);
    $('given').addEventListener('input', () => renderBalance(totals().subtotal + totals().tax));
    $('add').addEventListener('click', () => { const available = state.products.find(item => Number(item.stock) > 0 && !state.rows.some(row => Number(row.productId) === Number(item.id))); if (available) { state.rows.push({ productId: available.id, quantity: 1 }); renderRows(); } });
    $('submit').addEventListener('click', async () => {
        if (state.customerEmail !== $('email').value.trim()) state.customer = null;
        if (!showValidation()) return;
        clearNotice(); $('product-errors').innerHTML = ''; $('submit').disabled = true;
        const amount = Math.round(parseFloat($('given').value || '0') * 100);
        try {
            const response = await fetch(api + '/orders', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ customer: { email: $('email').value.trim(), name: $('name').value.trim() }, items: state.rows.map(row => ({ product_id: Number(row.productId), quantity: Number(row.quantity) })) }) });
            const body = await response.json();
            if (!response.ok) {
                const errors = Object.values(body.errors || {}).flatMap(value => Array.isArray(value) ? value : [value]);
                $('product-errors').innerHTML = errors.map(error => typeof error === 'object' ? '<span class="stock-error">' + (product(error.product_id)?.name || 'Product #' + error.product_id) + ': requested ' + error.requested + ', available ' + error.available + '</span>' : '<span class="stock-error">' + error + '</span>').join('');
                return;
            }
            receipt(body.data, amount); $('email').value = ''; $('name').value = ''; $('name').disabled = true; state.customer = null; $('given').value = ''; state.rows = [];
            await Promise.all([loadProducts(), loadLowStock()]);
        } catch { showNotice('The order could not be submitted. Check your connection or try again.'); } finally { $('submit').disabled = false; }
    });
    document.addEventListener('keydown', event => { if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') event.preventDefault(); });
    Promise.all([loadProducts(), loadLowStock()]).catch(() => showNotice('Unable to load billing data. Refresh and try again.'));
})();
</script>
</body>
</html>
