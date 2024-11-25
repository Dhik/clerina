<?php

namespace App\Domain\SpentTarget\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domain\SpentTarget\BLL\SpentTarget\SpentTargetBLL;
use App\Domain\SpentTarget\BLL\SpentTarget\SpentTargetBLLInterface;
use App\Domain\SpentTarget\DAL\SpentTarget\SpentTargetDAL;
use App\Domain\SpentTarget\DAL\SpentTarget\SpentTargetDALInterface;
use App\Domain\SpentTarget\Policies\SpentTargetPolicy;
use App\Domain\SpentTarget\Models\SpentTarget;

class SpentTargetProvider extends ServiceProvider
{
    protected $namespace = 'App\Domain\SpentTarget\Controllers';

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        SpentTargetBLLInterface::class => SpentTargetBLL::class,
        SpentTargetDALInterface::class => SpentTargetDAL::class,
    ];

    /** The policy mappings for the domain.
     *
     * @var array
     */
    protected $policies = [
        SpentTarget::class => SpentTargetPolicy::class,
    ];


    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEvents();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerPolicies();
    }

    /**
     * Register the domain's routes.
     *
     * @return void
     */
    public function registerRoutes()
    {
        if (!$this->app->routesAreCached()) {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/SpentTarget/Routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/SpentTarget/Routes/api.php'));

            $this->app->booted(function () {
                $this->app['router']->getRoutes()->refreshNameLookups();
                $this->app['router']->getRoutes()->refreshActionLookups();
            });
        }
    }

    /**
     * Register the domain's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    public function registerEvents()
    {
        $this->booting(function () {
            foreach ($this->listen as $event => $listeners) {
                foreach (array_unique($listeners) as $listener) {
                    Event::listen($event, $listener);
                }
            }
        });
    }
}
