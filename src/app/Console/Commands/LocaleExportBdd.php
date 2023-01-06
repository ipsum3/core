<?php

namespace Ipsum\Core\app\Console\Commands;

use Illuminate\Console\Command;
use ReflectionClass;

class LocaleExportBdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:exportBdd {--locale=en} {--path=storage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creation fichier csv de traduction' ;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $lignes = [];
        $classes = [];
        $dir = base_path() . '/vendor/ipsum3/';
        $ipsumFolders = scandir( $dir );
        unset( $ipsumFolders[array_search( '.', $ipsumFolders )] );
        unset( $ipsumFolders[array_search( '..', $ipsumFolders )] );
        foreach ( $ipsumFolders as $folder ) {
            $classes = array_merge( $this->searchTranslatableClass( $dir . $folder . '/src/app/Models/' ), $classes);
        }

        $classes = array_merge( $this->searchTranslatableClass(  app_path() . '/Models/' ), $classes);

        foreach ( $classes as $class ) {
            $lignes = array_merge($this->getTranslatableProperties( $class ), $lignes);
        }

        $this->saveCsvFile( $lignes );

        return Command::SUCCESS;
    }

    private function saveCsvFile( $lignes )
    {
        $fp = fopen($this->option('path') . '/translate-' . $this->option('locale')  . '.csv', 'w');
        //Convertion en UTF-8 BOM pour excel
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($lignes as $ligne) {
            fputcsv($fp, $ligne, ';');
        }

        fclose($fp);

        $this->info('Fichier sauvegardé !');
    }

    private function getTranslatableProperties( $class ): array
    {
        $lignes = [];
        $instance = new $class();
        $reflection = new ReflectionClass($instance);
        $property = $reflection->getProperty('translatable_attributes');
        $property->setAccessible(true);
        $translatable_attributes = $property->getValue($instance);
        $datas = $instance->all();
        foreach ( $datas as $data ) {
            foreach ( $translatable_attributes as $attribute ) {
                if( $data->$attribute ) {
                    $lignes[] = [ $class, $data->id, $attribute, $data->$attribute ];
                }
            }
        }
        return $lignes;
    }

    private function searchTranslatableClass( $modelsDir ): array
    {
        $classes = [];
        if( !is_dir( $modelsDir ) ) {
            return $classes;
        }

        $modelsFiles = scandir( $modelsDir );
        foreach ( $modelsFiles as $file ) {
            if( is_dir( $modelsDir . $file ) ) {
                if( $file != '.' && $file != '..' ) {
                    $subFolderModels = scandir( $modelsDir . $file );
                    foreach ( $subFolderModels as $subFile ) {
                        $path_parts = pathinfo($subFile);
                        if( isset( $path_parts['extension'] ) && $path_parts['extension'] == 'php' ) {
                            preg_match_all('/namespace.*;/', file_get_contents($modelsDir . $file . '/' . $subFile), $out);
                            foreach ( $out as $namespace ) {
                                //Prend namespace de 0 et break car certains fichiers possedent plusieurs fois le mot namespace dans leur contenu et on ne souhaite que le veritable namespace du fichier
                                if( in_array( "Ipsum\Core\Concerns\Translatable", class_uses( substr( str_replace(";", "", $namespace[0]), 10, strlen( $namespace[0] ) - 1 ) . "\\" . $path_parts['filename'] ) ) ) {
                                    $classes[] = substr( str_replace(";", "", $namespace[0]), 10, strlen( $namespace[0] ) - 1 ) . "\\" . $path_parts['filename'];
                                }
                                break;
                            }
                        }
                    }
                }
            } else {
                $path_parts = pathinfo($file);
                if( isset( $path_parts['extension'] ) && $path_parts['extension'] == 'php' ) {
                    preg_match_all('/namespace.*;/', file_get_contents($modelsDir . $file), $out);
                    foreach ( $out as $namespace ) {
                        if( in_array( "Ipsum\Core\Concerns\Translatable", class_uses( substr( str_replace(";", "", $namespace[0]), 10, strlen( $namespace[0] ) - 1 ) . "\\" . $path_parts['filename'] ) ) ) {
                            $classes[] = substr( str_replace(";", "", $namespace[0]), 10, strlen( $namespace[0] ) - 1 ) . "\\" . $path_parts['filename'];
                        }
                        break;
                    }
                }
            }
        }
        return $classes;
    }
}
