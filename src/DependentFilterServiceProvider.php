<?php

namespace DKulyk\Nova;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Event;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class DependentFilterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(ServingNova::class, function () {
            Nova::script('dkulyk-dependent-filter', dirname(__DIR__) . '/dist/js/filter.js');
        });

        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Get the Nova route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'namespace' => 'DKulyk\Nova\Http\Controllers',
            'domain' => config('nova.domain', null),
            'as' => 'nova.api.',
            'prefix' => 'nova-api',
            'middleware' => 'nova',
        ];
    }
}
