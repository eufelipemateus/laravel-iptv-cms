<?php

namespace App\Services\StreamMonitoring;

class SsrfGuard
{
    /**
     * Validate a target URL and every redirect hop before making monitoring requests.
     *
     * @param array<int, string> $redirectChain
     */
    public function assertAllowed(string $url, array $redirectChain = []): void
    {
        $this->assertSingleUrlAllowed($url);

        $maxRedirects = (int) config('stream_security.monitoring.max_redirects', 3);
        if (count($redirectChain) > $maxRedirects) {
            throw new StreamMonitoringSecurityException('Too many redirects for stream monitoring request.');
        }

        foreach ($redirectChain as $hop) {
            $this->assertSingleUrlAllowed($hop);
        }
    }

    public function connectTimeoutSeconds(): float
    {
        return (float) config('stream_security.monitoring.connect_timeout_seconds', 5.0);
    }

    public function requestTimeoutSeconds(): float
    {
        return (float) config('stream_security.monitoring.request_timeout_seconds', 10.0);
    }

    public function maxResponseBytes(): int
    {
        return (int) config('stream_security.monitoring.max_response_bytes', 1024 * 1024);
    }

    private function assertSingleUrlAllowed(string $url): void
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            throw new StreamMonitoringSecurityException('Invalid stream URL for monitoring.');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $allowedSchemes = array_values(array_unique(array_map(
            static fn (string $item): string => strtolower(trim($item)),
            (array) config('stream_security.monitoring.allowed_schemes', config('stream_security.allowed_schemes', ['https', 'http'])),
        )));
        if ($scheme === '' || ! in_array($scheme, $allowedSchemes, true)) {
            throw new StreamMonitoringSecurityException('Protocol is blocked for stream monitoring.');
        }

        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        if ($host === '') {
            throw new StreamMonitoringSecurityException('Missing host in stream URL for monitoring.');
        }

        if ((bool) config('stream_security.monitoring.block_localhost', true) && $host === 'localhost') {
            throw new StreamMonitoringSecurityException('Localhost is blocked for stream monitoring.');
        }

        $blockedHostnames = array_map(
            static fn (string $item): string => strtolower(trim($item)),
            (array) config('stream_security.monitoring.blocked_hostnames', []),
        );
        if (in_array($host, $blockedHostnames, true)) {
            throw new StreamMonitoringSecurityException('Cloud metadata endpoint is blocked for stream monitoring.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $this->assertAllowedIp($host);
        } elseif (filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            throw new StreamMonitoringSecurityException('Invalid host in stream URL for monitoring.');
        } else {
            foreach ($this->resolveHostIps($host) as $resolvedIp) {
                $this->assertAllowedIp($resolvedIp);
            }
        }

        if (array_key_exists('port', $parts)) {
            $port = (int) $parts['port'];
            $allowedPorts = array_values(array_unique(array_map(
                static fn (mixed $item): int => (int) $item,
                (array) config('stream_security.monitoring.allowed_ports', []),
            )));

            if ($allowedPorts !== [] && ! in_array($port, $allowedPorts, true)) {
                throw new StreamMonitoringSecurityException('Port is blocked for stream monitoring.');
            }
        }
    }

    private function assertAllowedIp(string $ip): void
    {
        if ((bool) config('stream_security.monitoring.block_private_ips', true) && ! $this->isPublicIp($ip)) {
            throw new StreamMonitoringSecurityException('Private or local IP is blocked for stream monitoring.');
        }

        if ((bool) config('stream_security.monitoring.block_cloud_metadata', true)) {
            $blockedCidrs = (array) config('stream_security.monitoring.blocked_cidrs', []);
            foreach ($blockedCidrs as $cidr) {
                if ($this->ipInCidr($ip, (string) $cidr)) {
                    throw new StreamMonitoringSecurityException('Cloud metadata endpoint is blocked for stream monitoring.');
                }
            }
        }
    }

    /**
     * @return array<int, string>
     */
    protected function resolveHostIps(string $host): array
    {
        $resolvedIps = [];

        $aRecords = @dns_get_record($host, DNS_A);
        if (is_array($aRecords)) {
            foreach ($aRecords as $record) {
                $ip = (string) ($record['ip'] ?? '');
                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                    $resolvedIps[] = $ip;
                }
            }
        }

        if (defined('DNS_AAAA')) {
            $aaaaRecords = @dns_get_record($host, DNS_AAAA);
            if (is_array($aaaaRecords)) {
                foreach ($aaaaRecords as $record) {
                    $ip = (string) ($record['ipv6'] ?? '');
                    if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
                        $resolvedIps[] = $ip;
                    }
                }
            }
        }

        $fallbackIpv4 = @gethostbynamel($host);
        if (is_array($fallbackIpv4)) {
            foreach ($fallbackIpv4 as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                    $resolvedIps[] = $ip;
                }
            }
        }

        return array_values(array_unique($resolvedIps));
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr, 2);
        $maskBits = (int) $mask;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
        ) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);

            if ($ipLong === false || $subnetLong === false || $maskBits < 0 || $maskBits > 32) {
                return false;
            }

            $maskLong = $maskBits === 0 ? 0 : (-1 << (32 - $maskBits));

            return (($ipLong & $maskLong) === ($subnetLong & $maskLong));
        }

        return false;
    }
}
