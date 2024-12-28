<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/login', function() {
//     // echo "hi";
//     return response()->json([
//         'success' => true,
//         'message' => 'Hi this is login page'
//     ]);
// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware(['verify.token'])->group(function () {
    Route::get('/getCategories', [CategoryController::class, 'getCategories']);
    Route::post('/addCategory', [CategoryController::class, 'addCategory']);
    Route::post('/addProduct', [ProductController::class, 'addProduct']);
});
