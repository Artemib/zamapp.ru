<?php

use Illuminate\Support\Facades\Route;
use App\MoonShine\Pages\OrderMergePage;
use App\MoonShine\Pages\OrderMergePageV2;

Route::get('/', function () {
    return view('welcome');
});

// Маршрут для слияния заказов
Route::post('/admin/page/order-merge', [OrderMergePage::class, 'merge_orders'])
    ->middleware(['web', 'moonshine'])
    ->name('orders.merge');

// Маршрут для получения данных заказов
Route::post('/admin/page/order-merge/get-orders-data', [OrderMergePage::class, 'getOrdersData'])
    ->middleware(['web', 'moonshine'])
    ->name('orders.get-data');

// Маршрут для получения списка заказов
Route::post('/admin/page/order-merge/get-orders-list', [OrderMergePage::class, 'getOrdersList'])
    ->middleware(['web', 'moonshine'])
    ->name('orders.get-list');

// Маршрут для слияния заказов V2
Route::post('/admin/page/order-merge-v2', [OrderMergePageV2::class, 'merge_orders'])
    ->middleware(['web', 'moonshine'])
    ->name('orders.merge.v2');
