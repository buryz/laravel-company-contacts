<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

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

// Redirect root to contacts
Route::get('/', function () {
    return redirect()->route('contacts.index');
});

// Contact routes - authenticated only (must be before resource routes)
Route::middleware('auth')->group(function () {
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::patch('/contacts/{contact}', [ContactController::class, 'update']);
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
});

// Contact routes - public routes (index, show, qr)
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
Route::get('/contacts/{contact}/qr', [ContactController::class, 'generateQR'])->name('contacts.qr');

// Search routes
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/group-by-company', [SearchController::class, 'groupByCompany'])->name('search.group-by-company');
Route::get('/search/group-by-position', [SearchController::class, 'groupByPosition'])->name('search.group-by-position');
Route::get('/search/by-tags', [SearchController::class, 'searchByTags'])->name('search.by-tags');
Route::get('/search/available-tags', [SearchController::class, 'getAvailableTags'])->name('search.available-tags');

// Tag routes (all require authentication)
Route::resource('tags', TagController::class)->middleware('auth');
Route::get('/api/tags', [TagController::class, 'api'])->name('tags.api')->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Custom authentication routes for contact management
Route::middleware('guest')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'showLoginForm'])->name('auth.login');
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/register', [AuthController::class, 'showRegisterForm'])->name('auth.register');
    Route::post('/auth/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
