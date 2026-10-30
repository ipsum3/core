<?php

namespace Ipsum\Core;


use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Ipsum\Core\app\Exceptions\Handler;
use Ipsum\Core\app\Models\Setting;
use Schema;
use Config;


class CoreServiceProvider extends ServiceProvider
{

    protected array $commands = [
        \Ipsum\Core\app\Console\Commands\Install::class,
        \Ipsum\Core\app\Console\Commands\LocaleExportBdd::class,
        \Ipsum\Core\app\Console\Commands\LocaleImportBdd::class,
        \Ipsum\Core\app\Console\Commands\IdeHelper::class,
    ];


    /**
     * Bootstrap the application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerExceptionHandling();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
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

    public function publishFiles(): void
    {
        $this->publishes([
            __DIR__.'/database/seeds/' => database_path('seeds'),
            __DIR__.'/ressources/lang/' => resource_path('lang'),
        ], 'install');
    }


    public function bladeDirectives(): void
    {
        Blade::directive('date', static function ($expression) {
            return "<?php echo $expression ? ($expression)->format('d/m/Y') : ''; ?>";
        });

        Blade::directive('prix', static function ($expression) {
            return "<?php echo prix($expression); ?>";
        });

        Blade::directive('asset_versioned', static function ($path) {
            return "<?php echo asset_versioned($path); ?>";
        });
    }

    public function globalPasswordRules(): void
    {
        Password::defaults(static function () {
            $rule = Password::min(8);

            return $rule->letters()->symbols();
        });
    }

    /**
     * @throws BindingResolutionException
     */
    protected function registerExceptionHandling(): void
    {
        Handler::register($this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class));
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/ipsum/translate.php', 'ipsum.translate'
        );

        // register the artisan commands
        $this->commands($this->commands);
    }

}
