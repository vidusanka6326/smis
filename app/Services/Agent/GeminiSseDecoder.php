<?php

namespace App\Services\Agent;

class GeminiSseDecoder
{
    /**
     * Decode one SSE JSON payload into an LLM event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function eventFromPayload(array $payload): AgentLlmEvent
    {
        $parts = data_get($payload, 'candidates.0.content.parts', []);
        $text = '';
        $functionCalls = [];

        if (is_array($parts)) {
            foreach ($parts as $part) {
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
                        $functionCalls[] = [
                            'name' => $name,
                            'args' => is_array($args) ? $args : [],
                        ];
                    }
                }
            }
        }

        $finish = data_get($payload, 'candidates.0.finishReason');

        return new AgentLlmEvent(
            textDelta: $text !== '' ? $text : null,
            functionCalls: $functionCalls,
            complete: is_string($finish) && $finish !== '',
        );
    }

    /**
     * @return list<string>
     */
    public function dataLinesFromChunk(string $chunk): array
    {
        $lines = [];

        foreach (preg_split("/\r\n|\n|\r/", $chunk) ?: [] as $line) {
            if (str_starts_with($line, 'data:')) {
                $lines[] = trim(substr($line, 5));
            }
        }

        return $lines;
    }
}
