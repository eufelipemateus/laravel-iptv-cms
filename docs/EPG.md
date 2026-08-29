# EPG and XMLTV

The EPG module imports external XMLTV guides, maps stable guide channel IDs to live IPTV channels, and publishes global or customer-filtered XMLTV feeds. Playlists only reference the EPG; importing and programme storage remain isolated in EPG services.

## Enable the module

```env
MODULE_EPG_ENABLED=true
EPG_REQUEST_TIMEOUT=15
EPG_CONNECT_TIMEOUT=5
EPG_MAX_REDIRECTS=3
EPG_SYNC_LOCK_SECONDS=1800
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

The scheduler checks due sources every minute and prunes daily. Run both `php artisan schedule:work` and `php artisan queue:work` in production. Queued synchronization jobs are unique per source, and a second cache lock prevents concurrent imports for `EPG_SYNC_LOCK_SECONDS`.

Every successful import creates a new programme generation. XMLReader processes the document as a stream and programmes are written in small batches. The new generation is exposed only after the entire document succeeds; missing programmes are then reconciled and the previous generation is removed. A failed import is discarded without replacing the last valid guide. `EPG_RETENTION_DAYS` controls how far expired programmes remain eligible for output and pruning.

## Channel mapping and playlists

Edit a live channel and select an **EPG Source** and **EPG Channel**. The searchable selector limits each result set to 50 records. Mapping is optional and uses `epg_channel_id`, never a channel name.

The source-provided `external_id` remains unchanged in the database. Exported XMLTV IDs use the stable global form `{epg_source_id}:{external_id}`, preventing collisions when multiple sources publish the same identifier. The same value is used by `<channel id>`, `<programme channel>`, and M3U `tvg-id`. Public playlists use `/epg.xml` as `url-tvg`; customer playlists use `/client/epg/{cdn-slug}.xml`.

## XMLTV endpoints

- `GET /epg.xml` streams every EPG channel mapped to a live channel.
- `GET /client/epg/{slug}.xml` reuses private-playlist HTTP Basic authentication and includes only channels in the customer's main or additional plans for the matching CDN.

Both return `application/xml; charset=utf-8`. Inactive, expired, revoked, indebted, or otherwise unauthorized customers are rejected by the existing customer middleware. VOD is intentionally outside the initial EPG scope.

## Security and limits

Only HTTP and HTTPS sources accepted by the shared SSRF guard are downloaded. Localhost, private/link-local/reserved addresses and cloud metadata endpoints are blocked, including at every redirect. Redirect count, connection/request timeouts, compressed size, uncompressed gzip size, and programme count are configurable. XML doctypes and entities are rejected, and libxml network access remains disabled to prevent XXE.
