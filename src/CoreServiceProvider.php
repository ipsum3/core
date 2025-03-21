<?php

namespace Ipsum\Core;


use Illuminate\Database\QueryException;
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
        //$this->loadViewsFrom(__DIR__.'/ressources/views', 'IpsumCore');
        $this->loadJsonTranslationsFrom(__DIR__.'/ressources/lang');

        try {
            $settings = Setting::all();
            foreach ($settings as $key => $setting) {
                Config::set($setting->key, $setting->value);
            }
        } catch (QueryException $e) { }

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
            return "<?php echo $expression ? ($expression)->format('d/m/Y') : ''; ?>";
        });

        Blade::directive('prix', function ($expression) {
            return "<?php echo number_format($expression, ((int) $expression == $expression ? 0 : 2), ',', '&nbsp;'); ?>";
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
