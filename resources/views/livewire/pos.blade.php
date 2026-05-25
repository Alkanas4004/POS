<div class="container mx-auto px-4">
    <div class="grid lg:grid-cols-3 gap-6">
        
        <!-- القسم الأيمن: المنتجات -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden backdrop-blur-lg bg-white/95">
                <!-- شريط البحث -->
                <div class="p-6 bg-gradient-to-r from-purple-50 to-blue-50 border-b border-gray-200">
                    <div class="relative group">
                        <i class="fas fa-search absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 text-xl group-focus-within:text-purple-500 transition"></i>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="🔍 ابحث بالاسم أو الباركود (امسح الباركود هنا)"
                               class="w-full p-4 pr-14 border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 text-lg transition-all bg-white"
                               autofocus>
                        <i class="fas fa-barcode absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
                    </div>
                </div>
                
                <!-- شبكة المنتجات -->
                <div class="p-6 h-[calc(100vh-250px)] overflow-y-auto">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @forelse($products as $product)
                        <div wire:click="addProduct('{{ $product->barcode }}')"
                             class="group cursor-pointer rounded-2xl overflow-hidden card-hover {{ $product->quantity == 0 ? 'opacity-60' : '' }}">
                            <div class="bg-gradient-to-br from-white to-gray-50 p-4 text-center">
                                <!-- صورة المنتج -->
                                <div class="w-24 h-24 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-purple-100 to-blue-100 flex items-center justify-center group-hover:scale-110 transition">
                                    @if($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover rounded-2xl">
                                    @else
                                        <i class="fas fa-box-open text-5xl text-purple-400 group-hover:text-purple-600 transition"></i>
                                    @endif
                                </div>
                                
                                <!-- اسم المنتج -->
                                <h3 class="font-bold text-gray-800 text-lg mb-1 line-clamp-1">{{ $product->name }}</h3>
                                
                                <!-- السعر -->
                                <div class="text-2xl font-black text-green-600 mb-2">
                                    {{ number_format($product->selling_price, 2) }} <span class="text-sm">EGP</span>
                                </div>
                                
                                <!-- الكمية والحالة -->
                                <div class="flex justify-center items-center gap-2 text-sm">
                                    <i class="fas fa-boxes text-gray-400"></i>
                                    <span class="{{ $product->quantity <= $product->min_quantity ? 'text-orange-600 font-bold' : 'text-gray-600' }}">
                                        {{ $product->quantity }}
                                    </span>
                                    @if($product->isLowStock())
                                        <span class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded-full text-xs font-bold">
                                            <i class="fas fa-exclamation-triangle text-xs"></i> منخفض
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-20">
                            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-400 text-lg">لا توجد منتجات تطابق البحث</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <!-- القسم الأيسر: سلة المشتريات -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl shadow-2xl h-[calc(100vh-120px)] flex flex-col overflow-hidden sticky top-24">
                <!-- هيدر السلة -->
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-6 text-white">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-black flex items-center gap-2">
                            <i class="fas fa-shopping-cart"></i>
                            <span>سلة المشتريات</span>
                        </h2>
                        <span class="bg-white/20 px-3 py-1 rounded-full font-bold">
                            {{ count($cart) }} منتجات
                        </span>
                    </div>
                </div>
                
                <!-- عناصر السلة -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                    @forelse($cart as $id => $item)
                    <div class="cart-item bg-white rounded-2xl p-4 shadow-md hover:shadow-xl transition">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 text-lg">{{ $item['name'] }}</h3>
                                <p class="text-sm text-gray-500">{{ $item['barcode'] }}</p>
                            </div>
                            <button wire:click="removeItem({{ $id }})" 
                                    class="text-red-500 hover:text-red-700 transition hover:scale-110">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </button>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3 bg-gray-100 rounded-xl p-1">
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] - 1 }})" 
                                        class="w-9 h-9 rounded-lg bg-white text-gray-600 hover:bg-red-500 hover:text-white transition font-bold text-xl">
                                    -
                                </button>
                                <span class="w-12 text-center font-bold text-lg">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] + 1 }})" 
                                        class="w-9 h-9 rounded-lg bg-white text-gray-600 hover:bg-green-500 hover:text-white transition font-bold text-xl">
                                    +
                                </button>
                            </div>
                            <div class="text-left">
                                <div class="text-green-600 font-black text-xl">
                                    {{ number_format($item['total'], 2) }} EGP
                                </div>
                                <div class="text-sm text-gray-400">{{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}</div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-20">
                        <i class="fas fa-shopping-cart text-7xl text-gray-300 mb-4"></i>
                        <p class="text-gray-400 text-lg">السلة فارغة</p>
                        <p class="text-gray-300 text-sm">أضف منتجات من القسم الأيمن</p>
                    </div>
                    @endforelse
                </div>
                
                <!-- إجماليات السلة -->
                <div class="border-t-2 border-gray-200 p-6 bg-white">
                    <!-- المجموع -->
                    <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-100">
                        <span class="text-gray-600">المجموع</span>
                        <span class="text-xl font-bold">{{ number_format($subtotal, 2) }} EGP</span>
                    </div>
                    
                    <!-- الخصم -->
                    <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-100">
                        <span class="text-gray-600">
                            <i class="fas fa-tag text-orange-500"></i> الخصم
                        </span>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.live="discount" 
                                   class="w-28 p-2 border-2 border-gray-200 rounded-xl text-center font-bold focus:outline-none focus:border-purple-500">
                            <span class="text-gray-600">EGP</span>
                        </div>
                    </div>
                    
                    <!-- الإجمالي النهائي -->
                    <div class="flex justify-between items-center mb-4 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-2xl">
                        <span class="text-lg font-bold text-gray-800">الإجمالي</span>
                        <span class="text-3xl font-black bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                            {{ number_format($total, 2) }} EGP
                        </span>
                    </div>
                    
                    <!-- المدفوع -->
                    <div class="mb-4">
                        <label class="block text-gray-600 mb-2 font-bold">💰 المبلغ المدفوع</label>
                        <input type="number" wire:model.live="paid" 
                               class="w-full p-3 border-2 border-gray-200 rounded-xl text-center text-2xl font-bold focus:outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20"
                               placeholder="أدخل المبلغ">
                    </div>
                    
                    <!-- الباقي -->
                    <div class="mb-4 p-4 rounded-2xl {{ $change >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-600">الباقي للعميل</span>
                            <span class="text-2xl font-black {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($change, 2) }} EGP
                            </span>
                        </div>
                    </div>
                    
                    <!-- طريقة الدفع -->
                    <div class="mb-4">
                        <select wire:model="paymentMethod" 
                                class="w-full p-3 border-2 border-gray-200 rounded-xl font-bold focus:outline-none focus:border-purple-500">
                            <option value="cash">💰 كاش</option>
                            <option value="card">💳 كريديت كارد</option>
                            <option value="mobile">📱 محفظة إلكترونية</option>
                        </select>
                    </div>
                    
                    <!-- زر إنهاء الفاتورة -->
                    <button wire:click="completeSale"
                            class="w-full btn-gradient text-white p-4 rounded-2xl font-black text-xl transition-all hover:scale-105 shadow-xl flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        إنهاء الفاتورة
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
