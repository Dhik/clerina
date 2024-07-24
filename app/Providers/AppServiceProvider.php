<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Order\BLL\Order\OrderBLLInterface;
use App\Domain\Order\BLL\Order\OrderBLL;
use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Order\DAL\Order\OrderDAL;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(OrderBLLInterface::class, OrderBLL::class);
        $this->app->bind(OrderDALInterface::class, OrderDAL::class);
        $this->app->bind(SalesChannelBLLInterface::class, SalesChannelBLL::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
