<?php

namespace App\Services;

use OpenAI\Client;

class OpenAiService
{
    public function __construct(protected Client $client)
    {
    }

    public function simpleChat(string $systemPrompt, string $userPrompt, string|null $stop = null, float $temperature = 0.0, int $maxTokens = 1000, $responseFormat = 'text'): string
    {
        $aiModel = config('minetrax.openai_ai_model');
        $completions = $this->client->chat()->create([
            'model' => $aiModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'response_format' => [
                'type' => $responseFormat,
            ],
            'stop' => $stop,
        ]);

        return $completions->choices[0]->message->content;
    }
}
