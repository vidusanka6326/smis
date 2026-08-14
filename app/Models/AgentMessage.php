<?php

namespace App\Models;

use App\Enums\AgentMessageRole;
use Database\Factories\AgentMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AgentMessageRole $role
 * @property string $content
 * @property list<array{id: string, label: string, message: string}>|null $choices
 * @property list<array{name: string, ok: bool}>|null $tool_trace
 */
class AgentMessage extends Model
{
    /** @use HasFactory<AgentMessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'agent_conversation_id',
        'role',
        'content',
        'choices',
        'tool_trace',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => AgentMessageRole::class,
            'choices' => 'array',
            'tool_trace' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AgentConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AgentConversation::class, 'agent_conversation_id');
    }
}
