<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::get('/', function () {
    return Inertia::render('Home', [
        'message' => 'Laravel 12 + Vue + Inertia 🚀'
    ]);
});

Route::get('/', [HomeController::class, 'index']);

require __DIR__.'/auth.php';


// Product CRUD Routes
Route::resource('products', \App\Http\Controllers\ProductController::class);
Route::post('products/bulk-delete', [\App\Http\Controllers\ProductController::class, 'bulkDelete'])->name('products.bulkDelete');
Route::get('products/export', [\App\Http\Controllers\ProductController::class, 'export'])->name('products.export');


// Menu CRUD Routes
Route::resource('menus', \App\Http\Controllers\MenuController::class);
Route::post('menus/bulk-delete', [\App\Http\Controllers\MenuController::class, 'bulkDelete'])->name('menus.bulkDelete');
Route::get('menus/export', [\App\Http\Controllers\MenuController::class, 'export'])->name('menus.export');
