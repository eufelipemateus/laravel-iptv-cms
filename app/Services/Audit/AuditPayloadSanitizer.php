<?php

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditPayloadSanitizer
{
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
                $request?->fullUrl(),
                (int) config('audit.metadata.max_url_length', 2048),
            ),
            'ip_address' => $request?->ip(),
            'user_agent' => $this->truncate(
                $request?->userAgent(),
                (int) config('audit.metadata.max_user_agent_length', 1024),
            ),
        ];
    }

    private function truncate(?string $value, int $maximumLength): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::substr($value, 0, max(0, $maximumLength));
    }
}
