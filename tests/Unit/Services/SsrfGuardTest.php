<?php

namespace Tests\Unit\Services;

use App\Services\StreamMonitoring\SsrfGuard;
use App\Services\StreamMonitoring\StreamMonitoringSecurityException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsrfGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocks_localhost_private_and_cloud_metadata_targets(): void
    {
        config()->set('stream_security.monitoring.allowed_ports', [80, 443, 1935]);
        config()->set('stream_security.monitoring.block_localhost', true);
        config()->set('stream_security.monitoring.block_private_ips', true);
        config()->set('stream_security.monitoring.block_cloud_metadata', true);
        config()->set('stream_security.monitoring.blocked_cidrs', ['169.254.169.254/32']);

        $guard = app(SsrfGuard::class);

        $this->expectException(StreamMonitoringSecurityException::class);
        $guard->assertAllowed('http://localhost/live.m3u8');
    }

    public function test_blocks_private_ip_and_disallowed_port(): void
    {
        config()->set('stream_security.monitoring.allowed_ports', [80, 443]);
        config()->set('stream_security.monitoring.block_private_ips', true);

        $guard = app(SsrfGuard::class);

        $blockedPrivateIp = false;
        try {
            $guard->assertAllowed('http://10.0.0.5/live.m3u8');
        } catch (StreamMonitoringSecurityException $e) {
            $blockedPrivateIp = true;
        }
        $this->assertTrue($blockedPrivateIp, 'Expected private IP to be blocked.');

        $this->expectException(StreamMonitoringSecurityException::class);
        $guard->assertAllowed('https://example.test:8443/live.m3u8');
    }

    public function test_blocks_internal_redirect_chain_and_excessive_redirects(): void
    {
        config()->set('stream_security.monitoring.max_redirects', 2);
        config()->set('stream_security.monitoring.block_private_ips', true);
        config()->set('stream_security.monitoring.allowed_ports', [80, 443]);

        $guard = app(SsrfGuard::class);

        $blockedRedirectHop = false;
        try {
            $guard->assertAllowed('https://example.test/live.m3u8', [
                'https://safe.example/path',
                'http://172.16.0.12/secret',
            ]);
        } catch (StreamMonitoringSecurityException $e) {
            $blockedRedirectHop = true;
        }
        $this->assertTrue($blockedRedirectHop, 'Expected private redirect hop to be blocked.');

        $this->expectException(StreamMonitoringSecurityException::class);
        $guard->assertAllowed('https://example.test/live.m3u8', [
            'https://safe1.example/path',
            'https://safe2.example/path',
            'https://safe3.example/path',
        ]);
    }

    public function test_blocks_hostname_when_dns_resolves_to_private_or_metadata_ips(): void
    {
        config()->set('stream_security.monitoring.allowed_ports', [80, 443]);
        config()->set('stream_security.monitoring.block_private_ips', true);
        config()->set('stream_security.monitoring.block_cloud_metadata', true);
        config()->set('stream_security.monitoring.blocked_cidrs', ['169.254.169.254/32']);

        $guard = new class extends SsrfGuard
        {
            protected function resolveHostIps(string $host): array
            {
                if ($host === 'private.example') {
                    return ['10.10.1.20'];
                }

                if ($host === 'metadata.example') {
                    return ['169.254.169.254'];
                }

                return ['203.0.113.10'];
            }
        };

        $blockedPrivate = false;
        try {
            $guard->assertAllowed('https://private.example/playlist.m3u8');
        } catch (StreamMonitoringSecurityException $e) {
            $blockedPrivate = true;
        }

        $blockedMetadata = false;
        try {
            $guard->assertAllowed('https://metadata.example/playlist.m3u8');
        } catch (StreamMonitoringSecurityException $e) {
            $blockedMetadata = true;
        }

        $this->assertTrue($blockedPrivate, 'Expected DNS-resolved private IP host to be blocked.');
        $this->assertTrue($blockedMetadata, 'Expected DNS-resolved metadata IP host to be blocked.');
    }

    public function test_blocks_redirect_to_disallowed_scheme(): void
    {
        config()->set('stream_security.allowed_schemes', ['https', 'http']);
        config()->set('stream_security.monitoring.allowed_ports', [80, 443]);

        $guard = app(SsrfGuard::class);

        $this->expectException(StreamMonitoringSecurityException::class);
        $guard->assertAllowed('https://example.test/live.m3u8', [
            'ftp://redirect.example/stream',
        ]);
    }

    public function test_returns_monitoring_limits_from_config(): void
    {
        config()->set('stream_security.monitoring.connect_timeout_seconds', 2.5);
        config()->set('stream_security.monitoring.request_timeout_seconds', 7.0);
        config()->set('stream_security.monitoring.max_response_bytes', 4096);

        $guard = app(SsrfGuard::class);

        $this->assertSame(2.5, $guard->connectTimeoutSeconds());
        $this->assertSame(7.0, $guard->requestTimeoutSeconds());
        $this->assertSame(4096, $guard->maxResponseBytes());
    }
}
