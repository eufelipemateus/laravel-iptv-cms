# EPG and XMLTV

The EPG module imports external XMLTV guides, maps stable guide channel IDs to live IPTV channels, and publishes global or customer-filtered XMLTV feeds. Playlists only reference the EPG; importing and programme storage remain isolated in EPG services.

## Enable the module

```env
MODULE_EPG_ENABLED=true

EPG_QUEUE_CONNECTION=database
EPG_QUEUE=epg
EPG_JOB_TIMEOUT=1800
EPG_SYNC_LOCK_SECONDS=3600
DATABASE_QUEUE_RETRY_AFTER=2100
EPG_ERROR_RETRY_MINUTES=15

EPG_REQUEST_TIMEOUT=15
EPG_CONNECT_TIMEOUT=5
EPG_MAX_REDIRECTS=3

EPG_MAX_DOWNLOAD_BYTES=52428800
EPG_MAX_UNCOMPRESSED_BYTES=52428800
EPG_MAX_PROGRAMMES_PER_IMPORT=500000

EPG_RETENTION_DAYS=7
EPG_DEFAULT_TIMEZONE=UTC

EPG_HTTP_CACHE_SECONDS=300
EPG_RATE_LIMIT_PER_MINUTE=30
```

Then run `php artisan config:clear` and `php artisan migrate`. The migration creates the database-backed `jobs` table used by EPG. When disabled, EPG admin screens are hidden, XMLTV endpoints return 404, synchronization does no work, and playlists contain no EPG metadata. Fresh installations keep EPG disabled unless it is selected interactively or `php artisan install --enable-epg` is used; `--disable-epg` explicitly disables it.

## Sources and synchronization

Open **EPG → EPG Sources**, register a public HTTP(S) XMLTV or XMLTV gzip (`.xml.gz`) URL, choose its timezone and refresh interval, then use **Sync now**. Compression is detected by the gzip signature, so the URL extension is optional. URLs resolving to local, private, loopback, link-local, multicast, reserved, or metadata addresses are rejected. Redirects, timeout, compressed and uncompressed sizes, and programme count are bounded. DTDs and entities are rejected and parsing uses `XMLReader` with network access disabled.

```bash
php artisan epg:sync              # all enabled sources
php artisan epg:sync 1            # one source ID
php artisan epg:sync-due          # queue due sources
php artisan epg:prune             # remove expired programmes
```

The scheduler checks due sources every minute and prunes daily. Run both processes in production:

```bash
php artisan schedule:work
php artisan queue:work database --queue=epg --timeout=1800 --tries=3
```

EPG jobs explicitly use `EPG_QUEUE_CONNECTION` and `EPG_QUEUE`, so clicking **Sync now** never performs the import inside the HTTP request even when the application's global `QUEUE_CONNECTION` is `sync`. `EPG_JOB_TIMEOUT` is the maximum job runtime. Keep `DATABASE_QUEUE_RETRY_AFTER` greater than that timeout, and keep `EPG_SYNC_LOCK_SECONDS` longer than `DATABASE_QUEUE_RETRY_AFTER` so uniqueness and the secondary cache lock cover execution and queue retry margins. Jobs retry with increasing delays; after a failed synchronization, `EPG_ERROR_RETRY_MINUTES` controls the shorter scheduler retry interval.

Every successful import creates a new programme generation. XMLReader processes the document as a stream and programmes are written in small batches. The new generation is exposed only after the entire document succeeds; missing programmes are then reconciled and the previous generation is removed. A failed import is discarded without replacing the last valid guide. `EPG_RETENTION_DAYS` controls how far expired programmes remain eligible for output and pruning.

## Channel mapping and playlists

Edit a live channel and select an **EPG Source** and **EPG Channel**. The searchable selector limits each result set to 50 records. Mapping is optional and uses `epg_channel_id`, never a channel name.

The source-provided `external_id` remains unchanged in the database. Exported XMLTV IDs use the stable global form `{epg_source_id}:{external_id}`, preventing collisions when multiple sources publish the same identifier. The same value is used by `<channel id>`, `<programme channel>`, and M3U `tvg-id`. Public playlists use `/epg.xml` as `url-tvg`; customer playlists use `/client/epg/{cdn-slug}.xml`.

## XMLTV endpoints

- `GET /epg.xml` streams every EPG channel mapped to a live channel and uses public HTTP caching.
- `GET /client/epg/{slug}.xml` reuses private-playlist HTTP Basic authentication, includes only channels in the customer's main or additional plans for the matching CDN, and uses private HTTP caching with `Vary: Authorization`.

Both return `application/xml; charset=utf-8`, an ETag, and a cache TTL controlled by `EPG_HTTP_CACHE_SECONDS`. Conditional requests can return `304 Not Modified` without serializing the XMLTV. `EPG_RATE_LIMIT_PER_MINUTE` limits repeated XMLTV requests. Inactive, expired, revoked, indebted, or otherwise unauthorized customers are rejected by the existing customer middleware. VOD is intentionally outside the initial EPG scope.

## Security and limits

Only HTTP and HTTPS sources accepted by the shared SSRF guard are downloaded. Localhost, private/link-local/reserved addresses and cloud metadata endpoints are blocked, including at every redirect. Redirect count, connection/request timeouts, compressed size, uncompressed gzip size, and programme count are configurable. XML doctypes and entities are rejected, and libxml network access remains disabled to prevent XXE.
