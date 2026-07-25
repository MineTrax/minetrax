<?php

namespace App\Ai\Agents;

use App\Ai\AiConfig;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

// Timeout is raised because translating large chunks can be slow.
#[Temperature(0.3)]
#[MaxTokens(40960)]
#[Timeout(300)]
class TranslationAgent implements Agent
{
    use Promptable;

    public function __construct(protected string $localeName, protected string $rules = '') {}

    public function provider(): string
    {
        return AiConfig::provider();
    }

    public function model(): ?string
    {
        return AiConfig::model();
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
        You are a professional translator. Translate the given JSON key-value pairs from English to {$this->localeName}.

        Rules:
        - Return ONLY a valid JSON object with the same keys and translated values.
        - NEVER modify the JSON keys in any way. Keys must be returned character-for-character identical to the input, including all punctuation, colons, spaces, and special characters. Only translate the values.
        - Preserve all placeholders like :name, :count, :attribute exactly as they are.
        - Preserve any HTML tags exactly as they are.
        - Do not translate proper nouns unless they have well-known translations.
        - Do not add any explanation, markdown, or wrapping around the JSON.
        {$this->rules}
        PROMPT;
    }
}
