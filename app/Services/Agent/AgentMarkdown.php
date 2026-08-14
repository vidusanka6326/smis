<?php

namespace App\Services\Agent;

use Illuminate\Support\Str;

class AgentMarkdown
{
    public function render(string $markdown): string
    {
        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
