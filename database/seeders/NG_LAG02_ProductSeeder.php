<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Utils\ProductUtility;
use Illuminate\Database\Seeder;

class NG_LAG02_ProductSeeder extends Seeder
{
    const REGION_CODE = 'LAG02';
    const COUNTRY_CODE = 'NG';

    public function run()
    {
        // Find the store by code '002' or the appropriate code for this region
        $store = Store::where('code', '002')->firstOrFail();

        // Product data specific to this region
        $productsData = $this->generateProductsData();

        // Seed each product using ProductUtility
        foreach ($productsData as $data) {
            ProductUtility::createProduct($store, $data, self::REGION_CODE, self::COUNTRY_CODE);
        }
    }

    private function generateProductsData()
    {
        return [
            [
                'title' => 'Green Peppers',
                'description' => 'Fresh and crisp green peppers.',
                'price' => 300,
                'weight' => 0.3,
                'qty' => 80,
                'track_quantity' => true,
                'reorder_point' => 5,
            ],
            [
                'title' => 'Cashew Nuts',
                'description' => 'Raw and roasted cashew nuts.',
                'price' => 1500,
                'weight' => 1.0,
                'qty' => 40,
            ],
            // Additional products...
        ];
    }
}
