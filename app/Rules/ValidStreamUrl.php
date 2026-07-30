<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidStreamUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid stream URL.');

            return;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            $fail('The :attribute contains control characters.');

            return;
        }

        if (str_contains($value, "\r") || str_contains($value, "\n")) {
            $fail('The :attribute must not contain line breaks.');

            return;
        }

        $maxLength = (int) config('stream_security.max_url_length', 2048);
        if (mb_strlen($value) > $maxLength) {
            $fail('The :attribute exceeds the maximum allowed length.');

            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        $parts = parse_url($value);
        if (! is_array($parts)) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $allowedSchemes = array_values(array_unique(array_map(
            static fn (string $item): string => strtolower(trim($item)),
            (array) config('stream_security.allowed_schemes', ['https', 'http']),
        )));

        if ($scheme === '' || ! in_array($scheme, $allowedSchemes, true)) {
            $fail('The :attribute protocol is not allowed.');

            return;
        }

        $host = (string) ($parts['host'] ?? '');
        if ($host === '') {
            $fail('The :attribute host is required.');

            return;
        }

        $lowerHost = strtolower($host);
        if ($lowerHost === 'localhost') {
            $fail('The :attribute host is invalid.');

            return;
        }

        // Accept either valid domain or IP literal host.
        if (
            filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            && filter_var($host, FILTER_VALIDATE_IP) === false
        ) {
            $fail('The :attribute host is invalid.');

            return;
        }

        if (array_key_exists('port', $parts)) {
            $port = (int) $parts['port'];
            if ($port < 1 || $port > 65535) {
                $fail('The :attribute port is invalid.');

                return;
            }

            $allowedPorts = array_values(array_unique(array_map(
                static fn (mixed $item): int => (int) $item,
                (array) config('stream_security.allowed_ports', []),
            )));

            if ($allowedPorts !== [] && ! in_array($port, $allowedPorts, true)) {
                $fail('The :attribute port is not allowed.');

                return;
            }
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            if (! self::isGlobalIp($host)) {
                $fail('The :attribute IP address is not allowed.');

                return;
            }
        }
    }

    private static function isGlobalIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $long = ip2long($ip);
            if ($long === false) {
                return false;
            }

            $unsigned = sprintf('%u', $long);
            $ranges = [
                ['0.0.0.0', '0.255.255.255'],
                ['10.0.0.0', '10.255.255.255'],
                ['100.64.0.0', '100.127.255.255'],
                ['127.0.0.0', '127.255.255.255'],
                ['169.254.0.0', '169.254.255.255'],
                ['172.16.0.0', '172.31.255.255'],
                ['192.0.0.0', '192.0.0.255'],
                ['192.0.2.0', '192.0.2.255'],
                ['192.168.0.0', '192.168.255.255'],
                ['198.18.0.0', '198.19.255.255'],
                ['198.51.100.0', '198.51.100.255'],
                ['203.0.113.0', '203.0.113.255'],
                ['224.0.0.0', '239.255.255.255'],
                ['240.0.0.0', '255.255.255.255'],
            ];

            foreach ($ranges as [$start, $end]) {
                $startLong = sprintf('%u', ip2long($start));
                $endLong = sprintf('%u', ip2long($end));

                if ($unsigned >= $startLong && $unsigned <= $endLong) {
                    return false;
                }
            }

            return true;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $normalized = strtolower($ip);

            if ($normalized === '::1') {
                return false;
            }

            // Block local/link-local/unique-local/documentation/multicast/reserved ranges.
            $blockedPrefixes = [
                'fc',
                'fd',
                'fe8',
                'fe9',
                'fea',
                'feb',
                'ff',
                '2001:db8',
                '::',
            ];

            foreach ($blockedPrefixes as $prefix) {
                if (str_starts_with($normalized, $prefix)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
