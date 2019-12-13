<?php

namespace Ipsum\Core;


use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Ipsum\Core\app\Models\Setting;
use Schema;
use Config;

class CoreServiceProvider extends ServiceProvider
{

    protected $commands = [
        \Ipsum\Core\app\Console\Commands\Install::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191); // Fix version de mysql

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/ressources/views', 'IpsumCore');
        $this->loadJsonTranslationsFrom(__DIR__.'/ressources/lang');

        $settings = Setting::all();
        foreach ($settings as $key => $setting) {
            Config::set($setting->key, $setting->value);
        }

        $this->publishFiles();


        $this->bladeDirectives();

    }

    public function publishFiles()
    {
        $this->publishes([
            __DIR__.'/database/seeds/' => database_path('seeds'),
            __DIR__.'/ressources/lang/' => resource_path('lang'),
        ], 'install');
    }


    public function bladeDirectives()
    {
        Blade::directive('date', function ($expression) {
            return "<?php echo ($expression)->format('d/m/Y'); ?>";
        });

        Blade::directive('prix', function ($expression) {
            return "<?php echo (int) $expression == $expression ? (int) $expression : number_format($expression, 2, ',', ' '); ?>";
        });
    }



    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // register the artisan commands
        $this->commands($this->commands);
    }
}
