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

    /**
     * Provider/setup failures should render as a callout, not a chat bubble.
     */
    public function isServiceNotice(): bool
    {
        return $this->serviceNoticeVariant() !== null;
    }

    /**
     * Provider/setup failures should render as a callout, not a chat bubble.
     *
     * @return 'warning'|'danger'|null
     */
    public function serviceNoticeVariant(): ?string
    {
        if ($this->role !== AgentMessageRole::Assistant) {
            return null;
        }

        $content = $this->content;

        if (str_contains($content, 'credits')
            || str_contains($content, 'quota')
            || str_contains($content, 'rate-limited')
            || str_contains($content, 'not configured')
            || str_contains($content, 'rejected the API key')
            || str_contains($content, 'busy right now')
            || str_contains($content, 'overloaded')
            || str_contains($content, 'did not respond in time')) {
            return 'warning';
        }

        if (str_contains($content, 'could not complete')
            || str_contains($content, 'Gemini rejected')
            || str_contains($content, 'safety filters')
            || str_contains($content, 'model is not available')) {
            return 'danger';
        }

        return null;
    }
}
