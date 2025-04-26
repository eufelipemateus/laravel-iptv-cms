# Laravel IPTV Channels - FelipeMateus\IPTVChannels

[![Latest Stable Version](http://poser.pugx.org/felipemateus/iptv-channels/v)](https://packagist.org/packages/felipemateus/iptv-channels)  [![Total Downloads](http://poser.pugx.org/felipemateus/iptv-channels/downloads)](https://packagist.org/packages/felipemateus/iptv-channels)  [![Latest Unstable Version](http://poser.pugx.org/felipemateus/iptv-channels/v/unstable)](https://packagist.org/packages/felipemateus/iptv-channels)  [![License](http://poser.pugx.org/felipemateus/iptv-channels/license)](https://packagist.org/packages/felipemateus/iptv-channels)  [![PHP Version Require](http://poser.pugx.org/felipemateus/iptv-channels/require/php)](https://packagist.org/packages/felipemateus/iptv-channels)

This is a libary package create to generate and control channel list m3u8 using php Laravel this libary work to [iptv-cms](https://github.com/eufelipemateus/laravel-iptv-cms).

![Screenshot Feipe Mateus IPTV Channels](/screenshots/channel_list.jpg?raw=true)

## Instaling

Tested in Laravel 9.0.

### Run the command below in root to install the package in your project
  
```bash

composer require felipemateus/iptv-channels

```

### Verify this file  'config/app.php'

```php
<?php
...
'providers' => [
...
FelipeMateus\IPTVChannels\IPTVProvider::class,
...

];

```

### Migrate the database

```bash
php artisan migrate

```

## Extra

- [more info about iptv-channels](https://felipemateus.com/blog/2021/08/pacote-gerenciador-de-canais-iptv-no-laravel)

- To add new locale compatibility you need contribute to [iptv-core](https://github.com/eufelipemateus/laravel-iptv-core/blob/main/src/Helpers/Locale.php) first.

- [Discord Channel to discussion about sugestion,Feature and etc.](https://discord.com/channels/885888529845076078/953528360615690270)

## License

[![License](http://poser.pugx.org/felipemateus/iptv-channels/license)](https://packagist.org/packages/felipemateus/iptv-channels)

## Author

[Felipe Mateus](https://felipemateus.com)
