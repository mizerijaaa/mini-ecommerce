<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('market.index');
});

Volt::route('/marketplace', 'market.index')->name('market.index');
Volt::route('/cart', 'cart.index')->middleware(['auth', 'buyer'])->name('cart.index');
Volt::route('/checkout', 'checkout.index')->middleware(['auth', 'buyer'])->name('checkout.index');
Volt::route('/buyer/orders', 'buyer.orders.index')->middleware(['auth', 'buyer'])->name('buyer.orders.index');
Volt::route('/buyer/orders/{orderId}', 'buyer.orders.show')->middleware(['auth', 'buyer'])->name('buyer.orders.show');
Volt::route('/vendor/orders', 'vendor.orders.index')->middleware(['auth', 'vendor'])->name('vendor.orders.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
