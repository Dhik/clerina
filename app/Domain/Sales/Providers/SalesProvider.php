<?php

namespace App\Domain\Sales\Providers;

use App\Domain\Sales\BLL\AdSpentMarketPlace\AdSpentMarketPlaceBLL;
use App\Domain\Sales\BLL\AdSpentMarketPlace\AdSpentMarketPlaceBLLInterface;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLL;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLLInterface;
use App\Domain\Sales\BLL\Sales\SalesBLL;
use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLL;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\BLL\Visit\VisitBLL;
use App\Domain\Sales\BLL\Visit\VisitBLLInterface;
use App\Domain\Sales\DAL\AdSpentMarketPlace\AdSpentMarketPlaceDAL;
use App\Domain\Sales\DAL\AdSpentMarketPlace\AdSpentMarketPlaceDALInterface;
use App\Domain\Sales\DAL\AdSpentSocialMedia\AdSpentSocialMediaDAL;
use App\Domain\Sales\DAL\AdSpentSocialMedia\AdSpentSocialMediaDALInterface;
use App\Domain\Sales\DAL\Sales\SalesDAL;
use App\Domain\Sales\DAL\Sales\SalesDALInterface;
use App\Domain\Sales\DAL\SalesChannel\SalesChannelDAL;
use App\Domain\Sales\DAL\SalesChannel\SalesChannelDALInterface;
use App\Domain\Sales\DAL\Visit\VisitDAL;
use App\Domain\Sales\DAL\Visit\VisitDALInterface;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Policies\AdSpentMarketPlacePolicy;
use App\Domain\Sales\Policies\AdSpentSocialMediaPolicy;
use App\Domain\Sales\Policies\SalesChannelPolicy;
use App\Domain\Sales\Policies\SalesPolicy;
use App\Domain\Sales\Policies\VisitPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SalesProvider extends ServiceProvider
{
    protected $namespace = 'App\Domain\Sales\Controllers';

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        AdSpentSocialMediaDALInterface::class => AdSpentSocialMediaDAL::class,
        AdSpentSocialMediaBLLInterface::class => AdSpentSocialMediaBLL::class,
        AdSpentMarketPlaceDALInterface::class => AdSpentMarketPlaceDAL::class,
        AdSpentMarketPlaceBLLInterface::class => AdSpentMarketPlaceBLL::class,
        SalesBLLInterface::class => SalesBLL::class,
        SalesDALInterface::class => SalesDAL::class,
        SalesChannelBLLInterface::class => SalesChannelBLL::class,
        SalesChannelDALInterface::class => SalesChannelDAL::class,
        VisitBLLInterface::class => VisitBLL::class,
        VisitDALInterface::class => VisitDAL::class,
    ];

    /** The policy mappings for the domain.
     *
     * @var array
     */
    protected $policies = [
        Sales::class => SalesPolicy::class,
        SalesChannel::class => SalesChannelPolicy::class,
        Visit::class => VisitPolicy::class,
        AdSpentSocialMedia::class => AdSpentSocialMediaPolicy::class,
        AdSpentMarketPlace::class => AdSpentMarketPlacePolicy::class,
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
                ->group(base_path('app/Domain/Sales/Routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/Sales/Routes/api.php'));

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
