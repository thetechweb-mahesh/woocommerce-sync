<?php

// namespace App\Services;

// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Http;
// use App\Models\Product;

// class WooCommerceService
// {
//      protected $apiUrl;
//     protected $consumerKey;
//     protected $consumerSecret;

//     public function __construct()
//     {
//         // $this->ck = config('services.woocommerce.ck');
//         // $this->cs = config('services.woocommerce.cs');
//         // $this->baseUrl = config('services.woocommerce.url');
//         $this->apiUrl = config('services.woocommerce.api_url');
//         $this->consumerKey = config('services.woocommerce.consumer_key');
//         $this->consumerSecret = config('services.woocommerce.consumer_secret');
//     }

//     public function sync($product)
//     {
//         try {
//             $response = Http::withBasicAuth($this->ck, $this->cs)
//                 ->post("{$this->baseUrl}/wp-json/wc/v3/products", [
//                     'name' => $product->name,
//                     'type' => 'simple',
//                     'regular_price' => (string) $product->price,
//                     'description' => $product->description,
//                     'images' => [['src' => $product->image_url]]
//                 ]);

//             if ($response->successful()) {
//                 $product->update([
//                     'wc_product_id' => $response['id'],
//                     'status' => 'Synced'
//                 ]);
//             } else {
//                 $product->update(['status' => 'Failed']);
//             }
//         } catch (\Exception $e) {
//             $product->update(['status' => 'Failed']);
//         }
//     }



//     public function update(Product $product)
// {
//     try {
//         retry(3, function () use ($product) {
//             Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
//                 ->put("{$this->apiUrl}/wp-json/wc/v3/products/{$product->wc_product_id}", [
//                     'name' => $product->name,
//                     'description' => $product->description,
//                     'regular_price' => (string) $product->price,
//                     'images' => [['src' => $product->image_url]],
//                 ]);
//         }, 100);

//         $product->status = 'updated';
//         $product->save();
//     } catch (\Exception $e) {
//         Log::error('WooCommerce Update Failed', ['error' => $e->getMessage()]);
//         $product->status = 'update_failed';
//         $product->save();
//     }
// }

// public function delete(Product $product)
// {
//     try {
//         retry(3, function () use ($product) {
//             Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
//                 ->delete("{$this->apiUrl}/wp-json/wc/v3/products/{$product->wc_product_id}");
//         }, 100);

//         $product->status = 'deleted';
//         $product->save();
//     } catch (\Exception $e) {
//         Log::error('WooCommerce Delete Failed', ['error' => $e->getMessage()]);
//         $product->status = 'delete_failed';
//         $product->save();
//     }
// }
// }


namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;

class WooCommerceService
{
    protected $apiUrl;
    protected $key;
    protected $secret;

    public function __construct()
    {
        $this->apiUrl = config('services.woocommerce.api_url');
        $this->key = config('services.woocommerce.consumer_key');
        $this->secret = config('services.woocommerce.consumer_secret');
    }

    public function sync(Product $product)
    {
        $response = Http::withBasicAuth($this->key, $this->secret)
            ->post("{$this->apiUrl}/wp-json/wc/v3/products", [
                'name' => $product->name,
                'type' => 'simple',
                'regular_price' => (string) $product->price,
                'description' => $product->description,
                'images' => [['src' => $product->image_url]],
            ]);

        if ($response->successful()) {
            $product->wc_product_id = $response['id'];
            $product->status = 'synced';
        } else {
            $product->status = 'sync_failed';
        }

        $product->save();
    }

    public function update(Product $product)
    {
        $response = Http::withBasicAuth($this->key, $this->secret)
            ->put("{$this->apiUrl}/wp-json/wc/v3/products/{$product->wc_product_id}", [
                'name' => $product->name,
                'description' => $product->description,
                'regular_price' => (string) $product->price,
                'images' => [['src' => $product->image_url]],
            ]);

        $product->status = $response->successful() ? 'updated' : 'update_failed';
        $product->save();
    }

    public function delete(Product $product)
    {
        $response = Http::withBasicAuth($this->key, $this->secret)
            ->delete("{$this->apiUrl}/wp-json/wc/v3/products/{$product->wc_product_id}");

        $product->status = $response->successful() ? 'deleted' : 'delete_failed';
        $product->save();
    }
}
