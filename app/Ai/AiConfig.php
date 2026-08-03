<?php

namespace App\Ai;

class AiConfig
{
    public static function enabled(): bool
    {
        return (bool) config('ai.enabled');
    }

    public static function provider(): string
    {
        return config('ai.provider');
    }

    public static function model(): ?string
    {
        return config('ai.model') ?: null;
    }

    /**
     * Ensure AI is enabled and the configured provider has valid credentials, throwing a friendly exception otherwise.
     */
    public static function ensureConfigured(): void
    {
        if (! self::enabled()) {
            throw new \Exception('AI feature is not enabled. Please enable it in .env file (set AI_ENABLED=true).');
        }

        $provider = self::provider();
        $providerConfig = config("ai.providers.{$provider}");
        if (! $providerConfig) {
            $availableProviders = array_keys(config('ai.providers', []));
            throw new \Exception('Invalid AI_PROVIDER in .env, Available providers: '.implode(', ', $availableProviders));
        }

        if ($provider === 'ollama' && empty($providerConfig['url'])) {
            throw new \Exception('Ollama provider requires OLLAMA_URL to be set in .env');
        }

        // Providers that authenticate without an API key (local endpoints, AWS credential
        // provider chain, etc.) opt out via `'requires_key' => false` in config/ai.php.
        $requiresKey = $providerConfig['requires_key'] ?? true;
        if ($requiresKey && empty($providerConfig['key'])) {
            throw new \Exception("The [{$provider}] AI provider requires an API key. Please set its key in config/ai.php (or the matching .env variable).");
        }
    }
}
