<?php

use App\Modules\Tdv\Controllers\CheckinController;
use Illuminate\Support\Facades\Route;
use App\Modules\Tdv\Controllers\DashboardController;
use App\Modules\Tdv\Controllers\StoreController;
use App\Modules\Tdv\Controllers\StoreChangeController;
use App\Modules\Tdv\Controllers\NewStoreController;
use App\Modules\Tdv\Controllers\PosterStoreRegisterController;

Route::as('admin.tdv.')->prefix('tdv')->middleware(['web', 'auth', 'tdv'])->group(function () {
    Route::get('/', [DashboardController::class, 'Index'])->name('dashboard');

    Route::get('/store', [StoreController::class, 'index'])->name('store.index');
    Route::get('/store/create/', [StoreController::class, 'create'])->name('store.create');
    Route::post('/store/store', [StoreController::class, 'store'])->name('store.store');
    Route::get('/store/{id}', [StoreController::class, 'show'])->name('store.show');
    Route::get('/store/{id}/edit/', [StoreController::class, 'edit'])->name('store.edit');
    Route::post('/store/update/{id}', [StoreController::class, 'update'])->name('store.update');
    Route::get('/store/{storeId}/turnover', [StoreController::class, 'turnover'])->name('store.turnover');
    Route::get('/store-changes', [StoreChangeController::class, 'index'])->name('store-changes.index');
    Route::get('/store-changes/{id}', [StoreChangeController::class, 'show'])->name('store-changes.show');
    Route::get('/store-changes/{id}/edit/', [StoreChangeController::class, 'edit'])->name('store-changes.edit');
    Route::post('/store-changes/update/{id}', [StoreChangeController::class, 'update'])->name('store-changes.update');
    Route::get('/new-stores', [NewStoreController::class, 'index'])->name('new-stores.index');
    Route::get('/new-stores/{id}', [NewStoreController::class, 'show'])->name('new-stores.show');
    Route::get('/new-stores/{id}/edit', [NewStoreController::class, 'edit'])->name('new-stores.edit');
    Route::post('/new-stores/update/{id}', [NewStoreController::class, 'update'])->name('new-stores.update');
    //Poster
    Route::get('/register-poster-index/{id}', [PosterStoreRegisterController::class, 'index'])->name('register-poster.index');
    Route::get('/register-poster/{store_id}/{poster_id}', [PosterStoreRegisterController::class, 'create'])->name('register-store-poster.create');
    Route::post('/store-register-poster', [PosterStoreRegisterController::class, 'store'])->name('register-store-poster.store');
    Route::get('/register-poster-list', [PosterStoreRegisterController::class, 'list'])->name('register-poster.list');
    Route::get('/image-store-poster/{store_poster_id}', [PosterStoreRegisterController::class, 'image_store'])->name('image-store-poster.create');
    Route::post('/post-image-register-poster/{store_poster_id}', [PosterStoreRegisterController::class, 'post_image_store'])->name('image-store-poster.post');

    Route::get('/show-images/{id}/{type}', [PosterStoreRegisterController::class, 'showImages'])->name('show-images');
    Route::get('/register-poster', [PosterStoreRegisterController::class, 'regPoster'])->name('reg-poster');

    Route::get('/register-poster/{poster_id}', [PosterStoreRegisterController::class, 'createByPoster'])->name('register-store-poster.create-by-poster');

    Route::post('ajax-poster-product', [PosterStoreRegisterController::class, 'getPosterByProduct'])->name('ajax-poster-product');

    Route::get('/image-acceptance-store/{store_poster_id}', [PosterStoreRegisterController::class, 'image_acceptance_store'])->name('image-acceptance-store.create');
    Route::post('/post-image-acceptance-store/{store_poster_id}', [PosterStoreRegisterController::class, 'post_image_acceptance_store'])->name('image-acceptance-store.post');
    Route::get('/offer-acceptance/{store_poster_id}', [PosterStoreRegisterController::class, 'offer_acceptance'])->name('offer-acceptance.create');
    Route::post('/post-offer-acceptance/{store_poster_id}', [PosterStoreRegisterController::class, 'post_offer_acceptance'])->name('offer-acceptance.post');

    Route::get('/checkin-store', [CheckinController::class, 'index'])->name('checkin.list');
    Route::post('/get-list-store', [CheckinController::class, 'getListStore'])->name('checkin.get-list-store');
    Route::post('/checkin', [CheckinController::class, 'checkin'])->name('checkin.checkin');
    Route::post('/checkout', [CheckinController::class, 'checkout'])->name('checkin.checkout');
    Route::get('/checkin/histories', [CheckinController::class, 'history'])->name('checkin.histories');
    Route::post('/checkin/request-forget-checkout', [CheckinController::class, 'forgetCheckout'])->name('checkin.forgetCheckout');
});
