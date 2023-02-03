<?php

namespace Ipsum\Core;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Ipsum\Core\app\Exceptions\Handler;
use Ipsum\Core\app\Models\Setting;
use Illuminate\Validation\Rules\Password;
use Schema;
use Config;

class CoreServiceProvider extends ServiceProvider
{

    protected $commands = [
        \Ipsum\Core\app\Console\Commands\Install::class,
        \Ipsum\Core\app\Console\Commands\LocaleExportBdd::class,
        \Ipsum\Core\app\Console\Commands\LocaleImportBdd::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public $singletons = [
        \Illuminate\Contracts\Debug\ExceptionHandler::class => Handler::class,
    ];


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

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

        $this->globalPasswordRules();
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
            return "<?php echo number_format($expression, (intval($expression) == $expression ? 0 : 2), ',', '&nbsp;'); ?>";
        });
    }

    public function globalPasswordRules()
    {
        Password::defaults(function () {
            $rule = Password::min(8);

            return $rule->letters()->symbols();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/ipsum/translate.php', 'ipsum.translate'
        );

        // register the artisan commands
        $this->commands($this->commands);
    }
}
