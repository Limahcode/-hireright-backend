<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    
    public function index(Request $request)
    {
        // Fetch query parameters for filtering, sorting, and pagination
        $size = $request->query('size', 10); // Default page size
        $page = $request->query('page', 1); // Default current page
        $sortBy = $request->query('sortBy', 'created_at'); // Default sort by created_at
        $sortDir = $request->query('sortDir', 'desc'); // Default sort direction (descending)
        $status = $request->query('status'); // Filter by status (optional)
        // Start the query
        $query = Product::select('id', 'title', 'slug', 'price', 'qty', 'status', 'created_at');
        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }
        // Apply sorting
        if (in_array($sortBy, ['id', 'title', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }
        // Paginate the results
        $products = $query->paginate($size, ['*'], 'page', $page);
        // Return paginated data with metadata
        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total_pages' => $products->lastPage(),
                'total_items' => $products->total(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'next' => $products->nextPageUrl(),
                'prev' => $products->previousPageUrl(),
            ],
        ], 200);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->query('search'); // The search term(s)

        if (!$searchTerm) {
            return response()->json(['error' => 'Search term is required'], 400);
        }
        // Start the query and search in title and description
        $query = Product::select('id', 'title', 'slug', 'price', 'qty', 'status', 'description')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%$searchTerm%")
                    ->orWhere('description', 'LIKE', "%$searchTerm%");
            });
        // Paginate the results
        $products = $query->paginate(10); // Default page size for search results
        // Return paginated search results
        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total_pages' => $products->lastPage(),
                'total_items' => $products->total(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'next' => $products->nextPageUrl(),
                'prev' => $products->previousPageUrl(),
            ],
        ], 200);
    }

    /**
     * Store a newly created product with pricing and inventory.
     */
    public function store(Request $request)
    {
        // Get authenticated user
        $user = Auth::user();
        // Check if the user has a store
        if (!$user->store_id) {
            return response()->json(['error' => 'User does not have a store.'], 403);
        }
        // Validate product creation request, pricing, and inventory
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:product_categories,id',
            'sub_category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
            // Pricing validation
            'pricing.current_price' => 'required|numeric|min:0',
            'pricing.sales_price' => 'nullable|numeric|min:0',
            'pricing.on_sales' => 'nullable|boolean',
            'pricing.supplier_price' => 'nullable|numeric|min:0',
            'pricing.markup' => 'nullable|numeric|min:0',
            // Inventory validation
            'inventory.sku' => 'required|string|max:100',
            'inventory.barcode' => 'nullable|string|max:100',
            'inventory.quantity' => 'required|integer|min:0',
            'inventory.track_quantity' => 'nullable|boolean',
            'inventory.reorder_point' => 'nullable|integer|min:0',
        ]);

        // Return validation errors, if any
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create the product associated with the store (using user's store_id)
        $product = new Product($request->only([
            'title',
            'slug',
            'description',
            'status',
            'category_id'
        ]));
        $product->store_id = $user->store_id; 
        $product->save();

        // Process and validate Pricing data
        $pricingData = $request->input('pricing');
        $pricing = new ProductPricing([
            'current_price' => $pricingData['current_price'],
            'sales_price' => $pricingData['sales_price'] ?? 0,
            'on_sales' => $pricingData['on_sales'] ?? false,
            'supplier_price' => $pricingData['supplier_price'] ?? 0,
            'markup' => $pricingData['markup'] ?? 0,
        ]);
        $product->pricing()->save($pricing);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('pricing', 'inventory')
        ], 201);
    }


    /**
     * Display the specified product with pricing and inventory.
     */
    public function show($id)
    {
        $product = Product::with(['pricing', 'inventory', 'category'])->find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    /**
     * Update the specified product with its pricing and inventory.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:products,slug,' . $id,
            'category_id' => 'sometimes|exists:product_categories,id',
            'pricing.current_price' => 'sometimes|numeric|min:0',
            'inventory.quantity' => 'sometimes|integer|min:0',
            // Add more validation rules as needed
        ]);
        // 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // Update Product
        $product->update($request->only('title', 'slug', 'status', 'category_id'));
        // Update Pricing
        if ($request->has('pricing')) {
            $pricingData = $request->input('pricing');
            $product->pricing()->updateOrCreate([], $pricingData);
        }
        // Update Inventory
        if ($request->has('inventory')) {
            $inventoryData = $request->input('inventory');
            $product->inventory()->updateOrCreate([], $inventoryData);
        }
        return response()->json(['message' => 'Product updated successfully', 'product' => $product->load('pricing', 'inventory')], 200);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        //
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        //
        $product->delete();
        //
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
