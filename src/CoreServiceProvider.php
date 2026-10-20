<?php

namespace Ipsum\Core;


use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Database\QueryException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Ipsum\Core\app\Models\Setting;
use Prologue\Alerts\Facades\Alert;
use Schema;
use Config;
use Throwable;

class CoreServiceProvider extends ServiceProvider
{

    protected $commands = [
        \Ipsum\Core\app\Console\Commands\Install::class,
        \Ipsum\Core\app\Console\Commands\LocaleExportBdd::class,
        \Ipsum\Core\app\Console\Commands\LocaleImportBdd::class,
        \Ipsum\Core\app\Console\Commands\IdeHelper::class,
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
    public function boot(): void
    {
        $this->registerExceptionHandling();

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
            return "<?php echo prix($expression); ?>";
        });

        Blade::directive('asset_versioned', function ($path) {
            return "<?php echo asset_versioned($path); ?>";
        });
    }

    public function globalPasswordRules()
    {
        Password::defaults(function () {
            $rule = Password::min(8);

            return $rule->letters()->symbols();
        });
    }

    protected function registerExceptionHandling(): void
    {
        $handler = $this->app->make(ExceptionHandlerContract::class);

        if (! method_exists($handler, 'renderable')) {
            return;
        }

        $handler->renderable(function (Throwable $e) {
            if (
                $e instanceof TokenMismatchException
                || $e->getPrevious() instanceof TokenMismatchException
            ) {
                Alert::warning(
                    'Le formulaire a expiré, merci de le valider à nouveau.'
                )->flash();

                return back()->withInput();
            }

            return null;
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
