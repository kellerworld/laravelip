<?php

namespace Kellerworld\Laravelip;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton('admin', function () {
            return new Admin;
        });
    }
}

