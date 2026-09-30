<?php

namespace App\Services\Audit;

use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class AuditSnapshotComparator
{
    public function __construct(private readonly AuditPayloadSanitizer $sanitizer) {}

    /** @param array<string, mixed> $snapshot */
    public function matches(Model $model, array $snapshot): bool
    {
        $expected = $this->comparableValues($model, $snapshot);
        $actual = $this->comparableValues(
            $model,
            Arr::only($model->getAttributes(), array_keys($expected)),
        );

        if (array_diff_key($expected, $actual) !== [] || array_diff_key($actual, $expected) !== []) {
            return false;
        }

        foreach ($expected as $attribute => $value) {
            if (! $this->valuesAreEquivalent($model, $attribute, $value, $actual[$attribute])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function comparableValues(Model $model, array $values): array
    {
        $values = $this->sanitizer->values($model, $values) ?? [];

        if ($model->usesTimestamps()) {
            unset($values[$model->getCreatedAtColumn()], $values[$model->getUpdatedAtColumn()]);
        }

        return $values;
    }

    private function valuesAreEquivalent(Model $model, string $attribute, mixed $expected, mixed $actual): bool
    {
        if ($expected === null || $actual === null) {
            return $expected === $actual;
        }

        $cast = strtolower(explode(':', (string) ($model->getCasts()[$attribute] ?? ''), 2)[0]);

        if (in_array($cast, ['bool', 'boolean'], true)
            || ($this->isBooleanLike($expected) && $this->isBooleanLike($actual)
                && (is_bool($expected) || is_bool($actual)))) {
            return filter_var($expected, FILTER_VALIDATE_BOOLEAN) === filter_var($actual, FILTER_VALIDATE_BOOLEAN);
        }

        if (in_array($cast, ['int', 'integer'], true)) {
            return (int) $expected === (int) $actual;
        }

        if (in_array($cast, ['real', 'float', 'double'], true)) {
            return abs((float) $expected - (float) $actual) < PHP_FLOAT_EPSILON * 4;
        }

        if (str_contains($cast, 'date') || $expected instanceof DateTimeInterface || $actual instanceof DateTimeInterface) {
            try {
                return Carbon::parse($expected)->equalTo(Carbon::parse($actual));
            } catch (InvalidFormatException) {
                return false;
            }
        }

        if (is_array($expected) || is_array($actual)) {
            return $expected === $actual;
        }

        return $expected === $actual
            || (is_scalar($expected) && is_scalar($actual) && (string) $expected === (string) $actual);
    }

    private function isBooleanLike(mixed $value): bool
    {
        return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);
    }
}
