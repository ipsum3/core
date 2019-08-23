<?php

namespace Ipsum\Core\app\Console\Commands;


class Install extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipsum:core:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Require dev packages and publish files for Ipsum\Core to work';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(4);
        $this->progressBar->start();
        $this->info(" Ipsum\Core installation started. Please wait...");
        $this->progressBar->advance();

        $this->line(' Publishing configs, langs, views and Ipsum Assets files');
        $this->executeProcess('php artisan vendor:publish --provider="Ipsum\Core\CoreServiceProvider" --tag=install');

        $this->line(" Generating users table (using Laravel's default migrations)");
        $this->executeProcess('php artisan migrate');

        $this->progressBar->finish();
        $this->info(" Ipsum\Core installation finished.");


        if ($this->confirm('Do you want to install admin?')) {
            $this->executeProcess('composer require ipsum3/admin');
            $this->executeProcess('php artisan ipsum:admin:install');
        }

    }

}
