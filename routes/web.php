<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReturnLogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin & User
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('items', ItemController::class)->only('index');
    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store']);
});

// Khusus Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('items', ItemController::class)->except(['index', 'show']);
    Route::get('/loans/{loan}/return', [ReturnLogController::class, 'create'])->name('returns.create');
    Route::post('/loans/{loan}/return', [ReturnLogController::class, 'store'])->name('returns.store');
});

require __DIR__.'/auth.php';