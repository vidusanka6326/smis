<?php

namespace App\Services\Agent;

use App\Contracts\AgentLlm;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenRouterAgentLlm implements AgentLlm
{
    public function isConfigured(): bool
    {
        $key = config('services.openrouter.key');

        return is_string($key) && $key !== '';
    }

    public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
    {
        if (! $this->isConfigured()) {
            throw new AgentLlmException('OpenRouter is not configured.');
        }

        $payload = [
            'model' => (string) config('services.openrouter.model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemInstruction,
                ],
                ...$contents,
            ],
            'temperature' => 0.3,
            'max_tokens' => 2048,
        ];

        if ($tools !== []) {
            $payload['tools'] = array_map(
                fn (array $tool): array => [
                    'type' => 'function',
                    'function' => [
                        'name' => $tool['name'],
                        'description' => $tool['description'] ?? '',
                        'parameters' => $tool['parameters'] ?? ['type' => 'object', 'properties' => (object) []],
                    ],
                ],
                $tools,
            );
        }

        $response = Http::timeout((int) config('services.openrouter.timeout'))
            ->connectTimeout((int) config('services.openrouter.connect_timeout'))
            ->withToken((string) config('services.openrouter.key'))
            ->acceptJson()
            ->withHeaders([
                'HTTP-Referer' => (string) config('app.url'),
                'X-Title' => (string) config('app.name'),
            ])
            ->post($this->endpoint(), $payload);

        if ($response->failed()) {
            throw new AgentLlmException(
                $this->userMessageFromResponse($response),
                [
                    'openrouter_status' => $response->status(),
                    'openrouter_message' => $this->upstreamMessage($response),
                ],
            );
        }

        $decoded = $response->json();

        if (! is_array($decoded)) {
            throw new AgentLlmException(__('SMIS Agent could not complete that request. Please try again.'));
        }

        $message = data_get($decoded, 'choices.0.message');

        if (! is_array($message)) {
            throw new AgentLlmException(__('SMIS Agent could not complete that request. Please try again.'));
        }

        $text = $this->textFromContent($message['content'] ?? null);
        $functionCalls = $this->functionCallsFromMessage($message);

        if ($text !== null) {
            yield new AgentLlmEvent(
                textDelta: $text,
                complete: false,
            );
        }

        yield new AgentLlmEvent(
            functionCalls: $functionCalls,
            complete: true,
        );
    }

    private function endpoint(): string
    {
        return rtrim((string) config('services.openrouter.base_url'), '/').'/chat/completions';
    }

    /**
     * @param  array<string, mixed>  $message
     * @return list<array{id: string, name: string, args: array<string, mixed>}>
     */
    private function functionCallsFromMessage(array $message): array
    {
        $raw = $message['tool_calls'] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $calls = [];

        foreach ($raw as $index => $call) {
            if (! is_array($call)) {
                continue;
            }

            $name = data_get($call, 'function.name');

            if (! is_string($name) || $name === '') {
                continue;
            }

            $id = $call['id'] ?? null;
            $id = is_string($id) && $id !== '' ? $id : 'call_'.$index;

            $calls[] = [
                'id' => $id,
                'name' => $name,
                'args' => $this->decodeArguments(data_get($call, 'function.arguments')),
            ];
        }

        return $calls;
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

    private function textFromContent(mixed $content): ?string
    {
        if (is_string($content)) {
            $content = trim($content);

            return $content === '' ? null : $content;
        }

        if (! is_array($content)) {
            return null;
        }

        $parts = [];

        foreach ($content as $part) {
            if (is_string($part)) {
                $parts[] = $part;

                continue;
            }

            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $parts[] = $part['text'];
            }
        }

        $text = trim(implode('', $parts));

        return $text === '' ? null : $text;
    }

    private function userMessageFromResponse(Response $response): string
    {
        $upstream = $this->upstreamMessage($response);

        return match ($response->status()) {
            400 => $this->invalidArgumentMessage($upstream),
            401, 403 => __('OpenRouter rejected the API key. Check OPENROUTER_API_KEY and retry.'),
            402 => __('OpenRouter credits are exhausted. Add credits and retry.'),
            404 => __('The configured OpenRouter model is not available. Set OPENROUTER_MODEL to openai/gpt-oss-20b:free and retry.'),
            429 => __('OpenRouter is rate-limited. Wait a moment and retry.'),
            default => __('SMIS Agent could not complete that request. Please try again.'),
        };
    }

    private function invalidArgumentMessage(?string $upstream): string
    {
        if ((bool) config('app.debug') && is_string($upstream) && $upstream !== '') {
            return __('OpenRouter rejected the request: :error', [
                'error' => Str::limit($upstream, 280),
            ]);
        }

        return __('SMIS Agent could not complete that request. Please try again.');
    }

    private function upstreamMessage(Response $response): ?string
    {
        $message = data_get($response->json(), 'error.message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        $error = data_get($response->json(), 'error');

        return is_string($error) && $error !== '' ? $error : null;
    }
}
