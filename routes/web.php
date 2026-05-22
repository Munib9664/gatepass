<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\WatchmanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'watchman' => redirect()->route('watchman.dashboard'),
        default => redirect()->route('resident.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:watchman'])->group(function () {
    Route::get('/watchman/dashboard', [WatchmanController::class, 'dashboard'])->name('watchman.dashboard');
    Route::get('/watchman/visitors', [WatchmanController::class, 'visitors'])->name('watchman.visitors.index');
    Route::post('/watchman/visitors', [WatchmanController::class, 'store'])->name('watchman.visitors.store');
    Route::patch('/watchman/visitors/{visitor}/resend', [WatchmanController::class, 'resend'])->name('watchman.visitors.resend');
    Route::patch('/watchman/visitors/{visitor}/entry', [WatchmanController::class, 'markEntry'])->name('watchman.visitors.entry');
    Route::patch('/watchman/visitors/{visitor}/exit', [WatchmanController::class, 'markExit'])->name('watchman.visitors.exit');
    Route::patch('/watchman/visitors/{visitor}/approve', [WatchmanController::class, 'approveByWatchman'])->name('watchman.visitors.approve');
    Route::patch('/watchman/visitors/{visitor}/reject', [WatchmanController::class, 'rejectByWatchman'])->name('watchman.visitors.reject');
});

Route::middleware(['auth', 'role:resident'])->group(function () {
    Route::get('/resident/dashboard', [ResidentController::class, 'dashboard'])->name('resident.dashboard');
    Route::get('/resident/visitors', [ResidentController::class, 'visitors'])->name('resident.visitors.index');
    Route::patch('/resident/visitors/{visitor}/approve', [ResidentController::class, 'approve'])->name('resident.visitors.approve');
    Route::patch('/resident/visitors/{visitor}/reject', [ResidentController::class, 'reject'])->name('resident.visitors.reject');
});

require __DIR__.'/auth.php';
