<?php

namespace App\Ai\Agents;

use App\Ai\AiConfig;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

// Timeout is raised because some models reply very slow.
#[MaxTokens(5000)]
#[Timeout(120)]
class PunishmentInsightsAgent implements Agent, HasStructuredOutput
{
    use Promptable;

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
        return (string) view('gptprompts.punishment-insights', [
            'locale' => config('app.locale'),
        ]);
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'score' => $schema->integer()->min(1)->max(100)
                ->description('Severity score of the punishment, between 1 and 100.')
                ->required(),
            'insights' => $schema->array()->items($schema->string())
                ->description('5 insightful points regarding this punishment, each in short few words.')
                ->required(),
        ];
    }
}
