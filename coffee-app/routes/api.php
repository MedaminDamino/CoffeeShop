<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, UserController,
    BranchController, TableController, CategoryController, ProductController,
    ReservationController, OrderController,
    PromotionController, PromotionUsageController
};

Route::apiResource('branches', BranchController::class);
Route::apiResource('tables', TableController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('reservations', ReservationController::class)->except(['update']);
Route::apiResource('promotions', PromotionController::class);
Route::apiResource('promotion-usages', PromotionUsageController::class)->only(['store']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,10');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerification'])->middleware('throttle:3,10');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/feedback', [UserController::class, 'submitFeedback']);
    Route::apiResource('users', UserController::class)->only(['index', 'store']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    Route::get('/docs', function () {
        return response()->file(storage_path('api-docs/api-docs.json'));
    });

    // Orders require authentication
    Route::apiResource('orders', OrderController::class);
    Route::post('/orders/add-item', [OrderController::class, 'addItem']);
});
?>