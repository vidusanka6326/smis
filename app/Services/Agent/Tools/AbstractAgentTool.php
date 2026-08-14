<?php

namespace App\Services\Agent\Tools;

use App\Services\Agent\AgentTool;

abstract class AbstractAgentTool implements AgentTool
{
    /**
     * @return array<string, mixed>
     */
    protected function stringParam(string $description): array
    {
        return [
            'type' => 'STRING',
            'description' => $description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function integerParam(string $description): array
    {
        return [
            'type' => 'INTEGER',
            'description' => $description,
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
            'type' => 'OBJECT',
            'properties' => $properties,
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
}
