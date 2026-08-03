<?php

namespace App\Ai\Agents;

use App\Ai\AiConfig;
use App\Ai\Support\AskDbDatabase;
use App\Ai\Tools\CurrentDateTimeTool;
use App\Ai\Tools\DatabaseSchemaTool;
use App\Ai\Tools\QueryDatabaseTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

// Timeout is raised because some models reply very slow.
#[MaxSteps(50)]
#[Timeout(120)]
class AskDbAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(protected AskDbDatabase $database) {}

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
        $prompt = (string) view('gptprompts.askdb-system-prompt', [
            'dialect' => $this->database->dialect(),
        ]);

        return rtrim($prompt, PHP_EOL);
    }

    /**
     * Get the tools available to the agent.
     */
    public function tools(): iterable
    {
        return [
            new CurrentDateTimeTool,
            new DatabaseSchemaTool($this->database),
            new QueryDatabaseTool($this->database),
        ];
    }
}
