<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ProductPricing;
use App\Models\OnlinePayment;
use App\Models\Store;
use App\Models\PaymentGateway;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated customer.
     */
    public function index(Request $request)
    {
        // Retrieve authenticated user using JWTAuth
        $user = Auth::user();
        $customerId = $user->id;
        //
        $status = $request->query('status');
        //
        $query = Order::where('customer_id', $customerId)
            ->where('staged', false); // Exclude staged orders
        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }
        // Paginate the results
        $orders = $query->paginate(10);
        // 
        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Store a newly created order for the customer.
     */
    public function store(Request $request)
    {
        $countryCode = 'NG';
        $regionCode = $request->input('region_code') ?? 'LAG';
        $generate_link = $request->input('generate_link') ?? false;
        // Validate the incoming request
        $validated = $request->validate([
            'store_id' => 'required|integer', // 'required|integer|exists:stores,id', Ensure store_id exists in the stores table
            'order_desc' => 'nullable|string',
            'discount_code' => 'nullable|string|max:50',
            'platform' => 'required|string|in:web,mobile',
            'currency_code' => 'nullable|string|max:10',
            'payment_option' => 'required|string|in:instant,pod',
            'delivery_option' => 'required|string|in:pickup,delivery',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $customerId = $user->id;
            // Set default values for missing fields
            $validated['currency_code'] = $validated['currency_code'] ?? 'NGN';
            $validated['order_desc'] = $validated['order_desc'] ?? '';
            $validated['discount_code'] = $validated['discount_code'] ?? null;
            $validated['gateway_option'] = $validated['gateway_option'] ?? 'paystack';

            // LOG::info($validated);
            // Retrieve the store and its settings
            $store = Store::findOrFail($validated['store_id']);
            // Fetch the gateway details for the store (use default if no gateway_option specified)
            $gateway = PaymentGateway::where('gateway_code', $validated['gateway_option'])
                ->where('currency_code', $validated['currency_code'])
                ->where('status', 'active')
                ->firstOrFail();
            // payment gateway settings 
            $passCharges = $gateway->pass_charges;
            $gatewayPercent = $gateway->percent;
            $surcharge = $gateway->surcharge;
            $cappedAt = $gateway->capped_at;
            // store settings.
            $applyVAT = $store->apply_vat;
            $vatPercent = $store->vat_percent;
            $applyServiceCharge = $store->apply_service_charge;
            $serviceCharge = $store->service_charge;
            $serviceChargeType = $store->service_charge_type;

            // Fetch all products in one query based on product IDs
            $productIds = collect($validated['items'])->pluck('product_id')->all();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $products->map(function ($product) use ($regionCode, $countryCode) {
                // Fetch specific pricing fields for the specified region and country
                $product->pricing = ProductPricing::where('product_id', $product->id)
                    ->where('country_code', $countryCode)
                    ->where('region_code', $regionCode)
                    ->select('current_price', 'sales_price', 'on_sales')
                    ->first() ?? null;

                return $product;
            });

            // Check for missing products and return an error if any product is not found
            if ($products->count() !== count($productIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products in the order are not found.',
                ], 404);
            }

            // Calculate totals and prepare data for the order
            $totalQty = 0;
            $subtotal = 0;
            $totalDiscount = $validated['discount_code'] ? $this->applyDiscount($validated['discount_code']) : 0;
            $loyaltyDiscount = 0;
            $totalVAT = 0;

            // Generate `order_desc` if not provided
            if (empty($validated['order_desc'])) {
                $firstProduct = $products->first();
                $orderDesc = str($firstProduct->title)->limit(40, '...');
                $otherProductCount = count($productIds) - 1;
                $validated['order_desc'] = $otherProductCount > 0 ? "{$orderDesc} (+{$otherProductCount})" : $orderDesc;
            }

            // Order data
            $orderData = [
                'store_id' => $validated['store_id'],
                'reference' => $this->generateUniqueReference(),
                'order_desc' => $validated['order_desc'],
                'discount_code' => $validated['discount_code'],
                'platform' => $validated['platform'],
                'currency_code' => $validated['currency_code'],
                'payment_option' => $validated['payment_option'],
                'delivery_option' => $validated['delivery_option'],
                'customer_id' => $customerId,
                'customer_email' => $user->email,
                'status' => $validated['payment_option'] === 'instant' ? 'staged' : 'new',
                'loyalty_discount' => $loyaltyDiscount,
                'total_paid' => 0,
                'staged' => $validated['payment_option'] === 'instant',
            ];

            // Log::info("Order data before saving: ", $orderData);

            // Create the order
            $order = Order::create($orderData);

            // Process products and calculate totals
            $orderItems = [];
            foreach ($validated['items'] as $item) {
                $product = $products[$item['product_id']];
                $qty = $item['qty'];

                // Determine price based on sales status
                $price = $product->pricing->on_sales ? $product->pricing->sales_price : $product->pricing->current_price;

                // Check inventory if `track_quantity` is true
                if ($product->inventory->track_quantity && $product->inventory->quantity < $qty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient quantity for product {$product->title}.",
                    ], 422);
                }

                $subtotal += $price * $qty;
                $totalQty += $qty;

                // Calculate VAT for this product if not VAT-exempt
                $productVAT = 0;
                if ($applyVAT && !$product->exempt_vat) {
                    $productVAT = $this->calculateVAT($price * $qty, $vatPercent);
                    $totalVAT += $productVAT;
                }
                // Prepare order item data
                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'product_barcode' => $product->barcode,
                    'combination_name' => $product->combination_name ?? null,
                    'product_category_id' => $product->category_id,
                    'price' => $price,
                    'qty' => $qty,
                    'vat' => $productVAT,
                    'on_sales' => $product->pricing->on_sales,
                    'vat_exempted' => $product->exempt_vat,
                ];

                // Update inventory manually if `track_quantity` is true and `payment_option` is 'pod'
                if ($product->inventory->track_quantity && $validated['payment_option'] === 'pod') {
                    $newQuantity = $product->inventory->quantity - $qty;

                    // Log new quantity for transparency
                    // Log::info("Updating inventory for product ID {$product->id}: New quantity is {$newQuantity}");

                    $product->inventory->update(['quantity' => $newQuantity]);
                }
            }

            // Insert all order items at once
            DB::table('order_items')->insert($orderItems);

            // Calculate service charge
            $serviceChargeAmount = $applyServiceCharge
                ? ($serviceChargeType === 'fixed' ? $serviceCharge : $subtotal * ($serviceCharge / 100))
                : 0;

            // Calculate gateway fee if pass_charges is true
            $gatewayFee = 0;
            if ($passCharges && $validated['payment_option'] === 'instant') {
                $calculatedGatewayFee = ($subtotal * ($gatewayPercent / 100)) + $surcharge;
                $gatewayFee = $cappedAt && $calculatedGatewayFee > $cappedAt ? $cappedAt : $calculatedGatewayFee;
            }

            // Finalize order totals
            $total = $subtotal - $totalDiscount + $totalVAT + $serviceChargeAmount + $gatewayFee;

            // Finalize totals on the order
            $order->total_qty = $totalQty;
            $order->subtotal = $subtotal;
            $order->total = $total;
            $order->total_discount = $totalDiscount;
            $order->loyalty_discount = $loyaltyDiscount;
            $order->service_charge = $serviceChargeAmount;
            $order->gateway_fee = $gatewayFee;
            $order->vat = $totalVAT;
            $order->balance = $order->total - $order->total_paid; // Calculate balance
            $order->save();

            // Create an online payment entry here of the total amount of the order
            // if the payment option is instant.
            // We will return the object as 'payment'
            // Check for instant payment option and create a corresponding online payment entry
            $payment = null;
            $paymentReference = $this->generateUniqueReference('PAY');
            if ($validated['payment_option'] === 'instant') {
                $payment = OnlinePayment::create([
                    'reference' => $paymentReference,
                    'gateway_code' => $validated['gateway_option'],
                    'customer_id' => $user->id,
                    'customer_email' => $user->email,
                    'initiated' => now(),
                    'amount' => $order->total,
                    'currency_code' => $validated['currency_code'],
                    'status' => 'pending',
                    'order_id' => $order->id,
                    'store_id' => $validated['store_id'],
                ]);
            }

            DB::commit();

            $paymentLink = null;
            // LOG::info("Payment Link: " . $generate_link);
            if ($generate_link) {
                $paymentLink = $this->generatePaymentLink($order, $gateway, $paymentReference);
                if (!$paymentLink) {
                    $payment->update(['status' => 'failed']);
                    return response()->json(['success' => false, 'message' => 'Failed to generate payment link.'], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order,
                'payment' => $payment ? array_merge($payment->only([
                    'reference',
                    'gateway_code',
                    'customer_id',
                    'customer_email',
                    'initiated',
                    'amount',
                    'currency_code',
                ]), ['payment_link' => $paymentLink]) : null,  // Handle null payment case
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order creation failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order for the authenticated customer.
     */
    public function show($id)
    {
        $customerId = Auth::id();
        // Retrieve the order with its items for the authenticated customer
        $order = Order::where('customer_id', $customerId)
            ->with(['items'])
            ->findOrFail($id);
        // Extract product IDs from the items in the order
        $productIds = $order->items->pluck('product_id')->unique();
        // Generate dummy status logs
        $statusLogs = $this->generateDummyStatusLogs($order->id);
        //
        return response()->json([
            'success' => true,
            'order' => $order,
            'status_logs' => $statusLogs,
        ]);
    }

    /**
     * Generate randomized dummy status logs for testing purposes.
     * This function should be deleted in production!
     */
    private function generateDummyStatusLogs($orderId)
    {
        // Define a set of status options for the logs
        $statuses = ['pending', 'approved', 'shipped', 'delivered', 'canceled'];
        // Generate 5 to 10 random logs
        $logCount = rand(5, 10);
        $logs = [];

        for ($i = 0; $i < $logCount; $i++) {
            $logs[] = [
                'old_status' => $statuses[array_rand($statuses)],
                'new_status' => $statuses[array_rand($statuses)],
                'changed_at' => now()->subDays(rand(1, 30))->toDateTimeString(), // Random past date
            ];
        }

        return $logs;
    }

    /**
     * Initialize payment and get a payment link for various gateways.
     */
    public function initializePayment(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'reference' => 'required|string',
        ]);
        //
        $orderReference = $request->input('reference');
        $generateLink = $request->input('generate_link') ?? false;
        $order = Order::where('reference', $orderReference)->firstOrFail();
        // Fetch the active payment gateway for the specified currency or use default
        $gateway = PaymentGateway::where('currency_code', $order->currency_code)
            ->orWhere('is_default', true)
            ->where('status', 'active')
            ->first();
        //
        if (!$gateway) {
            return response()->json(['success' => false, 'message' => 'No active payment gateway found.'], 400);
        }
        // Generate a unique payment reference and create the OnlinePayment entry
        $paymentReference =  $this->generateUniqueReference('PAY');
        $onlinePayment = OnlinePayment::create([
            'reference' => $paymentReference,
            'gateway_code' => $gateway->gateway_code,
            'order_id' => $order->id,
            'store_id' => $order->store_id,
            'amount' => $order->balance,
            'currency_code' => $order->currency_code,
            'status' => 'pending',
            'initiated' => now(),
            'customer_id' => $order->customer_id,
            'customer_email' => $order->customer_email,
        ]);
        //
        $paymentLink = null;
        //
        if ($generateLink) {
            $paymentLink = $this->generatePaymentLink($order, $gateway, $paymentReference);
            if (!$paymentLink) {
                $onlinePayment->update(['status' => 'failed']);
                return response()->json(['success' => false, 'message' => 'Failed to generate payment link.'], 500);
            }
        }
        //
        return response()->json([
            'success' => true,
            'reference' => $paymentReference,
            'amount' => $order->balance,
            'gateway' => $paymentReference,
            'currency_code' => $order->currency_code,
            'message' => 'Payment initialized successfully.',
            'payment_link' => $paymentLink,
        ]);
    }

    /**
     * Verify payment status for different gateways.
     */
    public function verifyPayment(Request $request)
    {
        // Validate the request input
        $request->validate([
            'reference' => 'required|string',
        ]);
        //
        $reference = $request->input('reference');
        // Retrieve the online payment using the reference
        $payment = OnlinePayment::where('reference', $reference)->first();
        // 
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment entry not found.'], 404);
        }
        // Fetch the gateway associated with this payment
        $gateway = PaymentGateway::where('gateway_code', $payment->gateway_code)->where('status', 'active')->first();
        if (!$gateway) {
            return response()->json(['success' => false, 'message' => 'Payment gateway is inactive or not found.'], 400);
        }
        // 
        switch ($gateway->gateway_code) {
            case 'paystack':
                return $this->verifyPaystackPayment($payment, $gateway);

                // Other gateways can be added here as cases
                // case 'flutterwave':
                //     return $this->verifyFlutterwavePayment($payment, $gateway);

            default:
                return response()->json(['success' => false, 'message' => 'Unsupported payment gateway.'], 400);
        }
    }

    /**
     * Verify Paystack payment.
     */
    private function verifyPaystackPayment(OnlinePayment $payment, PaymentGateway $gateway)
    {
        $secretKey = app()->environment('production') ? $gateway->live_secret_key : $gateway->test_secret_key;

        // Make the request to Paystack API to verify the transaction
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$secretKey}",
        ])->get("https://api.paystack.co/transaction/verify/{$payment->reference}");

        // Check if the request was successful
        if ($response->failed()) {
            return response()->json(['success' => false, 'message' => 'Failed to verify payment.'], 500);
        }

        $responseData = $response->json();
        // Check Paystack's response for different payment statuses
        if ($responseData['status']) {
            $paystackStatus = $responseData['data']['status'];
            $amountPaid = $responseData['data']['amount'] / 100; // Convert from kobo to Naira (or primary currency)

            switch ($paystackStatus) {
                case 'success':
                    // Fetch the order associated with the payment
                    $order = Order::find($payment->order_id);
                    if (!$order) {
                        return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
                    }
                    // Update the payment and order status to completed
                    $order->update([
                        'status' => 'new',
                        'total_paid' => $amountPaid,
                        'balance' => 0,
                        'staged' => false,
                        'updated_at' => now(),
                    ]);
                    $payment->update([
                        'status' => 'completed',
                        'verified' => true,
                        'last_verified' => now(),
                        'updated_at' => now(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment verified successfully.',
                        'order' => $order,
                        'payment' => $payment->only([
                            'reference',
                            'gateway_code',
                            'customer_id',
                            'customer_email',
                            'initiated',
                            'amount',
                            'currency_code',
                        ]),
                    ], 200);

                case 'failed':
                    $payment->update([
                        'status' => 'failed',
                        'last_verified' => now(),
                    ]);
                    return response()->json(['success' => false, 'message' => 'Payment failed.'], 400);

                case 'abandoned':
                    $payment->update([
                        'status' => 'abandoned',
                        'last_verified' => now(),
                    ]);
                    return response()->json(['success' => false, 'message' => 'Payment was abandoned.'], 400);

                default:
                    // Handle any other unexpected statuses
                    $payment->update([
                        'status' => 'unknown',
                        'last_verified' => now(),
                    ]);
                    return response()->json(['success' => false, 'message' => 'Unknown payment status.'], 400);
            }
        }
        return response()->json(['success' => false, 'message' => 'Payment verification failed or payment not completed.'], 400);
    }

    private function generatePaymentLink(Order $order, PaymentGateway $gateway, string $paymentReference): ?string
    {
        // Determine which key to use based on the environment
        $secretKey = app()->environment('production') ? $gateway->live_secret_key : $gateway->test_secret_key;

        // Convert the amount to minor units if required (e.g., kobo for NGN)
        $amount = $order->balance * 100;

        // Prepare payload for the gateway
        $payload = [
            'email' => $order->customer_email,
            'amount' => $amount,
            'reference' => $paymentReference,
            'callback_url' => route('customer.payments.verify'),
        ];

        // Initialize the payment based on the gateway code
        $response = match ($gateway->gateway_code) {
            'paystack' => Http::withHeaders(['Authorization' => "Bearer {$secretKey}"])
                ->post('https://api.paystack.co/transaction/initialize', $payload),
            'flutterwave' => Http::withHeaders(['Authorization' => "Bearer {$secretKey}"])
                ->post('https://api.flutterwave.com/v3/payments', array_merge($payload, [
                    'tx_ref' => $paymentReference,
                    'redirect_url' => route('customer.payments.verify'),
                ])),
                // Add additional gateway cases here
            default => null,
        };

        // Handle failed response
        if (!$response || $response->failed()) {
            Log::error("Failed to initialize payment with {$gateway->gateway_code}.", ['response' => $response?->body()]);
            return null; // You can return null here to signify failure, or handle differently if required
        }

        $responseData = $response->json();
        return $responseData['data']['authorization_url'] ?? $responseData['data']['link'] ?? null;
    }


    /**
     * Helper method to generate a unique reference for an order.
     */
    private function generateUniqueReference($prefix = 'ORD')
    {
        return $prefix . '-' . strtoupper(uniqid());
    }

    /**
     * Helper method to calculate VAT.
     */
    private function calculateVAT($amount, $vatPercent)
    {
        return $amount * $vatPercent;
    }

    /**
     * Helper method to apply a discount based on the discount code.
     */
    private function applyDiscount($discountCode)
    {
        // Logic to calculate discount based on code
        return 0; // Placeholder value for discount
    }
}
