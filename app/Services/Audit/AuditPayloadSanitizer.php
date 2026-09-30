<?php

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class AuditPayloadSanitizer
{
    /** @var list<string> */
    private const DEFAULT_SENSITIVE_URL_PARAMETERS = ['token', 'password', 'secret', 'credential'];

    private const REDACTED_VALUE = '[REDACTED]';

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    public function values(Model $model, ?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $hidden = array_unique([
            ...config('audit.hidden_attributes', []),
            ...$model->getHidden(),
        ]);

        return array_diff_key($values, array_flip($hidden));
    }

    /** @return array{url: ?string, ip_address: ?string, user_agent: ?string} */
    public function requestMetadata(?Request $request): array
    {
        return [
            'url' => $this->truncate(
                $this->sanitizeUrl($request),
                (int) config('audit.metadata.max_url_length', 2048),
            ),
            'ip_address' => $request?->ip(),
            'user_agent' => $this->truncate(
                $request?->userAgent(),
                (int) config('audit.metadata.max_user_agent_length', 1024),
            ),
        ];
    }

    private function sanitizeUrl(?Request $request): ?string
    {
        if ($request === null) {
            return null;
        }

        $url = $request->getSchemeAndHttpHost().$this->sanitizePath($request);
        $query = $this->sanitizeQueryString($request);

        return $query === '' ? $url : "{$url}?{$query}";
    }

    private function sanitizePath(Request $request): string
    {
        $route = $request->route();

        if (! $route instanceof Route) {
            return $request->getPathInfo();
        }

        $path = '/'.ltrim($route->uri(), '/');

        foreach ($route->parameters() as $name => $value) {
            if ($this->isSensitiveParameterName((string) $name)) {
                continue;
            }

            $resolved = $value instanceof Model ? (string) $value->getKey() : (string) $value;

            $path = preg_replace(
                '/\{'.preg_quote((string) $name, '/').'\??\}/',
                rawurlencode($resolved),
                $path,
                1,
            ) ?? $path;
        }

        return $path;
    }

    private function sanitizeQueryString(Request $request): string
    {
        $query = $request->query();

        if ($query === []) {
            return '';
        }

        $sanitized = [];
        foreach ($query as $key => $value) {
            $sanitized[$key] = $this->isSensitiveParameterName((string) $key)
                ? self::REDACTED_VALUE
                : $value;
        }

        return http_build_query($sanitized);
    }

    private function isSensitiveParameterName(string $name): bool
    {
        $name = strtolower($name);
        $patterns = config('audit.metadata.sensitive_url_parameters', self::DEFAULT_SENSITIVE_URL_PARAMETERS);

        foreach ($patterns as $pattern) {
            if (str_contains($name, strtolower((string) $pattern))) {
                return true;
            }
        }

        return false;
    }

    private function truncate(?string $value, int $maximumLength): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::substr($value, 0, max(0, $maximumLength));
    }
}
