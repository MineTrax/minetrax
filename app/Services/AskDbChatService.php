<?php

namespace App\Services;

use App\Ai\Agents\AskDbAgent;
use App\Ai\AiConfig;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Laravel\Ai\Responses\AgentResponse;

class AskDbChatService
{
    /**
     * A conversation is considered active while it has had activity within this window.
     * After this it is pruned and the next prompt starts a fresh conversation.
     */
    protected const CONVERSATION_LIFETIME_DAYS = 7;

    public function chat(string $prompt, User $user): AgentResponse
    {
        AiConfig::ensureConfigured();

        $this->pruneStaleConversations($user);

        $agent = AskDbAgent::make();
        $conversation = $this->activeConversation($user);

        return $conversation
            ? $agent->continue($conversation->id, as: $user)->prompt($prompt)
            : $agent->forUser($user)->prompt($prompt);
    }

    /**
     * Get the user's current chat history as a list of ['type' => 'user'|'assistant', 'content' => string] entries.
     *
     * @return list<array{type: string, content: string}>
     */
    public function history(User $user): array
    {
        $conversation = $this->activeConversation($user);
        if (! $conversation) {
            return [];
        }

        return ConversationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get(['role', 'content'])
            ->filter(fn (ConversationMessage $message) => filled($message->content))
            ->map(fn (ConversationMessage $message) => [
                'type' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();
    }

    public function reset(User $user): void
    {
        $this->deleteConversations($user->conversations());
    }

    /**
     * Get the user's most recent conversation that is still within its lifetime, if any.
     */
    protected function activeConversation(User $user): ?Conversation
    {
        return $user->conversations()
            ->where('updated_at', '>=', now()->subDays(self::CONVERSATION_LIFETIME_DAYS))
            ->latest('updated_at')
            ->first();
    }

    /**
     * Delete conversations (and their messages) that have fallen out of their lifetime window.
     */
    protected function pruneStaleConversations(User $user): void
    {
        $this->deleteConversations(
            $user->conversations()->where('updated_at', '<', now()->subDays(self::CONVERSATION_LIFETIME_DAYS))
        );
    }

    /**
     * Delete the given conversations along with all of their messages.
     *
     * @param  HasMany<Conversation, User>  $conversations
     */
    protected function deleteConversations(HasMany $conversations): void
    {
        $conversations->get()->each(function (Conversation $conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        });
    }
}
