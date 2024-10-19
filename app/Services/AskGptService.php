<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenAI\Client;

const MAX_TABLES_BEFORE_PERFORMING_LOOKUP = 1000;
const STRICT_MODE = true;

class AskGptService
{
    protected string $connection;

    public function __construct(protected OpenAiService $openAiService)
    {
        $this->connection = config('database.default');
    }

    public function askDb(string $question, $userId = null): string
    {
        Log::info('[AskDB] User: ' . $userId . ' Question: ' . $question);

        $query = $this->getDbQuery($question);

        $result = json_encode($this->evaluateQuery($query));

        $systemPrompt = $this->buildSystemPromptForAskDb('gptprompts.askdb-result', $question, $query, $result);

        $answer = $this->openAiService->simpleChat($systemPrompt, $question, null, 0.5);

        return Str::of($answer)
            ->trim()
            ->trim('"');
    }

    public function getDbQuery(string $question): string
    {
        $systemPrompt = $this->buildSystemPromptForAskDb('gptprompts.askdb-query', $question);

        $query = $this->openAiService->simpleChat($systemPrompt, $question);
        $query = Str::of($query)
            ->trim()
            ->trim('"');

        $this->ensureQueryIsSafe($query);
        Log::info('[AskDB] Query: ' . $query);

        return $query;
    }

    public function chatBot(string $message): string
    {
        $systemPrompt = $this->buildSystemPromptForChat();
        $answer = $this->openAiService->simpleChat($systemPrompt, $message, null, 0.7);

        return Str::of($answer)->trim();
    }

    protected function buildSystemPromptForAskDb(string $promptView, string $question, string $query = null, string $result = null): string
    {
        $tables = $this->getTables($question);

        $prompt = (string) view($promptView, [
            'question' => $question,
            'tables' => $tables,
            'dialect' => $this->getDialect(),
            'query' => $query,
            'result' => $result,
        ]);

        return rtrim($prompt, PHP_EOL);
    }

    protected function buildSystemPromptForChat()
    {
        $prompt = (string) view('gptprompts.chatbot');
        return rtrim($prompt, PHP_EOL);
    }

    protected function evaluateQuery(string $query)
    {
        $rawQuery = DB::raw($query)->getValue(DB::connection($this->connection)->getQueryGrammar());
        return DB::connection($this->connection)->select($rawQuery) ?? new \stdClass();
    }

    /**
     */
    protected function ensureQueryIsSafe(string $query): void
    {
        if (!STRICT_MODE) {
            return;
        }

        $query = strtolower($query);
        $forbiddenWords = ['insert ', 'update ', 'delete ', 'alter ', 'drop ', 'truncate ', 'create ', 'replace '];
        throw_if(Str::contains($query, $forbiddenWords), new \Exception($query));
    }

    protected function getDialect(): string
    {
        $databasePlatform = DB::connection($this->connection)->getDoctrineConnection()->getDatabasePlatform();

        return Str::before(class_basename($databasePlatform), 'Platform');
    }

    protected function getTables(string $question): array
    {
        return once(function () use ($question) {
            $tables = DB::connection($this->connection)
                ->getDoctrineSchemaManager()
                ->listTables();

            if (count($tables) < MAX_TABLES_BEFORE_PERFORMING_LOOKUP) {
                return $tables;
            }

            return $this->filterMatchingTables($question, $tables);
        });
    }

    protected function filterMatchingTables(string $question, array $tables): array
    {
        $prompt = (string) view('gptprompts.askdb-tables', [
            'question' => $question,
            'tables' => $tables,
        ]);
        $prompt = rtrim($prompt, PHP_EOL);

        $matchingTablesResult = $this->openAiService->simpleChat($prompt, 'Relevant Table Names:', "\n");

        $matchingTables = Str::of($matchingTablesResult)
            ->explode(',')
            ->transform(fn(string $tableName) => strtolower(trim($tableName)));

        return collect($tables)->filter(function ($table) use ($matchingTables) {
            return $matchingTables->contains(strtolower($table->getName()));
        })->toArray();
    }
}
