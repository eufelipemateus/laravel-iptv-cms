# Laravel IPTV Cms

[![Latest Stable Version](http://poser.pugx.org/felipemateus/iptv-cms/v)](https://packagist.org/packages/felipemateus/iptv-cms)  [![Total Downloads](http://poser.pugx.org/felipemateus/iptv-cms/downloads)](https://packagist.org/packages/felipemateus/iptv-cms)  [![Latest Unstable Version](http://poser.pugx.org/felipemateus/iptv-cms/v/unstable)](https://packagist.org/packages/felipemateus/iptv-cms)  [![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)  [![PHP Version Require](http://poser.pugx.org/felipemateus/iptv-cms/require/php)](https://packagist.org/packages/felipemateus/iptv-cms)

![Screenshot Dashboard Feipe Mateus IPTV Channels](/.github/screenshots/dashboard.png?raw=true)

## Instaling


### Install

```bash
composer create-project felipemateus/iptv-cms  iptv-project
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

- To add new locale compatibility you need contribute to [iptv-core](https://github.com/eufelipemateus/laravel-iptv-core/blob/main/src/Helpes/Locale.php) first.

- [Discord Channel to discussion about sugestion,Feature and etc.](https://discord.com/channels/885888529845076078/953528360615690270)

## License

[![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)

## Author

[Felipe Mateus](https://felipemateus.com)
