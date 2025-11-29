<?php

namespace App\Utils;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Str;

class ProductUtility
{
    /**
     * Creates or updates a product and its associated pricing and inventory.
     *
     * @param Store $store
     * @param array $data
     * @param string $regionCode
     * @param string $countryCode
     * @return Product
     */
    public static function createProduct(Store $store, array $data, string $regionCode, string $countryCode): Product
    {
        // Generate unique slug by concatenating store ID
        $slug = Str::slug($data['title'] ?? 'default-title') . '-' . $store->id;

        // Create or find product
        $product = Product::firstOrCreate(
            ['slug' => $slug],
            [
                'title' => $data['title'] ?? 'Product Title',
                'slug' => $slug,
                'store_id' => $store->id,
                'category_id' => $data['category_id'] ?? null,
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'description' => $data['description'] ?? null,
                'free_delivery' => $data['free_delivery'] ?? false,
                'exempt_vat' => $data['exempt_vat'] ?? false,
                'has_variants' => $data['has_variants'] ?? false,
                'relevant_blog_url' => $data['relevant_blog_url'] ?? null,
                'status' => 'active',
            ]
        );

        return $product;
    }
}
