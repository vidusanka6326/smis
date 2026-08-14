<?php

namespace Database\Factories;

use App\Enums\AgentMessageRole;
use App\Models\AgentConversation;
use App\Models\AgentMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentMessage>
 */
class AgentMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_conversation_id' => AgentConversation::factory(),
            'role' => AgentMessageRole::User,
            'content' => fake()->sentence(),
            'choices' => null,
            'tool_trace' => null,
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => AgentMessageRole::Assistant,
        ]);
    }
}
