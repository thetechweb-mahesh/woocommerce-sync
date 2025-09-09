<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
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
        ->post("{$this->apiUrl}/products", [
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
        // // 👇 Log the actual error returned by WooCommerce
        // Log::error('WooCommerce Sync Failed', [
        //     'body' => $response->body(),
        //     'status' => $response->status(),
        // ]);

        $product->status = 'sync_failed';
    }

    $product->save();
}
public function update(Product $product)
{
    $url = rtrim($this->apiUrl, '/') . "/products/{$product->wc_product_id}";

    $response = Http::withBasicAuth($this->key, $this->secret)
        ->put($url, [
            'name'        => $product->name,
            'regular_price' => (string) $product->price,
            'description' => $product->description,
            'images'      => [['src' => $product->image_url]],
        ]);

    if ($response->successful()) {
        $product->status = 'updated';
    } else {
        \Log::error('WooCommerce Update Failed', [
            'wc_product_id' => $product->wc_product_id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        $product->status = 'update_failed';
    }

    $product->save();

    return $response->json();
}


public function delete(Product $product)
    {
        $response = Http::withBasicAuth($this->key, $this->secret)
            ->delete("{$this->apiUrl}/wp-json/wc/v3/products/{$product->wc_product_id}");

        $product->status = $response->successful() ? 'deleted' : 'delete_failed';
        $product->save();
    }
}
