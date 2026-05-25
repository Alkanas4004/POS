<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class Pos extends Component
{
    public $cart = [];
    public $search = '';
    public $subtotal = 0;
    public $discount = 0;
    public $tax = 0;
    public $total = 0;
    public $paid = 0;
    public $change = 0;
    public $paymentMethod = 'cash';

    protected $listeners = ['clearCart'];

    public function updatedPaid()
    {
        $this->calculateChange();
    }

    public function updatedDiscount()
    {
        $this->calculateTotal();
    }

    public function addProduct($barcode)
    {
        $product = Product::where('barcode', $barcode)->first();
        
        if (!$product) {
            $this->dispatch('alert', type: 'error', message: '❌ المنتج غير موجود!');
            return;
        }

        if ($product->quantity <= 0) {
            $this->dispatch('alert', type: 'error', message: '⚠️ المنتج غير متوفر بالمخزون!');
            return;
        }

        if (isset($this->cart[$product->id])) {
            $newQuantity = $this->cart[$product->id]['quantity'] + 1;
            if ($newQuantity > $product->quantity) {
                $this->dispatch('alert', type: 'error', message: '⚠️ الكمية المطلوبة أكبر من المتوفرة!');
                return;
            }
            $this->cart[$product->id]['quantity'] = $newQuantity;
            $this->cart[$product->id]['total'] = $newQuantity * $product->selling_price;
        } else {
            $this->cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
                'barcode' => $product->barcode
            ];
        }

        $this->calculateTotal();
        $this->search = '';
        $this->dispatch('focus-search');
        $this->dispatch('alert', type: 'success', message: "✅ تم إضافة {$product->name}");
    }

    public function removeItem($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeItem($productId);
            return;
        }

        $product = Product::find($productId);
        if ($quantity > $product->quantity) {
            $this->dispatch('alert', type: 'error', message: '⚠️ الكمية المطلوبة أكبر من المتوفرة!');
            return;
        }

        $this->cart[$productId]['quantity'] = $quantity;
        $this->cart[$productId]['total'] = $quantity * $this->cart[$productId]['price'];
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->subtotal = array_sum(array_column($this->cart, 'total'));
        $this->total = max(0, $this->subtotal - $this->discount + $this->tax);
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->change = max(0, $this->paid - $this->total);
    }

    public function completeSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', type: 'error', message: '🛒 السلة فارغة! أضف منتجات أولاً');
            return;
        }

        if ($this->paid < $this->total) {
            $this->dispatch('alert', type: 'error', message: '💸 المبلغ المدفوع أقل من إجمالي الفاتورة!');
            return;
        }

        DB::beginTransaction();
        
        try {
            $sale = Sale::create([
                'invoice_number' => 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'tax' => $this->tax,
                'total_amount' => $this->total,
                'paid_amount' => $this->paid,
                'change_amount' => $this->change,
                'payment_method' => $this->paymentMethod,
                'user_id' => auth()->id()
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ]);

                Product::where('id', $item['id'])->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            $this->dispatch('print-invoice', sale: $sale->load('items.product'));
            
            $this->reset(['cart', 'subtotal', 'discount', 'tax', 'total', 'paid', 'change']);
            $this->dispatch('alert', type: 'success', message: '🎉 تمت عملية البيع بنجاح!');
            $this->dispatch('focus-search');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('alert', type: 'error', message: '❌ حدث خطأ: ' . $e->getMessage());
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->calculateTotal();
    }

    public function render()
    {
        $products = Product::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('barcode', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->take(30)
            ->get();

        return view('livewire.pos', compact('products'))->layout('layouts.app');
    }
}
