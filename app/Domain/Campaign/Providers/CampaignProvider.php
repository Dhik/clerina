<?php

namespace App\Domain\Campaign\Providers;

use App\Domain\Campaign\BLL\CampaignContent\CampaignContentBLL;
use App\Domain\Campaign\BLL\CampaignContent\CampaignContentBLLInterface;
use App\Domain\Campaign\BLL\KOL\KeyOpinionLeaderBLL;
use App\Domain\Campaign\BLL\KOL\KeyOpinionLeaderBLLInterface;
use App\Domain\Campaign\BLL\Offer\OfferBLL;
use App\Domain\Campaign\BLL\Offer\OfferBLLInterface;
use App\Domain\Campaign\BLL\Statistic\StatisticBLL;
use App\Domain\Campaign\BLL\Statistic\StatisticBLLInterface;
use App\Domain\Campaign\DAL\CampaignContent\CampaignContentDAL;
use App\Domain\Campaign\DAL\CampaignContent\CampaignContentDALInterface;
use App\Domain\Campaign\DAL\KOL\KeyOpinionLeaderDAL;
use App\Domain\Campaign\DAL\KOL\KeyOpinionLeaderDALInterface;
use App\Domain\Campaign\DAL\Offer\OfferDAL;
use App\Domain\Campaign\DAL\Offer\OfferDALInterface;
use App\Domain\Campaign\DAL\Statistic\StatisticDAL;
use App\Domain\Campaign\DAL\Statistic\StatisticDALInterface;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\Offer;
use App\Domain\Campaign\Policies\CampaignContentPolicy;
use App\Domain\Campaign\Policies\KeyOpinionLeaderPolicy;
use App\Domain\Campaign\Policies\OfferPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domain\Campaign\BLL\Campaign\CampaignBLL;
use App\Domain\Campaign\BLL\Campaign\CampaignBLLInterface;
use App\Domain\Campaign\DAL\Campaign\CampaignDAL;
use App\Domain\Campaign\DAL\Campaign\CampaignDALInterface;
use App\Domain\Campaign\Policies\CampaignPolicy;
use App\Domain\Campaign\Models\Campaign;

class CampaignProvider extends ServiceProvider
{
    protected $namespace = 'App\Domain\Campaign\Controllers';

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        CampaignBLLInterface::class => CampaignBLL::class,
        CampaignDALInterface::class => CampaignDAL::class,
        CampaignContentBLLInterface::class => CampaignContentBLL::class,
        CampaignContentDALInterface::class => CampaignContentDAL::class,
        KeyOpinionLeaderBLLInterface::class => KeyOpinionLeaderBLL::class,
        KeyOpinionLeaderDALInterface::class => KeyOpinionLeaderDAL::class,
        OfferBLLInterface::class => OfferBLL::class,
        OfferDALInterface::class => OfferDAL::class,
        StatisticBLLInterface::class => StatisticBLL::class,
        StatisticDALInterface::class => StatisticDAL::class
    ];

    /** The policy mappings for the domain.
     *
     * @var array
     */
    protected $policies = [
        Campaign::class => CampaignPolicy::class,
        CampaignContent::class => CampaignContentPolicy::class,
        KeyOpinionLeader::class => KeyOpinionLeaderPolicy::class,
        Offer::class => OfferPolicy::class
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
                ->group(base_path('app/Domain/Campaign/Routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/Campaign/Routes/api.php'));

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
