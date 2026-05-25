let cart = [];
let products = [];

async function loadProducts(search = '') {
    try {
        const response = await fetch(`api/get_products.php?search=${encodeURIComponent(search)}&limit=50`);
        const result = await response.json();
        if (result.success) {
            products = result.data;
            displayProducts();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function displayProducts() {
    const grid = document.querySelector('#productsGrid .grid');
    if (!grid) return;
    
    if (products.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center py-20 text-gray-400">لا توجد منتجات</div>';
        return;
    }
    
    grid.innerHTML = products.map(product => `
        <div onclick="addToCart(${product.id})" class="product-card border rounded-2xl p-4 text-center cursor-pointer hover:shadow-xl transition bg-white">
            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-purple-100 to-blue-100 rounded-2xl flex items-center justify-center mb-3">
                <i class="fas fa-box-open text-3xl text-purple-500"></i>
            </div>
            <h3 class="font-bold">${escapeHtml(product.name)}</h3>
            <div class="text-green-600 font-bold text-xl">${product.selling_price} EGP</div>
            <div class="text-sm text-gray-500">المتبقي: ${product.quantity}</div>
            ${product.quantity <= product.min_quantity ? '<div class="text-xs text-orange-500">⚠️ مخزون منخفض</div>' : ''}
        </div>
    `).join('');
}

function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product || product.quantity <= 0) {
        showToast('المنتج غير متوفر', 'error');
        return;
    }
    
    const existing = cart.find(item => item.id === productId);
    if (existing) {
        if (existing.quantity + 1 > product.quantity) {
            showToast('الكمية أكبر من المتوفرة', 'error');
            return;
        }
        existing.quantity++;
        existing.total = existing.quantity * existing.price;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.selling_price),
            quantity: 1,
            total: parseFloat(product.selling_price)
        });
    }
    updateCart();
    showToast(`تم إضافة ${product.name}`, 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

function updateQuantity(productId, quantity) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;
    
    const product = products.find(p => p.id === productId);
    if (quantity > product.quantity) {
        showToast('الكمية أكبر من المتوفرة', 'error');
        return;
    }
    
    if (quantity <= 0) {
        removeFromCart(productId);
        return;
    }
    
    item.quantity = quantity;
    item.total = item.quantity * item.price;
    updateCart();
}

function updateCart() {
    const cartContainer = document.getElementById('cartItems');
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const total = subtotal - discount;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    const change = paid - total;
    
    document.getElementById('subtotal').innerHTML = `${subtotal.toFixed(2)} EGP`;
    document.getElementById('total').innerHTML = `${total.toFixed(2)} EGP`;
    document.getElementById('change').innerHTML = `${change.toFixed(2)} EGP`;
    document.getElementById('cartCount').innerHTML = cart.length;
    
    if (cart.length === 0) {
        cartContainer.innerHTML = `<div class="text-center text-gray-400 py-20"><i class="fas fa-shopping-cart text-6xl mb-3"></i><p>السلة فارغة</p></div>`;
        return;
    }
    
    cartContainer.innerHTML = cart.map(item => `
        <div class="cart-item bg-white rounded-2xl p-4 shadow-md">
            <div class="flex justify-between items-start">
                <div><h3 class="font-bold">${escapeHtml(item.name)}</h3><div class="text-sm text-gray-500">${item.price} EGP</div></div>
                <button onclick="removeFromCart(${item.id})" class="text-red-500"><i class="fas fa-trash-alt"></i></button>
            </div>
            <div class="flex justify-between items-center mt-2">
                <div class="flex gap-2">
                    <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="w-8 h-8 bg-gray-200 rounded-full">-</button>
                    <span class="w-10 text-center font-bold">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="w-8 h-8 bg-gray-200 rounded-full">+</button>
                </div>
                <div class="font-bold text-green-600">${item.total.toFixed(2)} EGP</div>
            </div>
        </div>
    `).join('');
}

async function completeSale() {
    if (cart.length === 0) { showToast('السلة فارغة', 'error'); return; }
    
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const total = subtotal - discount;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    
    if (paid < total) { showToast('المبلغ أقل من الإجمالي', 'error'); return; }
    
    const saleData = { cart, subtotal, discount, tax: 0, total, paid, change: paid - total, payment_method: document.getElementById('paymentMethod').value, user_id: 1 };
    
    try {
        const response = await fetch('api/add_sale.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(saleData) });
        const result = await response.json();
        if (result.success) {
            showToast('تمت عملية البيع بنجاح 🎉', 'success');
            printInvoice(result.invoice_number, cart, total, paid);
            cart = []; updateCart();
            document.getElementById('paidAmount').value = '';
            document.getElementById('discount').value = 0;
            loadProducts();
        } else { showToast(result.message, 'error'); }
    } catch (error) { showToast('خطأ في الاتصال', 'error'); }
}

function printInvoice(invoiceNumber, cartItems, total, paid) {
    let w = window.open('', '_blank');
    w.document.write(`
        <html dir="rtl"><head><title>فاتورة ${invoiceNumber}</title>
        <style>body{font-family:monospace;padding:20px;text-align:center} table{width:100%;margin:20px 0} th,td{padding:8px;border-bottom:1px solid #ddd}</style>
        </head><body>
        <h2>🏪 سوبر ماركتي</h2><p><strong>فاتورة:</strong> ${invoiceNumber}</p><p><strong>التاريخ:</strong> ${new Date().toLocaleString()}</p><hr>
        <table><thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead><tbody>
        ${cartItems.map(item => `<tr><td>${item.name}</td><td>${item.quantity}</td><td>${item.price}</td><td>${item.total.toFixed(2)}</td></tr>`).join('')}
        </tbody></table><hr><h3>الإجمالي: ${total.toFixed(2)} EGP</h3><p>المدفوع: ${paid.toFixed(2)} EGP</p><p>الباقي: ${(paid - total).toFixed(2)} EGP</p><hr><footer>شكراً لزيارتكم 🌟</footer></body></html>
    `);
    w.print(); w.close();
}

function showToast(msg, type) {
    let t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} ml-2"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m])); }

document.getElementById('searchInput').addEventListener('input', (e) => loadProducts(e.target.value));
document.getElementById('paidAmount').addEventListener('input', updateCart);
document.getElementById('discount').addEventListener('input', updateCart);
loadProducts();
