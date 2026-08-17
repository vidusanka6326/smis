<?php

namespace App\Services\Agent;

use App\Contracts\AgentLlm;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use stdClass;

class GeminiAgentLlm implements AgentLlm
{
    public function isConfigured(): bool
    {
        $key = config('services.gemini.key');

        return is_string($key) && $key !== '';
    }

    public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
    {
        if (! $this->isConfigured()) {
            throw new AgentLlmException('Gemini is not configured.');
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => $this->geminiContents($contents),
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 2048,
                'thinkingConfig' => [
                    'thinkingBudget' => 0,
                ],
            ],
        ];

        if ($tools !== []) {
            $payload['tools'] = [
                ['functionDeclarations' => array_map(
                    fn (array $tool): array => [
                        'name' => $tool['name'],
                        'description' => $tool['description'] ?? '',
                        'parameters' => $this->geminiSchema($tool['parameters'] ?? ['type' => 'object', 'properties' => new stdClass]),
                    ],
                    $tools,
                )],
            ];
        }

        try {
            $response = $this->postGenerateContent($payload);

            if (in_array($response->status(), [502, 503], true)) {
                $response = $this->postGenerateContent($payload);
            }
        } catch (ConnectionException $exception) {
            throw new AgentLlmException(
                __('Gemini did not respond in time. Wait a moment and retry.'),
                ['gemini_message' => $exception->getMessage()],
            );
        }

        if ($response->failed()) {
            throw new AgentLlmException(
                $this->userMessageFromResponse($response),
                [
                    'gemini_status' => $response->status(),
                    'gemini_message' => $this->upstreamMessage($response),
                ],
            );
        }

        $decoded = $response->json();

        if (! is_array($decoded)) {
            throw new AgentLlmException(__('SMIS Agent could not complete that request. Please try again.'));
        }

        $blockReason = data_get($decoded, 'promptFeedback.blockReason');

        if (is_string($blockReason) && $blockReason !== '') {
            throw new AgentLlmException(__('That request was blocked by Gemini safety filters. Rephrase and try again.'));
        }

        $event = $this->eventFromPayload($decoded);

        if ($event->textDelta !== null) {
            yield new AgentLlmEvent(
                textDelta: $event->textDelta,
                complete: false,
            );
        }

        yield new AgentLlmEvent(
            functionCalls: $event->functionCalls,
            complete: true,
        );
    }

    private function endpoint(): string
    {
        $base = rtrim((string) config('services.gemini.base_url'), '/');
        $model = (string) config('services.gemini.model');

        return "{$base}/models/{$model}:generateContent";
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postGenerateContent(array $payload): Response
    {
        return Http::timeout((int) config('services.gemini.timeout'))
            ->connectTimeout((int) config('services.gemini.connect_timeout'))
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => (string) config('services.gemini.key'),
            ])
            ->post($this->endpoint(), $payload);
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     * @return list<array<string, mixed>>
     */
    private function geminiContents(array $messages): array
    {
        $callNames = [];

        foreach ($messages as $message) {
            if (($message['role'] ?? '') !== 'assistant') {
                continue;
            }

            foreach ($message['tool_calls'] ?? [] as $call) {
                if (! is_array($call)) {
                    continue;
                }

                $id = $call['id'] ?? null;
                $name = data_get($call, 'function.name');

                if (is_string($id) && $id !== '' && is_string($name) && $name !== '') {
                    $callNames[$id] = $name;
                }
            }
        }

        $contents = [];
        $pendingResponses = [];

        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';

            if ($role === 'system') {
                continue;
            }

            if ($role === 'tool') {
                $id = $message['tool_call_id'] ?? '';
                $name = is_string($id) ? ($callNames[$id] ?? 'unknown') : 'unknown';
                $raw = $message['content'] ?? '{}';
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                $pendingResponses[] = [
                    'functionResponse' => [
                        'name' => $name,
                        'response' => $this->jsonObject(is_array($decoded) ? $decoded : ['result' => $raw]),
                    ],
                ];

                continue;
            }

            if ($pendingResponses !== []) {
                $contents[] = ['role' => 'user', 'parts' => $pendingResponses];
                $pendingResponses = [];
            }

            if ($role === 'assistant') {
                $parts = [];
                $text = $message['content'] ?? null;

                if (is_string($text) && $text !== '') {
                    $parts[] = ['text' => $text];
                }

                foreach ($message['tool_calls'] ?? [] as $call) {
                    if (! is_array($call)) {
                        continue;
                    }

                    $name = data_get($call, 'function.name');

                    if (! is_string($name) || $name === '') {
                        continue;
                    }

                    $part = [
                        'functionCall' => [
                            'name' => $name,
                            'args' => $this->jsonObject($this->decodeArguments(data_get($call, 'function.arguments'))),
                        ],
                    ];

                    $signature = $call['thoughtSignature'] ?? null;

                    if (is_string($signature) && $signature !== '') {
                        $part['thoughtSignature'] = $signature;
                    }

                    $parts[] = $part;
                }

                if ($parts !== []) {
                    $contents[] = ['role' => 'model', 'parts' => $parts];
                }

                continue;
            }

            $text = $message['content'] ?? '';
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => is_string($text) ? $text : '']],
            ];
        }

        if ($pendingResponses !== []) {
            $contents[] = ['role' => 'user', 'parts' => $pendingResponses];
        }

        return $contents;
    }

    /**
     * @return array<string, mixed>|stdClass
     */
    private function geminiSchema(mixed $schema): array|stdClass
    {
        if ($schema instanceof stdClass) {
            return $schema;
        }

        if (! is_array($schema)) {
            return ['type' => 'OBJECT', 'properties' => new stdClass];
        }

        $converted = [];

        foreach ($schema as $key => $value) {
            if ($key === 'type' && is_string($value)) {
                $converted[$key] = strtoupper($value);

                continue;
            }

            if ($key === 'properties') {
                if ($value instanceof stdClass || $value === []) {
                    $converted[$key] = new stdClass;
                } elseif (is_array($value)) {
                    $converted[$key] = [];

                    foreach ($value as $propName => $propSchema) {
                        $converted[$key][$propName] = $this->geminiSchema($propSchema);
                    }
                }

                continue;
            }

            if ($key === 'items') {
                $converted[$key] = $this->geminiSchema($value);

                continue;
            }

            $converted[$key] = $value;
        }

        return $converted;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function eventFromPayload(array $payload): AgentLlmEvent
    {
        $parts = data_get($payload, 'candidates.0.content.parts', []);
        $text = '';
        $functionCalls = [];

        if (is_array($parts)) {
            foreach ($parts as $index => $part) {
                if (! is_array($part)) {
                    continue;
                }

                if (isset($part['thought']) && $part['thought'] === true) {
                    continue;
                }

                if (isset($part['text']) && is_string($part['text'])) {
                    $text .= $part['text'];
                }

                if (isset($part['functionCall']) && is_array($part['functionCall'])) {
                    $name = $part['functionCall']['name'] ?? null;
                    $args = $part['functionCall']['args'] ?? [];

                    if (is_string($name) && $name !== '') {
                        $call = [
                            'id' => 'call_'.$index,
                            'name' => $name,
                            'args' => is_array($args) ? $args : [],
                        ];

                        $signature = $part['thoughtSignature'] ?? $part['thought_signature'] ?? null;

                        if (is_string($signature) && $signature !== '') {
                            $call['thoughtSignature'] = $signature;
                        }

                        $functionCalls[] = $call;
                    }
                }
            }
        }

        return new AgentLlmEvent(
            textDelta: $text !== '' ? $text : null,
            functionCalls: $functionCalls,
            complete: true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeArguments(mixed $arguments): array
    {
        if (is_array($arguments)) {
            /** @var array<string, mixed> $arguments */
            return $arguments;
        }

        if (! is_string($arguments) || $arguments === '') {
            return [];
        }

        $decoded = json_decode($arguments, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function userMessageFromResponse(Response $response): string
    {
        $upstream = $this->upstreamMessage($response);

        return match ($response->status()) {
            400 => $this->invalidArgumentMessage($upstream),
            401, 403 => __('Gemini rejected the API key. Check GEMINI_API_KEY and retry.'),
            404 => __('The configured Gemini model is not available. Set GEMINI_MODEL to gemini-flash-latest and retry.'),
            429 => __('Gemini credits or quota are exhausted. Add billing in Google AI Studio and retry.'),
            502, 503 => __('Gemini is busy right now. Wait a moment and retry.'),
            default => __('SMIS Agent could not complete that request. Please try again.'),
        };
    }

    private function invalidArgumentMessage(?string $upstream): string
    {
        if ((bool) config('app.debug') && is_string($upstream) && $upstream !== '') {
            return __('Gemini rejected the request: :error', [
                'error' => Str::limit($upstream, 280),
            ]);
        }

        return __('SMIS Agent could not complete that request. Please try again.');
    }

    private function upstreamMessage(Response $response): ?string
    {
        $message = data_get($response->json(), 'error.message');

        return is_string($message) && $message !== '' ? $message : null;
    }

    /**
     * Gemini Struct fields must be JSON objects. PHP empty arrays encode as [].
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|stdClass
     */
    private function jsonObject(array $data): array|stdClass
    {
        return $data === [] ? new stdClass : $data;
    }
}
