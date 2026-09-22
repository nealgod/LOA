<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\LoaRequestController;
use App\Http\Controllers\Staff\ReportsController;
use App\Http\Controllers\Student\IdentityController;
use App\Http\Controllers\Student\LoaFormController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

// ── Staff login / logout (self-registration removed) ─────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/staff/login',  [LoginController::class, 'create'])->name('login');
    Route::post('/staff/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::post('/staff/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Invitation setup (public — guest only, no auth required) ─────────────────
Route::middleware('guest')->group(function () {
    Route::get('/staff/invite/{token}',  [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/staff/invite/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});

// ── Authenticated staff ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard, pipeline, reports, profile
    Route::get('/staff/dashboard', DashboardController::class)->name('staff.dashboard');
    Route::get('/staff/pipeline',  [LoaRequestController::class, 'pipeline'])->name('staff.pipeline');
    Route::get('/staff/reports',   ReportsController::class)->name('staff.reports');
    Route::get('/staff/profile', function () {
        $user     = auth()->user();
        $acted    = \App\Models\LoaRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('dept_head_by', $user->id)
                  ->orWhere('saso_by', $user->id)
                  ->orWhere('campus_director_by', $user->id)
                  ->orWhere('registrar_by', $user->id)
                  ->orWhere('guidance_by', $user->id)
                  ->orWhere('rejected_by', $user->id);
            })->count();
        $approved = \App\Models\LoaRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('dept_head_by', $user->id)
                  ->orWhere('saso_by', $user->id)
                  ->orWhere('campus_director_by', $user->id)
                  ->orWhere('registrar_by', $user->id)
                  ->orWhere('guidance_by', $user->id);
            })->count();
        $rejected = \App\Models\LoaRequest::query()
            ->where('rejected_by', $user->id)
            ->count();
        return view('staff.profile', compact('user', 'acted', 'approved', 'rejected'));
    })->name('staff.profile');

    // LOA detail / approve / reject (dept-scoped)
    Route::middleware('dept.scope')->group(function () {
        Route::get('/staff/loa/{loaRequest}',                        [LoaRequestController::class, 'show'])->name('staff.loa.show');
        Route::get('/staff/loa/{loaRequest}/files/{attachment}',     [LoaRequestController::class, 'attachment'])->name('staff.loa.attachment');
        Route::post('/staff/loa/{loaRequest}/approve',               [LoaRequestController::class, 'approve'])->name('staff.loa.approve');
        Route::post('/staff/loa/{loaRequest}/reject',                [LoaRequestController::class, 'reject'])->name('staff.loa.reject');
    });

    // Admin — user management (Administrator role only, enforced in controller)
    Route::middleware('admin.only')->group(function () {
        Route::get('/admin/users',                         [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create',                  [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users',                        [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit',             [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::patch('/admin/users/{user}',                [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}',               [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{user}/resend',          [AdminUserController::class, 'resend'])->name('admin.users.resend');
        Route::post('/admin/users/{user}/get-link',        [AdminUserController::class, 'getLink'])->name('admin.users.get-link');
    });
});

// ── Student LOA request flow ──────────────────────────────────────────────────
Route::get('/loa/request',            [IdentityController::class, 'create'])->name('student.identity');
Route::post('/loa/request',           [IdentityController::class, 'store'])
    ->middleware('throttle:loa-identity')
    ->name('student.identity.store');
Route::get('/loa/request/check-email',[IdentityController::class, 'sent'])->name('student.identity.sent');

Route::get('/loa/form/{token}',           [LoaFormController::class, 'show'])->name('student.form.show');
Route::post('/loa/form/{token}',          [LoaFormController::class, 'store'])->name('student.form.store');
Route::get('/loa/form/{token}/submitted', [LoaFormController::class, 'submitted'])->name('student.form.submitted');
