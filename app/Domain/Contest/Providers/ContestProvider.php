<?php

namespace App\Domain\Contest\Providers;

use App\Domain\Contest\BLL\ContestContent\ContestContentBLL;
use App\Domain\Contest\BLL\ContestContent\ContestContentBLLInterface;
use App\Domain\Contest\DAL\ContestContent\ContestContentDAL;
use App\Domain\Contest\DAL\ContestContent\ContestContentDALInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domain\Contest\BLL\Contest\ContestBLL;
use App\Domain\Contest\BLL\Contest\ContestBLLInterface;
use App\Domain\Contest\DAL\Contest\ContestDAL;
use App\Domain\Contest\DAL\Contest\ContestDALInterface;
use App\Domain\Contest\Policies\ContestPolicy;
use App\Domain\Contest\Models\Contest;

class ContestProvider extends ServiceProvider
{
    protected $namespace = 'App\Domain\Contest\Controllers';

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        ContestBLLInterface::class => ContestBLL::class,
        ContestDALInterface::class => ContestDAL::class,
        ContestContentBLLInterface::class => ContestContentBLL::class,
        ContestContentDALInterface::class => ContestContentDAL::class
    ];

    /** The policy mappings for the domain.
     *
     * @var array
     */
    protected $policies = [
        Contest::class => ContestPolicy::class,
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
                ->group(base_path('app/Domain/Contest/Routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/Contest/Routes/api.php'));

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
