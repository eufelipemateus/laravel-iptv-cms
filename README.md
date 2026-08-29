# Laravel IPTV Cms

[![Latest Stable Version](http://poser.pugx.org/felipemateus/iptv-cms/v)](https://packagist.org/packages/felipemateus/iptv-cms)  [![Total Downloads](http://poser.pugx.org/felipemateus/iptv-cms/downloads)](https://packagist.org/packages/felipemateus/iptv-cms)  [![Latest Unstable Version](http://poser.pugx.org/felipemateus/iptv-cms/v/unstable)](https://packagist.org/packages/felipemateus/iptv-cms)  [![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)  [![PHP Version Require](http://poser.pugx.org/felipemateus/iptv-cms/require/php)](https://packagist.org/packages/felipemateus/iptv-cms)  ![Liberapay patrons](https://img.shields.io/liberapay/patrons/eufelipemateus.svg?logo=liberapay)


![Screenshot Dashboard Feipe Mateus IPTV Channels](/.github/screenshots/dashboard.png?raw=true)

## Installation

### Requirements

- PHP 8.4+
- Composer
- MySQL | Postgres | SqlLite

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

### EPG and XMLTV

The optional EPG module imports XMLTV guides, maps guide channels to live channels, enriches M3U playlists, and provides global and customer-filtered XMLTV feeds.

See [docs/EPG.md](docs/EPG.md) for setup, synchronization, security, endpoints, and retention.


## License

[![License](http://poser.pugx.org/felipemateus/iptv-cms/license)](https://packagist.org/packages/felipemateus/iptv-cms)

## Author

[Felipe Mateus](https://felipemateus.com)
