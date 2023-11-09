<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

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
        Validator::extend('distinct_entries', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $entries = $data[$parameters[0]];

            $count = count(array_filter($entries, function ($entry) use ($value) {
                return $entry == $value;
            }));

            return $count <= 1;
        });
    }
}
