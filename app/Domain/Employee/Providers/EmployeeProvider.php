<?php

namespace App\Domain\Employee\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domain\Employee\BLL\Employee\EmployeeBLL;
use App\Domain\Employee\BLL\Employee\EmployeeBLLInterface;
use App\Domain\Employee\DAL\Employee\EmployeeDAL;
use App\Domain\Employee\DAL\Employee\EmployeeDALInterface;
use App\Domain\Employee\Policies\EmployeePolicy;
use App\Domain\Employee\Models\Employee;

use App\Domain\Employee\BLL\Attendance\AttendanceBLL;
use App\Domain\Employee\BLL\Attendance\AttendanceBLLInterface;
use App\Domain\Employee\DAL\Attendance\AttendanceDAL;
use App\Domain\Employee\DAL\Attendance\AttendanceDALInterface;

class EmployeeProvider extends ServiceProvider
{
    protected $namespace = 'App\Domain\Employee\Controllers';

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        EmployeeBLLInterface::class => EmployeeBLL::class,
        EmployeeDALInterface::class => EmployeeDAL::class,
        AttendanceBLLInterface::class => AttendanceBLL::class,
        AttendanceDALInterface::class => AttendanceDAL::class,
    ];

    /** The policy mappings for the domain.
     *
     * @var array
     */
    protected $policies = [
        Employee::class => EmployeePolicy::class,
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
                ->group(base_path('app/Domain/Employee/Routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('app/Domain/Employee/Routes/api.php'));

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
