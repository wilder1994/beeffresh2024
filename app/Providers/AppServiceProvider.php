<?php

namespace App\Providers;

use App\Contracts\UserRepositoryContract;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserRepositoryContract::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    
    public function boot(): void
    {
        //
    }

}
