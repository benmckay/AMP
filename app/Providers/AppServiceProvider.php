<?php

namespace App\Providers;

use App\Models\Department;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('users.edit', function ($view) {
            if (!$view->offsetExists('roles')) {
                $view->with('roles', Role::orderBy('name')->get());
            }

            if (!$view->offsetExists('departments')) {
                $view->with('departments', Department::active()->orderBy('name')->get());
            }
        });
    }
}
