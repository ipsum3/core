<?php

namespace Ipsum\Core\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LocaleExportBdd extends Command
{
    protected $signature = 'locale:exportBdd
                            {--locale=en : Langue cible (ex: en, es, it)}
                            {--source=fr : Langue source}
                            {--path=storage : Dossier de sortie}
                            {--translate : Traduit automatiquement avec Google Translate}';

    protected $description = 'Création d\'un fichier CSV de traduction pour la base de données via Google Translate';

    public function handle()
    {
        $lignes = [];
        $classes = [];

        // 1. Scan des dossiers des packages Ipsum
        $dir = base_path() . '/vendor/ipsum3/';
        if (File::isDirectory($dir)) {
            foreach (File::directories($dir) as $folder) {
                $modelsPath = $folder . '/src/app/Models/';
                $classes = array_merge($this->searchTranslatableClass($modelsPath), $classes);
            }
        }

        // 2. Scan des modèles de l'application principale
        $classes = array_merge($this->searchTranslatableClass(app_path('Models/')), $classes);
        $classes = array_unique($classes);

        // 3. Extraction des lignes traduisibles
        foreach ($classes as $class) {
            $lignes = array_merge($this->getTranslatableProperties($class), $lignes);
        }

        if (empty($lignes)) {
            $this->warn("Aucun texte à traduire ou à exporter n'a été trouvé.");
            return Command::SUCCESS;
        }

        // 4. Traduction optionnelle avec stichoza/google-translate-php
        if ($this->option('translate')) {
            $lignes = $this->translateLines($lignes);
        }

        // 5. Sauvegarde
        $this->saveCsvFile($lignes);

        return Command::SUCCESS;
    }

    /**
     * Traduit le contenu des lignes récoltées
     */
    private function translateLines(array $lignes): array
    {
        $source = $this->option('source');
        $target = $this->option('locale');

        $this->info("Initialisation de Google Translate ({$source} -> {$target})...");

        $tr = new GoogleTranslate();
        $tr->setSource($source);
        $tr->setTarget($target);

        $this->info("Traduction en cours...");

        // Création d'une barre de progression dans la console Laravel
        $bar = $this->output->createProgressBar(count($lignes));
        $bar->start();

        foreach ($lignes as &$ligne) {
            $texteOriginal = $ligne[3]; // L'index 3 correspond au texte original

            if (!empty($texteOriginal)) {
                try {
                    // On remplace le texte d'origine par le texte traduit
                    // Si vous préférez ajouter la traduction dans une nouvelle colonne, faites : $ligne[] = $tr->translate($texteOriginal);
                    $ligne[3] = $tr->translate($texteOriginal);
                } catch (\Exception $e) {
                    // En cas d'erreur (ex: timeout réseau), on garde le texte original pour ne pas crasher
                    $this->error("\nErreur lors de la traduction de : " . substr($texteOriginal, 0, 20) . "...");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(); // Saut de ligne après la fin de la barre de progression

        return $lignes;
    }

    private function saveCsvFile(array $lignes)
    {
        $path = $this->option('path');
        $directory = urlencode($path) === $path && !str_starts_with($path, '/') ? base_path($path) : $path;

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filePath = $directory . '/translate-' . $this->option('locale') . '.csv';
        $fp = fopen($filePath, 'w');

        if (!$fp) {
            $this->error("Impossible de créer le fichier dans : {$directory}");
            return;
        }

        // Conversion en UTF-8 BOM pour Excel
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

        foreach ($lignes as $ligne) {
            fputcsv($fp, $ligne, ';');
        }

        fclose($fp);

        $this->info("Fichier sauvegardé avec succès dans : {$filePath}");
    }

    private function getTranslatableProperties(string $class): array
    {
        $lignes = [];
        $instance = new $class();

        try {
            $reflection = new ReflectionClass($instance);
            if (!$reflection->hasProperty('translatable_attributes')) {
                return $lignes;
            }

            $property = $reflection->getProperty('translatable_attributes');
            $property->setAccessible(true);
            $translatable_attributes = $property->getValue($instance);
        } catch (ReflectionException $e) {
            return $lignes;
        }

        if (empty($translatable_attributes) || !is_array($translatable_attributes)) {
            return $lignes;
        }

        // Utilisation de chunk pour préserver la mémoire vive (RAM)
        $instance->newQuery()->chunk(200, function ($datas) use (&$lignes, $class, $translatable_attributes) {
            foreach ($datas as $data) {
                foreach ($translatable_attributes as $attribute) {
                    if (!empty($data->$attribute)) {
                        $lignes[] = [
                            $class,
                            $data->getKey(),
                            $attribute,
                            $data->$attribute
                        ];
                    }
                }
            }
        });

        return $lignes;
    }

    private function searchTranslatableClass(string $modelsDir): array
    {
        $classes = [];
        if (!File::isDirectory($modelsDir)) {
            return $classes;
        }

        $files = File::allFiles($modelsDir);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = $file->getContents();
            if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                $namespace = trim($matches[1]);
                $className = $namespace . '\\' . $file->getFilenameWithoutExtension();

                if (class_exists($className)) {
                    $traits = class_uses_recursive($className);
                    if (in_array("Ipsum\Core\Concerns\Translatable", $traits)) {
                        $classes[] = $className;
                    }
                }
            }
        }

        return $classes;
    }
}