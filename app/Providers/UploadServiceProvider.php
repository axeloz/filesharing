<?php

namespace app\Providers;

use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path().'/Helpers/Upload.php';
    }
}
