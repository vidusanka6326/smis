@props([
    'content' => '',
])

<div {{ $attributes->merge(['class' => 'agent-markdown']) }}>
    {!! app(\App\Services\Agent\AgentMarkdown::class)->render((string) $content) !!}
</div>
