<?php

namespace App\Services\Epg;

use App\Services\StreamMonitoring\SsrfGuard;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class XmltvDownloader
{
    public function __construct(
        private SsrfGuard $guard,
        private ?ClientInterface $client = null,
    ) {
        $this->client ??= new Client;
    }

    public function download(string $url): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'xmltv-');
        if ($temporaryPath === false) {
            throw new EpgImportException('Unable to create a temporary XMLTV file.');
        }

        try {
            $currentUrl = $url;
            $redirects = 0;
            while (true) {
                $this->guard->assertAllowed($currentUrl);
                $response = $this->request($currentUrl);

                if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
                    if (++$redirects > (int) config('modules.epg.max_redirects', 3)) {
                        throw new EpgImportException('The XMLTV source redirected too many times.');
                    }
                    $location = $response->getHeaderLine('Location');
                    $currentUrl = $this->resolveRedirect($currentUrl, $location);

                    continue;
                }

                if ($response->getStatusCode() !== 200) {
                    throw new EpgImportException('The XMLTV source returned HTTP '.$response->getStatusCode().'.');
                }

                $this->writeLimitedResponse($response, $temporaryPath);
                break;
            }

            $prefix = file_get_contents($temporaryPath, false, null, 0, 4096);
            $isGzip = is_string($prefix) && str_starts_with($prefix, "\x1f\x8b");
            if (! is_string($prefix) || (! $isGzip && (stripos($prefix, '<!DOCTYPE') !== false || ! preg_match('/<tv(?:\s|>)/i', $prefix)))) {
                throw new EpgImportException('The response is not a safe XMLTV document.');
            }

            return $temporaryPath;
        } catch (\Throwable $exception) {
            @unlink($temporaryPath);
            if ($exception instanceof EpgImportException) {
                throw $exception;
            }
            throw new EpgImportException('Unable to download XMLTV: '.$exception->getMessage(), 0, $exception);
        }
    }

    private function request(string $url): ResponseInterface
    {
        return $this->client->request('GET', $url, [
            'allow_redirects' => false,
            'connect_timeout' => (float) config('modules.epg.connect_timeout', 5),
            'timeout' => (float) config('modules.epg.request_timeout', 15),
            'stream' => true,
            'headers' => ['Accept' => 'application/xml,text/xml,application/xmltv+xml'],
        ]);
    }

    private function writeLimitedResponse(ResponseInterface $response, string $path): void
    {
        $maximum = (int) config('modules.epg.max_download_bytes', 52428800);
        $length = (int) $response->getHeaderLine('Content-Length');
        if ($length > $maximum) {
            throw new EpgImportException('The XMLTV payload exceeds the configured size limit.');
        }

        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new EpgImportException('Unable to write the XMLTV temporary file.');
        }

        $total = 0;
        try {
            $body = $response->getBody();
            while (! $body->eof()) {
                $chunk = $body->read(8192);
                $total += strlen($chunk);
                if ($total > $maximum) {
                    throw new EpgImportException('The XMLTV payload exceeds the configured size limit.');
                }
                fwrite($handle, $chunk);
            }
        } finally {
            fclose($handle);
        }
    }

    private function resolveRedirect(string $base, string $location): string
    {
        if ($location === '') {
            throw new EpgImportException('The XMLTV source returned an invalid redirect.');
        }
        if (filter_var($location, FILTER_VALIDATE_URL)) {
            return $location;
        }
        $parts = parse_url($base);
        if (! is_array($parts) || ! str_starts_with($location, '/')) {
            throw new EpgImportException('Relative XMLTV redirects must use an absolute path.');
        }
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port.$location;
    }
}
