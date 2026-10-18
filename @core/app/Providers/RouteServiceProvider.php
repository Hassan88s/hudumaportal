<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        //
        parent::boot();
    }

    public function map()
    {
        $this->mapApiRoutes();
        $this->mapAdminRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    protected function mapAdminRoutes(){
        Route::middleware(['web','auth:admin','setlang','adminglobalVariable',\App\Http\Middleware\AdminRoutePermission::class])
            ->namespace($this->namespace)
            ->prefix('admin-home')
            ->group(base_path('routes/admin.php'));
    }

}
