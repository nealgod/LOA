<?php

namespace App\Providers;

use App\Models\LoaRequest;
use App\Policies\LoaRequestPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(LoaRequest::class, LoaRequestPolicy::class);

        RateLimiter::for('loa-identity', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });
    }
}
