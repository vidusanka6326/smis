<?php

namespace App\Services\Agent;

use App\Contracts\AgentLlm;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
            throw new GeminiRequestException('Gemini is not configured.');
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
            ->post($this->endpoint(), $payload);

        if ($response->failed()) {
            throw new GeminiRequestException($this->userMessageFromResponse($response));
        }

        $decoded = $response->json();

        if (! is_array($decoded)) {
            throw new GeminiRequestException(__('SMIS Agent could not complete that request. Please try again.'));
        }

        $blockReason = data_get($decoded, 'promptFeedback.blockReason');

        if (is_string($blockReason) && $blockReason !== '') {
            throw new GeminiRequestException(__('That request was blocked by Gemini safety filters. Rephrase and try again.'));
        }

        $event = $this->decoder->eventFromPayload($decoded);

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

    private function userMessageFromResponse(Response $response): string
    {
        return match ($response->status()) {
            401, 403 => __('Gemini rejected the API key. Check GEMINI_API_KEY and retry.'),
            404 => __('The configured Gemini model is not available. Set GEMINI_MODEL to gemini-flash-latest and retry.'),
            429 => __('Gemini credits or quota are exhausted. Add billing in Google AI Studio and retry.'),
            default => __('SMIS Agent could not complete that request. Please try again.'),
        };
    }
}
