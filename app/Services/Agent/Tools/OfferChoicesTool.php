<?php

namespace App\Services\Agent\Tools;

use App\Models\User;

class OfferChoicesTool extends AbstractAgentTool
{
    public function name(): string
    {
        return 'offer_choices';
    }

    public function description(): string
    {
        return 'Present clickable follow-up choices to the user. Call this after answering so they can pick the next step (for example show free teachers, pick a timeslot, or assign a named teacher).';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'choices' => [
                'type' => 'ARRAY',
                'description' => 'Two to five choices.',
                'items' => $this->objectSchema([
                    'id' => $this->stringParam('Stable id such as show-free-teachers.'),
                    'label' => $this->stringParam('Short button label shown in the chat.'),
                    'message' => $this->stringParam('The user message to send when the button is clicked.'),
                ], ['id', 'label', 'message']),
            ],
        ], ['choices']);
    }

    public function authorized(User $user): bool
    {
        return true;
    }

    public function handle(User $user, array $arguments): array
    {
        $choices = [];

        foreach ($arguments['choices'] ?? [] as $choice) {
            if (! is_array($choice)) {
                continue;
            }

            $id = trim((string) ($choice['id'] ?? ''));
            $label = trim((string) ($choice['label'] ?? ''));
            $message = trim((string) ($choice['message'] ?? $label));

            if ($id === '' || $label === '' || $message === '') {
                continue;
            }

            $choices[] = [
                'id' => $id,
                'label' => $label,
                'message' => $message,
            ];
        }

        return [
            'ok' => true,
            'choices' => array_slice($choices, 0, 6),
        ];
    }
}
