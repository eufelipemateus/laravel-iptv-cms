# Laravel IPTV Cms

[![Latest Stable Version](http://poser.pugx.org/tschope/iptv-cms/v)](https://packagist.org/packages/tschope/iptv-cms)  [![Total Downloads](http://poser.pugx.org/tschope/iptv-cms/downloads)](https://packagist.org/packages/tschope/iptv-cms)  [![Latest Unstable Version](http://poser.pugx.org/tschope/iptv-cms/v/unstable)](https://packagist.org/packages/tschope/iptv-cms)  [![License](http://poser.pugx.org/tschope/iptv-cms/license)](https://packagist.org/packages/tschope/iptv-cms)  [![PHP Version Require](http://poser.pugx.org/tschope/iptv-cms/require/php)](https://packagist.org/packages/tschope/iptv-cms)

![Screenshot Dashboard Feipe Mateus IPTV Channels](/.github/screenshots/dashboard.png?raw=true)

## Instaling


### Install

```bash
composer create-project tschope/iptv-cms  iptv-project
```

### Config

generate  app key

```bash
    php artisan key:generate
```

Replace  .env with your database info.

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

- To add new locale compatibility you need contribute to [iptv-core](https://github.com/tschope/laravel-iptv-core/blob/main/src/Helpes/Locale.php) first.

- [Discord Channel to discussion about sugestion,Feature and etc.](https://discord.com/channels/885888529845076078/953528360615690270)

## License

[![License](http://poser.pugx.org/tschope/iptv-cms/license)](https://packagist.org/packages/tschope/iptv-cms)

## Author

[Felipe Mateus](https://felipemateus.com)
