# Laravel IPTV Cms

## Instaling

.

### Run the command below in root to install the package in your project
  
```bash

git clone https://github.com/eufelipemateus/laravel-iptv-cms.git
```

### Install

```bash
composer install 
```

### Config

Create new  file .env if not create.

```bash
    mv .env.exmple .env
```

generate  app key

```bash
    php artisan key:generate
```

Replace  .env with bd

```env
    DB_CONNECTION=mysql
    DB_HOST=´Your Host´
    DB_PORT=3306
    DB_DATABASE=´Your Database´
    DB_USERNAME=´Your Root´
    DB_PASSWORD=´Your Password´
```

### Migrate the database

```bash
php artisan migrate --seed
```

## Extra

- To add new locale compatibility you need contribute to [iptv-core](https://github.com/eufelipemateus/laravel-iptv-core/blob/main/src/Helpes/Locale.php) first.

- [Discord Channel to discussion about sugestion,Feature and etc.](https://discord.com/channels/885888529845076078/953528360615690270)

## License

[![License](http://poser.pugx.org/felipemateus/iptv-channels/license)](https://packagist.org/packages/felipemateus/iptv-channels)

## Author

[Felipe Mateus](https://felipemateus.com)
