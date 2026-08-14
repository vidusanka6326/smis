<?php

namespace App\Services\Agent;

use App\Contracts\AgentLlm;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiAgentLlm implements AgentLlm
{
    public function __construct(private GeminiSseDecoder $decoder) {}

    public function isConfigured(): bool
    {
        $key = config('services.gemini.key');

        return is_string($key) && $key !== '';
    }

    public function streamTurn(array $contents, array $tools, string $systemInstruction): iterable
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Gemini is not configured.');
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 2048,
            ],
        ];

        if ($tools !== []) {
            $payload['tools'] = [
                ['functionDeclarations' => $tools],
            ];
        }

        $response = Http::timeout((int) config('services.gemini.timeout'))
            ->connectTimeout((int) config('services.gemini.connect_timeout'))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => (string) config('services.gemini.key'),
            ])
            ->withOptions(['stream' => true])
            ->withQueryParameters(['alt' => 'sse'])
            ->post($this->endpoint(), $payload);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';
        $pendingCalls = [];

        while (! $body->eof()) {
            $buffer .= $body->read(256);

            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $rawEvent = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);

                foreach ($this->decoder->dataLinesFromChunk($rawEvent) as $json) {
                    if ($json === '' || $json === '[DONE]') {
                        continue;
                    }

                    /** @var array<string, mixed>|null $decoded */
                    $decoded = json_decode($json, true);

                    if (! is_array($decoded)) {
                        continue;
                    }

                    $event = $this->decoder->eventFromPayload($decoded);

                    if ($event->functionCalls !== []) {
                        $pendingCalls = array_merge($pendingCalls, $event->functionCalls);
                    }

                    if ($event->textDelta !== null || $event->complete) {
                        yield new AgentLlmEvent(
                            textDelta: $event->textDelta,
                            functionCalls: [],
                            complete: false,
                        );
                    }
                }
            }
        }

        yield new AgentLlmEvent(
            functionCalls: $pendingCalls,
            complete: true,
        );
    }

    private function endpoint(): string
    {
        $base = rtrim((string) config('services.gemini.base_url'), '/');
        $model = (string) config('services.gemini.model');

        return "{$base}/models/{$model}:streamGenerateContent";
    }
}
