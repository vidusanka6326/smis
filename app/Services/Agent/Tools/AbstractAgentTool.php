<?php

namespace App\Services\Agent\Tools;

use App\Enums\ActivityAction;
use App\Models\User;
use App\Services\Agent\AgentTool;
use App\Services\Audit\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class AbstractAgentTool implements AgentTool
{
    /**
     * @return array<string, mixed>
     */
    protected function stringParam(string $description): array
    {
        return [
            'type' => 'string',
            'description' => $description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function integerParam(string $description): array
    {
        return [
            'type' => 'integer',
            'description' => $description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function booleanParam(string $description): array
    {
        return [
            'type' => 'boolean',
            'description' => $description,
        ];
    }

    /**
     * @param  array<string, mixed>  $itemSchema
     * @return array<string, mixed>
     */
    protected function arrayParam(string $description, array $itemSchema): array
    {
        return [
            'type' => 'array',
            'description' => $description,
            'items' => $itemSchema,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $properties
     * @param  list<string>  $required
     * @return array<string, mixed>
     */
    protected function objectSchema(array $properties, array $required = []): array
    {
        $schema = [
            'type' => 'object',
            // Empty PHP arrays JSON-encode as lists. OpenAI-compatible APIs require `properties` to be a map.
            'properties' => $properties === [] ? (object) [] : $properties,
        ];

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function stringArg(array $arguments, string $key): ?string
    {
        $value = $arguments[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function intArg(array $arguments, string $key): ?int
    {
        $value = $arguments[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function floatArg(array $arguments, string $key): ?float
    {
        $value = $arguments[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function boolArg(array $arguments, string $key): ?bool
    {
        $value = $arguments[$key] ?? null;

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (! is_string($value)) {
            return null;
        }

        return match (Str::lower(trim($value))) {
            'true', '1', 'yes' => true,
            'false', '0', 'no' => false,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<int|string, mixed>
     */
    protected function arrayArg(array $arguments, string $key): array
    {
        $value = $arguments[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    protected function dateString(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function datetimeString(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function normalizedAction(array $arguments): string
    {
        return Str::lower((string) ($this->stringArg($arguments, 'action') ?? ''));
    }

    /**
     * @param  list<string>  $allowed
     * @return array<string, mixed>
     */
    protected function unknownAction(array $allowed): array
    {
        return [
            'ok' => false,
            'error' => 'Unknown or missing action. Allowed: '.implode(', ', $allowed).'.',
            'allowed_actions' => $allowed,
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    protected function logMutation(
        ActivityLogger $logger,
        User $user,
        string $description,
        ?Model $subject = null,
        array $properties = [],
    ): void {
        $logger->log(
            ActivityAction::AgentMutated,
            $description,
            $subject,
            [
                'tool' => $this->name(),
                ...$properties,
            ],
            $user,
        );
    }
}
