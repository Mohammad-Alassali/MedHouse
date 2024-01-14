<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
//Auth User Route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify', [AuthController::class, 'verifyingRegister']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget/password', [AuthController::class, 'forgetPassword']);
Route::post('/reset/password', [AuthController::class, 'resetPassword']);
Route::post('/verify/password', [AuthController::class, 'verifyForgetPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
//Admin Auth Route
Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);
});


//Admin Route
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/users/num', [AdminController::class, 'getNumberOfUser']);
    Route::get('/admins/num', [AdminController::class, 'getNumberOfAdmin']);
    Route::get('/today', [CartController::class, 'getSalesToday']);
    Route::get('/week', [CartController::class, 'getSalesLastWeek']);
    Route::get('/month', [CartController::class, 'getSalesThisMonth']);
    Route::prefix('admin')->group(function () {
        Route::get('', [AdminController::class, 'getAllAdmins']);
        Route::post('/add', [AdminController::class, 'addNewAdmin'])
            ->middleware('permission:add_new_admin');
        Route::post('/delete', [AdminController::class, 'deleteAdmin'])
            ->middleware('super_admin');
        Route::get('/permissions', [AdminController::class, 'getPermissions']);
        Route::get('/is_super_admin', [AdminController::class, 'isSuperAdmin']);
    });
});
Route::prefix('permission')
    ->middleware(['auth:api', 'super_admin'])
    ->group(function () {
        Route::post('/add', [AdminController::class, 'addPermission']);
        Route::post('/delete', [AdminController::class, 'deletePermission']);
    });

//User Route

Route::middleware('auth:api')->group(function () {
    Route::get('/latest/product', [ProductController::class, 'latestProduct']);
    Route::prefix('/edit')->group(function () {
        Route::post('/password', [UserController::class, 'editPassword']);
        Route::post('/name', [UserController::class, 'editName']);
        Route::post('/photo', [UserController::class, 'editPhoto']);
        Route::post('/phone', [UserController::class, 'editPhone']);
    });
    Route::post('/verify/phone', [UserController::class, 'verifyChangeNumber']);
    Route::get('/show/user', [UserController::class, 'showUser']);
    Route::get('/notifications', [UserController::class, 'showNotifications']);
    Route::post('save-notification-token', [UserController::class, 'saveNotificationToken']);
});


Route::middleware('auth:api')->group(function () {
// Product Route
    Route::prefix('/products')->group(function () {
        Route::post('', [ProductController::class, 'filteredProduct']);


        Route::get('/sort/sales', [ProductController::class, 'bySales']);
        Route::get('/otc', [ProductController::class, 'getOTC']);
        Route::post('/create', [ProductController::class, 'store'])
            ->middleware('permission:add_product');
        Route::post('/{product}', [ProductController::class, 'update'])
            ->middleware('permission:update_product');
        Route::get('/{product}', [ProductController::class, 'show']);// here you should pass a parameter true if you entered from search
        Route::delete('/{product}', [ProductController::class, 'destroy'])
            ->middleware('permission:delete_product');
    });
    // Search Route
    Route::prefix('/search')->group(function () {
        Route::get('/delete/{product}', [ProductController::class, 'destroyLatestSearch']);
        Route::get('/products', [ProductController::class, 'latestSearches']);
    });
    //Favorite Route
    Route::prefix('/favorites')->group(function () {
        Route::get('', [ProductController::class, 'favoriteProduct']);
        Route::get('/add/{product}', [ProductController::class, 'addToFavorite']);
        Route::get('/remove/{product}', [ProductController::class, 'removeFromFavorite']);
    });

    // Language Route
    Route::get('/language', LanguageController::class);

//    Cart Route
    Route::prefix('/carts')->group(function () {
        Route::get('', [CartController::class, 'index']);
        Route::get('/all-carts', [CartController::class, 'allCarts'])
            ->middleware('admin');
        Route::post('/create', [CartController::class, 'store']);
        Route::post('/reorder', [CartController::class, 'store']);
        Route::delete('/{cart}', [CartController::class, 'destroy']);
        Route::delete('/cancel/{cart}', [CartController::class, 'cancelOrder'])
            ->middleware('permission:change_status_of_request');
        Route::delete('/order/{order}', [OrderController::class, 'destroy']);
        Route::put('/next/{cart}', [CartController::class, 'nextStatus'])
            ->middleware('permission:change_status_of_request');
        Route::put('/paid/{cart}', [CartController::class, 'setAsPaid'])
            ->middleware('permission:change_status_of_request');
        Route::get('order/{cart}', [CartController::class, 'getOrdersOnCart'])
            ->middleware('admin');
    });

//    Report Route
    Route::post('/user-report', [CartController::class, 'userReport']);
    Route::post('/admin-report', [CartController::class, 'adminReport'])->middleware(['admin']);

//Company Route
    Route::prefix('/companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store'])
            ->middleware('permission:add_company');
        Route::get('/{id}', [CompanyController::class, 'show']);
        Route::put('/{id}', [CompanyController::class, 'update'])
            ->middleware('permission:update_company');
        Route::delete('/{id}', [CompanyController::class, 'delete'])
            ->middleware('permission:delete_company');
    });
    Route::get('/classifications', ClassificationController::class);
});

// Get Images Route
Route::get('/image', [ImageController::class, 'getImage']);


