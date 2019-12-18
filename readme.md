## Install

``` bash
# install the package
composer require ipsum3/core

# Run install
php artisan ipsum:core:install
```

### Add Setting seeder to DatabaseSeeder.php file
`$this->call(SettingsTableSeeder::class);`
