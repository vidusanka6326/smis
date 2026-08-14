<?php

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentConversation>
 */
class AgentConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'title' => fake()->sentence(4),
        ];
    }
}
