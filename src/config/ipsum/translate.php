<?php

return [

    'locales' => [
        'fr' => [
            'nom' => 'fr',
            'intitule' => 'FR',

            /*
            |--------------------------------------------------------------------------
            | Parmétres envoyé à la fonction php setLocale
            |--------------------------------------------------------------------------
            */

            'setLocale' => [
                'LC_TIME' => ['fr_FR.utf8', 'fr_FR', 'fr_fr', 'fr'],
            ],


            /*
            |--------------------------------------------------------------------------
            | Parmétres envoyé à LaravelGettext::setLocale : utile ?
            |--------------------------------------------------------------------------
            */

            'gettext' => 'fr_FR',
        ],

        /*'en' => [
            'nom' => 'en',
            'intitule' => 'EN',
            'setLocale' => [
                'LC_TIME' => ['en_EN.utf8', 'en_EN', 'en_EN', 'en'],
            ],
            'gettext' => 'en_EN',
        ]*/
    ],


    'default_locale' => 'fr',

];