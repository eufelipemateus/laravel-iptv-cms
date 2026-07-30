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

## Operation mode

The platform supports one exclusive distribution mode at a time:

- `m3u8`: traditional playlist distribution (`/public/m3u8/*`, `/client/m3u8/*`).
- `dtv3`: TV 3.0 distribution (`/api/v1/tv/*`, `/tv3/*`).

Default mode for existing installations is `m3u8`.

Mode is configured in the admin panel (`IPTV Config`) and persisted through the same `IPTVConfig` source of truth.

When mode changes:

- only one mode remains active;
- routes and exclusive UI of the inactive mode return `404`;
- caches are invalidated;
- no channel, customer, stream, playlist, device or program data is deleted.

## Extra

- To add new locale compatibility you need contribute to [iptv-core](https://github.com/eufelipemateus/laravel-iptv-core/blob/main/src/Helpes/Locale.php) first.

- [Telegram Group to discussion about sugestion,Feature and etc.](https://t.me/laravel_iptv)

## License

[![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)

## Author

[Felipe Mateus](https://felipemateus.com)
