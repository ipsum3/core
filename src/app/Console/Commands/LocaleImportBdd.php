<?php

namespace Ipsum\Core\app\Console\Commands;

use Illuminate\Console\Command;
use Ipsum\Core\app\Models\Translate;

class LocaleImportBdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:importBdd {--file_path=} {--locale=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import d`\'un fichier csv de traduction' ;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if( $this->option('locale') ) {
            if (($handle = fopen($this->option('file_path'), "r")) !== FALSE) {
                while (($rows = fgetcsv( $handle, null, ';', '"' )) !== FALSE) {
                    $data = [
                        'translatable_type' => $rows[0],
                        'translatable_id' => $rows[1],
                        'locale' => $this->option('locale'),
                        'attribut' => $rows[2],
                        'value' => $rows[3]
                    ];
                    Translate::updateOrCreate( [
                        'translatable_type' => $rows[0],
                        'translatable_id' => $rows[1],
                        'locale' => $this->option('locale'),
                        'attribut' => $rows[2],
                    ], $data );
                }
                fclose($handle);
            }
        } else {
            echo('Veuillez renseigner la locale (ex: --locale=)');
        }
        return Command::SUCCESS;
    }

}
