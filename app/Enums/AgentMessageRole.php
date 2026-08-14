<?php

namespace App\Enums;

enum AgentMessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
}
