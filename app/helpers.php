<?php

if (! function_exists('env_csv')) {
    function env_csv(string $key, array $default = []): array
    {
        $value = env($key);

        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
