<?php

namespace App\Services;

use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Text\Generator;
use EchoLabs\Prism\Text\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class AiService
{
    public Generator $client;
    private ?Provider $provider;
    private string $model;
    public function __construct()
    {
        $enabled = config('prism.enabled');
        $providerStr = config('prism.provider');
        $this->model = config('prism.model');

        // Error if not enabled.
        if (!$enabled) {
            throw new \Exception('AI feature is not enabled. Please enable it in .env file (set AI_ENABLED=true).');
        }

        // Check if provider is valid and exists in enum Provider
        $this->provider = Provider::tryFrom($providerStr);
        if (!$this->provider) {
            $providersArray = Arr::pluck(Provider::cases(), 'value');
            throw new \Exception('Invalid AI_PROVIDER in .env, Available providers: ' . implode(', ', $providersArray));
        }

        // Error if enabled but keys not set.
        $providerConfig = config("prism.providers.{$this->provider->value}");
        if ($this->provider == Provider::Ollama && !$providerConfig['url']) {
            throw new \Exception('Ollama provider requires OLLAMA_URL to be set in .env');
        } elseif (!$providerConfig['api_key']) {
            throw new \Exception(ucfirst($this->provider->value) . " provider requires " . strtoupper($this->provider->value) . "_API_KEY to be set in .env");
        }

        $this->client = Prism::text()->using($this->provider, $this->model);
    }

    public function simplePrompt(
        string $systemPrompt,
        string $userPrompt,
        float $temperature = null,
        int $maxTokens = 2048,
        array $providerConfig = [],
    ): string {
        $response = $this->client
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($userPrompt)
            ->withMaxTokens($maxTokens)
            ->usingTemperature($temperature)
            ->usingProviderConfig($providerConfig)
            ->generate();

        Log::info("[SimplePrompt] Prompt Tokens: {$response->usage->promptTokens} Completion Tokens: {$response->usage->completionTokens}");

        return $response->text;
    }

    public function chainPrompt(
        array $messages,
        array $tools = [],
        float $temperature = null,
        int $maxTokens = null,
        int $maxSteps = 2,
    ): Response {
        $reponse = $this->client
            ->withMessages($messages)
            ->usingTemperature($temperature)
            ->withMaxTokens($maxTokens)
            ->withMaxSteps($maxSteps)
            ->withTools($tools)
            ->withClientOptions(['timeout' => 120])  // Some model reply very slow, so we need to increase timeout.
            ->generate();

        return $reponse;
    }
}
