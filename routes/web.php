<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\ShortUrlController;
use App\Http\Controllers\ShortUrlRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('short-urls', ShortUrlController::class)->only(['index', 'store']);
        Route::get('short-urls/download', [ShortUrlController::class, 'download'])->name('short-urls.download');
        Route::post('invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::resource('companies', CompanyController::class)->only(['index', 'show', 'store']);
        
        Route::get('my-company', [CompanyController::class, 'showMyCompany'])->name('companies.my-company');
    });

    Route::get('/s/{code}', [ShortUrlRedirectController::class, 'redirect'])
        ->where('code', '[a-zA-Z0-9]+')
        ->name('short-url.redirect');
});


Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});
