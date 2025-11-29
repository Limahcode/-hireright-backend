<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated store owner.
     */
    public function index(Request $request)
    {
        LOG:info("<---- Index called -----> ");
        $storeId = Auth::user()->store->id; // Assuming user is a store owner
        $status = $request->query('status');
        $query = Order::where('store_id', $storeId);

        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10); // Paginate results for production performance

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'order_desc' => 'nullable|string',
            'discount_code' => 'nullable|string|max:50',
            'platform' => 'required|string|in:web,mobile',
            'currency_code' => 'required|string|max:10',
            'payment_option' => 'required|string|in:cash,credit_card,bank_transfer',
            'delivery_option' => 'required|string|in:pickup,delivery',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            // 'customer_id' => 'required|exists:users,id',
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            $storeId = Auth::user()->store->id; // Assuming user is a store owner
            $customer = User::findOrFail($validated['customer_id']);
            // Calculate totals and build order
            $totalQty = 0;
            $subtotal = 0;
            $totalDiscount = $validated['discount_code'] ? $this->applyDiscount($validated['discount_code']) : 0;
            // Create the order
            $order = Order::create([
                'reference' => $this->generateUniqueReference(),
                'order_desc' => $validated['order_desc'],
                'discount_code' => $validated['discount_code'],
                'platform' => $validated['platform'],
                'currency_code' => $validated['currency_code'],
                'payment_option' => $validated['payment_option'],
                'delivery_option' => $validated['delivery_option'],
                'customer_id' => $customer->id,
                'store_id' => $storeId,
            ]);
            // Process products
            foreach ($validated['items'] as $productInput) {
                $product = Product::findOrFail($productInput['product_id']);
                $qty = $productInput['qty'];
                $subtotal += $product->price * $qty;
                $totalQty += $qty;
                // Attach product to order (many-to-many relationship with pivot table for quantities)
                $order->products()->attach($product->id, ['qty' => $qty]);
            }
            // Finalize totals
            $order->total_qty = $totalQty;
            $order->subtotal = $subtotal;
            $order->total = $subtotal - $totalDiscount;
            $order->total_discount = $totalDiscount;
            $order->vat = $this->calculateVAT($order->total);
            $order->balance = $order->total - $order->total_paid;
            $order->save();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $storeId = Auth::user()->store->id;
        $order = Order::where('store_id', $storeId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Update the specified order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        $storeId = Auth::user()->store->id;
        $order = Order::where('store_id', $storeId)->findOrFail($id);

        $order->status = $validated['status'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'order' => $order,
        ]);
    }

    /**
     * Helper method to generate unique reference for an order.
     */
    private function generateUniqueReference()
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    /**
     * Helper method to calculate VAT.
     */
    private function calculateVAT($amount)
    {
        $vatRate = 0.075; // 7.5% VAT
        return $amount * $vatRate;
    }

    /**
     * Helper method to apply discount based on the discount code.
     */
    private function applyDiscount($discountCode)
    {
        // Here you can add logic to apply a discount based on the code
        // Example: find the discount and return the discount value
        return 10.00; // Assuming a fixed discount for simplicity
    }
}
