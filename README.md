# Laravel IPTV Cms

[![Latest Stable Version](http://poser.pugx.org/felipemateus/iptv-cms/v)](https://packagist.org/packages/felipemateus/iptv-cms)  [![Total Downloads](http://poser.pugx.org/felipemateus/iptv-cms/downloads)](https://packagist.org/packages/felipemateus/iptv-cms)  [![Latest Unstable Version](http://poser.pugx.org/felipemateus/iptv-cms/v/unstable)](https://packagist.org/packages/felipemateus/iptv-cms)  [![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)  [![PHP Version Require](http://poser.pugx.org/felipemateus/iptv-cms/require/php)](https://packagist.org/packages/felipemateus/iptv-cms)

![Screenshot Dashboard Feipe Mateus IPTV Channels](/.github/screenshots/dashboard.png?raw=true)

## Installation

### Requirements

- PHP 8.4+
- Composer
- MySQL

### Install IPTV CMS

Run:

```bash
composer create-project felipemateus/iptv-cms my-iptv
```

### Run the installer again

```bash
cd my-iptv
php artisan install
```


### VOD

This project includes a simple VOD module for uploading, listing and playing videos through a versioned API.

See [docs/VOD.md](docs/VOD.md) for environment variables, storage disks, and API routes.

## Extra

- To add new locale compatibility you need contribute to [iptv-core](https://github.com/eufelipemateus/laravel-iptv-core/blob/main/src/Helpes/Locale.php) first.

- [Telegram Group to discussion about sugestion,Feature and etc.](https://t.me/laravel_iptv)

## License

[![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)

## Author

[Felipe Mateus](https://felipemateus.com)
