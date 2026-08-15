# EPG and XMLTV

The EPG module imports external XMLTV guides, maps stable guide channel IDs to live IPTV channels, and publishes global or customer-filtered XMLTV feeds. Playlists only reference the EPG; importing and programme storage remain isolated in EPG services.

## Enable the module

```env
MODULE_EPG_ENABLED=true
EPG_REQUEST_TIMEOUT=15
EPG_MAX_DOWNLOAD_BYTES=52428800
EPG_MAX_UNCOMPRESSED_BYTES=52428800
EPG_MAX_PROGRAMMES_PER_IMPORT=500000
EPG_RETENTION_DAYS=7
EPG_DEFAULT_TIMEZONE=UTC
```

Then run `php artisan config:clear` and `php artisan migrate`. When disabled, EPG admin screens are hidden, XMLTV endpoints return 404, synchronization does no work, and playlists contain no EPG metadata.

## Sources and synchronization

Open **EPG → EPG Sources**, register a public HTTP(S) XMLTV or XMLTV gzip (`.xml.gz`) URL, choose its timezone and refresh interval, then use **Sync now**. Compression is detected by the gzip signature, so the URL extension is optional. URLs resolving to local, private, loopback, link-local, multicast, reserved, or metadata addresses are rejected. Redirects, timeout, compressed and uncompressed sizes, and programme count are bounded. DTDs and entities are rejected and parsing uses `XMLReader` with network access disabled.

```bash
php artisan epg:sync              # all enabled sources
php artisan epg:sync 1            # one source ID
php artisan epg:sync-due          # queue due sources
php artisan epg:prune             # remove expired programmes
```

The scheduler checks due sources every minute and prunes daily. Run Laravel's scheduler and a queue worker in production. A cache lock prevents concurrent imports of one source. Imports update records by stable identifiers instead of duplicating them.

## Channel mapping and playlists

Edit a live channel and select an **EPG Source** and **EPG Channel**. The searchable selector limits each result set to 50 records. Mapping is optional and uses `epg_channel_id`, never a channel name.

Mapped M3U entries use the XMLTV channel `external_id` as `tvg-id`. Public playlists use `/epg.xml` as `url-tvg`; customer playlists use `/client/epg/{cdn-slug}.xml`.

## XMLTV endpoints

- `GET /epg.xml` streams every EPG channel mapped to a live channel.
- `GET /client/epg/{slug}.xml` reuses private-playlist HTTP Basic authentication and includes only channels in the customer's main or additional plans for the matching CDN.

Both return `application/xml; charset=utf-8`. Inactive, expired, revoked, indebted, or otherwise unauthorized customers are rejected by the existing customer middleware. VOD is intentionally outside the initial EPG scope.
