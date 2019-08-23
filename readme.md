## Install

``` bash
# install the package
composer require ipsum3/settings

# run the migration
php artisan vendor:publish --provider="Ipsum\Settings\SettingsServiceProvider"
php artisan migrate

# [optional] insert some example dummy data to the database
php artisan db:seed --class="Backpack\Settings\database\seeds\SettingsTableSeeder"
```
