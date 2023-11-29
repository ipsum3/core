<?php

namespace Ipsum\Core\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class IdeHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipsum:ide';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update IDE Helper files' ;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        Artisan::call('ide-helper:generate');
        Artisan::call('ide-helper:models', [
            '--write' => true,
            '--smart-reset' => true,
            '--quiet' => true,
        ]);
        Artisan::call('ide-helper:meta');

        $this->info('IDE Helper files updated successfully!');
        return Command::SUCCESS;
    }

}
