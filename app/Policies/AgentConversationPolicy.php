<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\AgentConversation;
use App\Models\User;

class AgentConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::UseSmisAgent->value);
    }

    public function view(User $user, AgentConversation $agentConversation): bool
    {
        return $user->can(PermissionName::UseSmisAgent->value)
            && $agentConversation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::UseSmisAgent->value);
    }

    public function update(User $user, AgentConversation $agentConversation): bool
    {
        return $this->view($user, $agentConversation);
    }

    public function delete(User $user, AgentConversation $agentConversation): bool
    {
        return $this->view($user, $agentConversation);
    }
}
