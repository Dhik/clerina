<?php

namespace App\Domain\Marketing\Providers;

use App\Domain\Marketing\BLL\Marketing\MarketingBLL;
use App\Domain\Marketing\BLL\Marketing\MarketingBLLInterface;
use App\Domain\Marketing\BLL\MarketingCategory\MarketingCategoryBLL;
use App\Domain\Marketing\BLL\MarketingCategory\MarketingCategoryBLLInterface;
use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLL;
use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Marketing\DAL\Marketing\MarketingDAL;
use App\Domain\Marketing\DAL\Marketing\MarketingDALInterface;
use App\Domain\Marketing\DAL\MarketingCategory\MarketingCategoryDAL;
use App\Domain\Marketing\DAL\MarketingCategory\MarketingCategoryDALInterface;
use App\Domain\Marketing\DAL\SocialMedia\SocialMediaDAL;
use App\Domain\Marketing\DAL\SocialMedia\SocialMediaDALInterface;
use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Marketing\Policies\MarketingCategoryPolicy;
use App\Domain\Marketing\Policies\MarketingPolicy;
use App\Domain\Marketing\Policies\SocialMediaPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MarketingProvider extends ServiceProvider
{
    protected $namespace = 'App\Domain\Marketing\Controllers';

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        MarketingDALInterface::class => MarketingDAL::class,
        MarketingBLLInterface::class => MarketingBLL::class,
        MarketingCategoryDALInterface::class => MarketingCategoryDAL::class,
        MarketingCategoryBLLInterface::class => MarketingCategoryBLL::class,
        SocialMediaDALInterface::class => SocialMediaDAL::class,
        SocialMediaBLLInterface::class => SocialMediaBLL::class,
    ];

    /** The policy mappings for the domain.
     *
     * @var array
     */
    protected $policies = [
        Marketing::class => MarketingPolicy::class,
        SocialMedia::class => SocialMediaPolicy::class,
        MarketingCategory::class => MarketingCategoryPolicy::class,
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
        if (! $this->app->routesAreCached()) {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/Marketing/Routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/Marketing/Routes/api.php'));

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
