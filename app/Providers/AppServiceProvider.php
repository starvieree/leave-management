<?php

namespace App\Providers;

use App\Repositories\Contracts\LeaveRepositoryInterface;
use App\Repositories\Eloquent\LeaveRepository;
use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            LeaveRepositoryInterface::class,
            LeaveRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(
            LeaveRequest::class,
            LeaveRequestPolicy::class
        );
    }
}
