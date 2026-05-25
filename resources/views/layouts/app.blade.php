<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>نظام سوبر ماركت - سوبر ماركتي</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Tailwind + Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { font-family: 'Tajawal', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        
        /* تخصيص السكرول بار */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(45deg, #667eea, #764ba2); border-radius: 10px; }
        
        /* تأثيرات hover */
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        
        /* أزرار مخصصة */
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-gradient:hover { background: linear-gradient(135deg, #5a67d8 0%, #6b46a0 100%); transform: scale(1.05); }
        
        /* نبضات للتنبيه */
        @keyframes pulse-red {
            0%, 100% { background-color: #fee2e2; }
            50% { background-color: #fecaca; }
        }
        .pulse-warning { animation: pulse-red 1s ease-in-out infinite; }
        
        /* تأثير دخول المنتجات */
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .cart-item { animation: slideIn 0.3s ease-out; }
    </style>
</head>
<body class="font-tajawal">
    <!-- شريط التنقل العلوي -->
    <nav class="bg-white/95 backdrop-blur-lg shadow-2xl sticky top-0 z-50 border-b border-white/20">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-8 space-x-reverse">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-500 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                            <i class="fas fa-store text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-black bg-gradient-to-r from-purple-600 to-blue-500 bg-clip-text text-transparent">سوبر ماركتي</span>
                    </a>
                    
                    <div class="hidden md:flex space-x-2 space-x-reverse">
                        <a href="{{ route('pos') }}" class="px-5 py-2.5 rounded-xl font-bold transition-all duration-300 hover:bg-gradient-to-r hover:from-purple-500 hover:to-blue-500 hover:text-white group">
                            <i class="fas fa-cash-register ml-2 group-hover:scale-110 inline-block transition"></i>
                            كاشير
                        </a>
                        <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl font-bold transition-all duration-300 hover:bg-gradient-to-r hover:from-purple-500 hover:to-blue-500 hover:text-white group">
                            <i class="fas fa-boxes ml-2 group-hover:scale-110 inline-block transition"></i>
                            المنتجات
                        </a>
                        <a href="{{ route('reports') }}" class="px-5 py-2.5 rounded-xl font-bold transition-all duration-300 hover:bg-gradient-to-r hover:from-purple-500 hover:to-blue-500 hover:text-white group">
                            <i class="fas fa-chart-line ml-2 group-hover:scale-110 inline-block transition"></i>
                            التقارير
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-3 bg-gray-100 px-4 py-2 rounded-2xl">
                        <i class="fas fa-user-circle text-purple-600 text-xl"></i>
                        <span class="font-bold text-gray-700">{{ auth()->user()->name ?? 'مدير' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl font-bold bg-red-500 text-white hover:bg-red-600 transition-all hover:scale-105 shadow-lg">
                            <i class="fas fa-sign-out-alt ml-2"></i>
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-6">
        {{ $slot }}
    </main>

    @livewireScripts
    
    <script>
        // منع الـ context menu على الصور
        document.addEventListener('contextmenu', function(e) {
            if (e.target.tagName === 'IMG') e.preventDefault();
        });
        
        // رسائل toast
        window.showToast = function(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-2xl text-white font-bold z-50 animate-bounce ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} ml-2"></i>${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        };
        
        Livewire.on('alert', (data) => {
            showToast(data.message, data.type);
        });
        
        Livewire.on('focus-search', () => {
            setTimeout(() => document.querySelector('input[wire\\:model=\"search\"]')?.focus(), 100);
        });
    </script>
</body>
</html>
