<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\LoaRequestController;
use App\Http\Controllers\Student\IdentityController;
use App\Http\Controllers\Student\LoaFormController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/staff/login', [LoginController::class, 'create'])->name('login');
    Route::post('/staff/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/staff/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/staff/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/staff/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/staff/dashboard', DashboardController::class)->name('staff.dashboard');
    Route::get('/staff/loa/{loaRequest}', [LoaRequestController::class, 'show'])->name('staff.loa.show');
    Route::get('/staff/loa/{loaRequest}/files/{attachment}', [LoaRequestController::class, 'attachment'])->name('staff.loa.attachment');
});

Route::get('/loa/request', [IdentityController::class, 'create'])->name('student.identity');
Route::post('/loa/request', [IdentityController::class, 'store'])
    ->middleware('throttle:loa-identity')
    ->name('student.identity.store');
Route::get('/loa/request/check-email', [IdentityController::class, 'sent'])->name('student.identity.sent');

Route::get('/loa/form/{token}', [LoaFormController::class, 'show'])->name('student.form.show');
Route::post('/loa/form/{token}', [LoaFormController::class, 'store'])->name('student.form.store');
Route::get('/loa/form/{token}/submitted', [LoaFormController::class, 'submitted'])->name('student.form.submitted');
