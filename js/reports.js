let salesChart = null;

// تبديل التبويبات
function showTab(tab) {
    document.getElementById('salesTab').classList.add('hidden');
    document.getElementById('stockTab').classList.add('hidden');
    document.getElementById(`tabSalesBtn`).classList.remove('active', 'text-purple-600', 'border-b-2', 'border-purple-600');
    document.getElementById(`tabStockBtn`).classList.remove('active', 'text-purple-600', 'border-b-2', 'border-purple-600');
    
    if (tab === 'sales') {
        document.getElementById('salesTab').classList.remove('hidden');
        document.getElementById('tabSalesBtn').classList.add('active', 'text-purple-600', 'border-b-2', 'border-purple-600');
        loadSalesReport();
    } else {
        document.getElementById('stockTab').classList.remove('hidden');
        document.getElementById('tabStockBtn').classList.add('active', 'text-purple-600', 'border-b-2', 'border-purple-600');
        loadStockReport();
    }
}

// تحميل تقرير المبيعات
async function loadSalesReport() {
    const startDate = document.getElementById('startDate').value || getDefaultStartDate();
    const endDate = document.getElementById('endDate').value || getTodayDate();
    
    document.getElementById('startDate').value = startDate;
    document.getElementById('endDate').value = endDate;
    
    try {
        const response = await fetch(`api/get_reports.php?type=sales&start_date=${startDate}&end_date=${endDate}`);
        const result = await response.json();
        
        if (result.success) {
            displaySalesReport(result.data.sales, result.data);
        }
    } catch (error) {
        showToast('خطأ في تحميل التقرير', 'error');
    }
}

// عرض تقرير المبيعات
function displaySalesReport(sales, data) {
    const container = document.getElementById('salesReportContent');
    
    if (!sales || sales.length === 0) {
        container.innerHTML = '<div class="text-center py-20 text-gray-400">لا توجد مبيعات في هذه الفترة</div>';
        return;
    }
    
    const total = sales.reduce((sum, s) => sum + parseFloat(s.total_amount), 0);
    
    container.innerHTML = `
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl p-4 mb-6">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">إجمالي المبيعات</span>
                <span class="text-2xl font-bold text-purple-600">${total.toFixed(2)} EGP</span>
            </div>
            <div class="flex justify-between items-center mt-2">
                <span class="text-gray-600">عدد الفواتير</span>
                <span class="text-xl font-bold">${sales.length}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr><th class="px-4 py-2">رقم الفاتورة</th><th class="px-4 py-2">الإجمالي</th><th class="px-4 py-2">طريقة الدفع</th><th class="px-4 py-2">التاريخ</th></tr>
                </thead>
                <tbody>
                    ${sales.map(s => `
                        <tr class="border-b">
                            <td class="px-4 py-2 text-center">${s.invoice_number}</td>
                            <td class="px-4 py-2 text-center text-green-600 font-bold">${parseFloat(s.total_amount).toFixed(2)} EGP</td>
                            <td class="px-4 py-2 text-center">${getPaymentMethodIcon(s.payment_method)}</td>
                            <td class="px-4 py-2 text-center">${new Date(s.created_at).toLocaleString()}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// تحميل تقرير المخزون
async function loadStockReport() {
    try {
        const response = await fetch('api/get_reports.php?type=stock');
        const result = await response.json();
        
        if (result.success) {
            displayStockReport(result.data.products);
        }
    } catch (error) {
        showToast('خطأ في تحميل التقرير', 'error');
    }
}

// عرض تقرير المخزون
function displayStockReport(products) {
    const container = document.getElementById('stockReportContent');
    
    if (!products || products.length === 0) {
        container.innerHTML = '<div class="text-center py-20 text-gray-400">لا توجد منتجات</div>';
        return;
    }
    
    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr><th class="px-4 py-2">المنتج</th><th class="px-4 py-2">الباركود</th><th class="px-4 py-2">الكمية</th><th class="px-4 py-2">سعر البيع</th><th class="px-4 py-2">الحالة</th></tr>
                </thead>
                <tbody>
                    ${products.map(p => `
                        <tr class="border-b">
                            <td class="px-4 py-2">${escapeHtml(p.name)}</td>
                            <td class="px-4 py-2">${p.barcode}</td>
                            <td class="px-4 py-2 ${p.quantity <= p.min_quantity ? 'text-orange-600 font-bold' : ''}">${p.quantity}</td>
                            <td class="px-4 py-2">${parseFloat(p.selling_price).toFixed(2)} EGP</td>
                            <td class="px-4 py-2">${getStockStatus(p)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// دوال مساعدة
function getPaymentMethodIcon(method) {
    const icons = { cash: '💰 كاش', card: '💳 كارد', mobile: '📱 محفظة' };
    return icons[method] || method;
}

function getStockStatus(product) {
    if (product.quantity === 0) return '<span class="text-red-600">⚠️ نفد من المخزون</span>';
    if (product.quantity <= product.min_quantity) return '<span class="text-orange-600">⚠️ مخزون منخفض</span>';
    return '<span class="text-green-600">✓ متوفر</span>';
}

function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

function getDefaultStartDate() {
    let d = new Date();
    d.setMonth(d.getMonth() - 1);
    return d.toISOString().split('T')[0];
}

function showToast(msg, type) {
    let t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} ml-2"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m]));
}

// تحميل أولي
document.getElementById('startDate').value = getDefaultStartDate();
document.getElementById('endDate').value = getTodayDate();
loadSalesReport();
