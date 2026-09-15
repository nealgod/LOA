<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\LoaRequestController;
use App\Http\Controllers\Staff\ReportsController;
use App\Http\Controllers\Student\IdentityController;
use App\Http\Controllers\Student\LoaFormController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/staff/login', [LoginController::class, 'create'])->name('login');
    Route::post('/staff/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
    Route::get('/staff/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/staff/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('register.store');
});

Route::post('/staff/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/staff/dashboard', DashboardController::class)->name('staff.dashboard');
    Route::get('/staff/pipeline', [LoaRequestController::class, 'pipeline'])->name('staff.pipeline');
    Route::get('/staff/reports', ReportsController::class)->name('staff.reports');
    Route::get('/staff/profile', function () {
        $user  = auth()->user();
        $acted = \App\Models\LoaRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('dept_head_by', $user->id)
                  ->orWhere('saso_by', $user->id)
                  ->orWhere('campus_director_by', $user->id)
                  ->orWhere('rejected_by', $user->id);
            })->count();
        $approved = \App\Models\LoaRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('dept_head_by', $user->id)
                  ->orWhere('saso_by', $user->id)
                  ->orWhere('campus_director_by', $user->id);
            })->count();
        $rejected = \App\Models\LoaRequest::query()
            ->where('rejected_by', $user->id)
            ->count();
        return view('staff.profile', compact('user', 'acted', 'approved', 'rejected'));
    })->name('staff.profile');

    Route::middleware('dept.scope')->group(function () {
        Route::get('/staff/loa/{loaRequest}', [LoaRequestController::class, 'show'])->name('staff.loa.show');
        Route::get('/staff/loa/{loaRequest}/files/{attachment}', [LoaRequestController::class, 'attachment'])->name('staff.loa.attachment');
        Route::post('/staff/loa/{loaRequest}/approve', [LoaRequestController::class, 'approve'])->name('staff.loa.approve');
        Route::post('/staff/loa/{loaRequest}/reject', [LoaRequestController::class, 'reject'])->name('staff.loa.reject');
    });
});

Route::get('/loa/request', [IdentityController::class, 'create'])->name('student.identity');
Route::post('/loa/request', [IdentityController::class, 'store'])
    ->middleware('throttle:loa-identity')
    ->name('student.identity.store');
Route::get('/loa/request/check-email', [IdentityController::class, 'sent'])->name('student.identity.sent');

Route::get('/loa/form/{token}', [LoaFormController::class, 'show'])->name('student.form.show');
Route::post('/loa/form/{token}', [LoaFormController::class, 'store'])->name('student.form.store');
Route::get('/loa/form/{token}/submitted', [LoaFormController::class, 'submitted'])->name('student.form.submitted');
