<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Http; 

class ProductController extends Controller
{
   public function index()
    {
        // response()->json(Product::all());
       $products= Product::all();
       return view('products/index', compact('products'));
    }

    public function store(Request $request, WooCommerceService $woo)
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image_url' => 'required|url',
        ]);

        $product = Product::create([
            'user_id' => $user->id, 
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'image_url' => $validated['image_url'],
            'status' => 'Created',
        ]);

        $woo->sync($product);

        return response()->json([
            'message' => 'Product created and synced',
            'product' => $product
        ]);
    }

    //update function   
    // Update
public function update(Request $request, Product $product, \App\Services\WooCommerceService $wooService)
{
    $this->authorize('update', $product);

    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'price' => 'required|numeric',
        'image_url' => 'nullable|url',
    ]);

    // update in local DB
    $product->update($request->only('name', 'description', 'price', 'image_url'));

    // sync with WooCommerce
    $wooService->update($product);

    return response()->json($product);
}

    //  public function update(Request $request, Product $product)
    // {
        // \Log::info('User:', ['user' => auth()->user()]);
        // \Log::info('Product:', ['product' => $product]);

        // $this->authorize('update', $product);

        // $request->validate([
            // 'name' => 'required',
            // 'description' => 'required',
            // 'price' => 'required|numeric',
            // 'image_url' => 'nullable|url',
        // ]);

        // $product->update($request->only('name', 'description', 'price', 'image_url'));

        // try {
            // $woocommerce = Http::withBasicAuth(
                // config('services.woocommerce.consumer_key'),
                // config('services.woocommerce.consumer_secret')
            // )->put(config('services.woocommerce.api_url') . '/wp-json/wc/v3/products/' . $product->wc_product_id, [
                // 'name' => $product->name,
                // 'description' => $product->description,
                // 'regular_price' => (string) $product->price,
                // 'images' => [['src' => $product->image_url]],
            // ]);

            // $product->status = 'updated';
            // $product->save();
        // } catch (\Exception $e) {
            // \Log::error('WooCommerce Update Failed', ['error' => $e->getMessage()]);
            // $product->status = 'update_failed';
            // $product->save();
        // }

    //   return response()->json($product);
    // }

// product delete function
 public function destroy(Product $product)
    {
        Log::info('User ID from request', ['id' => optional(auth()->user())->id]);
        
        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }


}
