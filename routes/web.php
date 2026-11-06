<?php

declare(strict_types=1);

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Auth\Controllers\LogoutController;
use App\Features\Auth\Livewire\Login;
use App\Features\Auth\Livewire\Register;
use App\Features\Profile\Livewire\Profile;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Livewire\StockList;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:1000-per-second'])->group(function (): void {
    // Guest & Landing Route
    Route::get('/', function () {
        if (auth()->check()) {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            return $user->isAdmin()
                ? redirect()->route('admin.users')
                : redirect()->route('stocks.index');
        }

        return redirect()->route('login');
    })->name('home');

    Route::middleware('guest')->group(function (): void {
        Route::get('/login', Login::class)->name('login');
        Route::get('/register', Register::class)->name('register');
    });

    // Authenticated Routes
    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', LogoutController::class)->name('logout');
        Route::get('/profile', Profile::class)->name('profile.show');

        // Member Stock Routes
        Route::middleware(['role:member', 'owns.stock'])->group(function (): void {
            Route::get('/stocks', StockList::class)->name('stocks.index');
            Route::get('/stocks/{stock}', StockDetail::class)->name('stocks.show');
        });

        // Admin Routes
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
            Route::get('/users', UserList::class)->name('users');
            Route::get('/users/{user}/stocks', AdminUserStockList::class)->name('users.stocks');
        });
    });
});
