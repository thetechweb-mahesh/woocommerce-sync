<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Route::get('/test-sync', function () {
//     $response = Http::withBasicAuth(
//         'ck_182c717667e588c46169a19dd4d47aa1ad48d48a',
//         'cs_1b0b28940790019a7bc57a1a09f1cff83205a23b'
//     )->post('http://localhost/fitnessnstyle/wp-json/wc/v3/products', [
//         'name' => 'Laravel Test Product',
//         'type' => 'simple',
//         'regular_price' => '9.99',
//         'description' => 'Testing sync from browser',
//     ]);

//     return $response->json();
// });


Route::get('/', function () {
    return view('welcome');
});
Route::get('/products', [ProductController::class, 'index']);