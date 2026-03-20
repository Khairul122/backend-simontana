<?php

namespace App\Providers;

use App\Models\Monitoring;
use App\Models\RiwayatTindakan;
use App\Models\TindakLanjut;
use App\Policies\MonitoringPolicy;
use App\Policies\RiwayatTindakanPolicy;
use App\Policies\TindakLanjutPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        
    }

    
    public function boot(): void
    {
        Gate::policy(Monitoring::class, MonitoringPolicy::class);
        Gate::policy(TindakLanjut::class, TindakLanjutPolicy::class);
        Gate::policy(RiwayatTindakan::class, RiwayatTindakanPolicy::class);

        RateLimiter::for('auth-login', function (Request $request) {
            $identifier = strtolower((string) ($request->input('username') ?? $request->input('email') ?? 'anonymous'));

            return [
                Limit::perMinute(6)->by($request->ip() . '|login'),
                Limit::perMinute(10)->by($request->ip() . '|' . $identifier),
            ];
        });

        RateLimiter::for('auth-register', static function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . '|register');
        });
    }
}
