<?php

namespace Tests\Unit\Services;

use App\Services\Epg\EpgImportException;
use App\Services\Epg\XmltvDownloader;
use App\Services\StreamMonitoring\SsrfGuard;
use App\Services\StreamMonitoring\StreamMonitoringSecurityException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Mockery;
use Tests\TestCase;

class XmltvDownloaderTest extends TestCase
{
    public function test_downloads_http_and_https_xmltv(): void
    {
        foreach (['http', 'https'] as $scheme) {
            $path = $this->download([$this->response(200, '<tv/>')], $scheme.'://example.com/guide.xml');
            $this->assertSame('<tv/>', file_get_contents($path));
            @unlink($path);
        }
    }

    public function test_guard_blocks_private_local_link_local_and_metadata_targets(): void
    {
        foreach (['http://localhost/guide.xml', 'http://10.0.0.1/guide.xml', 'http://169.254.10.1/guide.xml', 'http://169.254.169.254/latest/meta-data'] as $url) {
            $guard = Mockery::mock(SsrfGuard::class);
            $guard->shouldReceive('assertAllowed')->once()->with($url)->andThrow(new StreamMonitoringSecurityException('blocked'));
            $client = Mockery::mock(ClientInterface::class);
            $client->shouldNotReceive('request');
            try {
                (new XmltvDownloader($guard, $client))->download($url);
                $this->fail('Blocked targets must not be downloaded.');
            } catch (EpgImportException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_each_redirect_is_validated_and_private_redirect_is_blocked(): void
    {
        $guard = Mockery::mock(SsrfGuard::class);
        $guard->shouldReceive('assertAllowed')->once()->with('https://example.com/guide.xml');
        $guard->shouldReceive('assertAllowed')->once()->with('http://127.0.0.1/private')->andThrow(new StreamMonitoringSecurityException('blocked'));
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')->once()->andReturn($this->response(302, '', ['Location' => 'http://127.0.0.1/private']));

        $this->expectException(EpgImportException::class);
        (new XmltvDownloader($guard, $client))->download('https://example.com/guide.xml');
    }

    public function test_rejects_excess_redirects_content_length_stream_overflow_and_non_xmltv(): void
    {
        config(['modules.epg.max_redirects' => 1, 'modules.epg.max_download_bytes' => 8]);
        foreach ([
            [$this->response(302, '', ['Location' => '/one']), $this->response(302, '', ['Location' => '/two'])],
            [$this->response(200, '<tv/>', ['Content-Length' => '9'])],
            [$this->response(200, '<tv>'.str_repeat('x', 20).'</tv>')],
            [$this->response(200, 'not xml')],
        ] as $responses) {
            try {
                $this->download($responses, 'https://example.com/guide.xml');
                $this->fail('Unsafe or oversized response should be rejected.');
            } catch (EpgImportException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /** @param list<Response> $responses */
    private function download(array $responses, string $url): string
    {
        $guard = Mockery::mock(SsrfGuard::class);
        $guard->shouldReceive('assertAllowed')->zeroOrMoreTimes();
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')->times(count($responses))->andReturn(...$responses);

        return (new XmltvDownloader($guard, $client))->download($url);
    }

    /** @param array<string, string> $headers */
    private function response(int $status, string $body, array $headers = []): Response
    {
        return new Response($status, $headers, Utils::streamFor($body));
    }
}
