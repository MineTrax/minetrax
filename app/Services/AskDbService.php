<?php

namespace App\Services;

use EchoLabs\Prism\Facades\Tool;
use EchoLabs\Prism\ValueObjects\Messages\SystemMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

const STRICT_MODE = true;
const IGNORE_TABLES = [
    'migrations',
    'failed_jobs',
    'jobs_batches',
    'password_resets',
    'personal_access_tokens',
    'pulse_aggregates',
    'pulse_entries',
    'pulse_values',
    'telescope_entries',
    'telescope_entries_tags',
    'telescope_monitoring',
];
const TOOLS_RESPONSE_MAX_CHARACTERS = 100000;

class AskDbService
{
    protected string $connection;

    public function __construct(protected AiService $aiService)
    {
        $this->connection = config('database.default');
    }

    public function chatWithAskDbForUser(string $prompt, $user)
    {
        $messagesHistory = Cache::get("askdb::user_chat_session::{$user->id}", []);
        dd($messagesHistory);
        $systemPrompt = $this->generateSystemPrompt();

        if (count($messagesHistory) > 0) {
            // Pick last 20 messages from history to avoid using too many tokens when chat is long.
            $messagesHistory = array_slice($messagesHistory, -20);
            $messages = [
                ...$messagesHistory,
                new UserMessage($prompt),
            ];
        } else {
            $messages = [
                new SystemMessage($systemPrompt),
                new UserMessage($prompt),
            ];
        }

        $tools = [
            $this->currentTimeTool(),
            $this->getTablesTool(),
            $this->queryDatabaseTool(),
        ];
        $response = $this->aiService->chainPrompt(
            $messages,
            $tools,
            null,
            null,
            7,
        );

        $oneDayInSeconds = 60 * 60 * 24;
        Cache::put("askdb::user_chat_session::{$user->id}", $response->steps->last()->messages, $oneDayInSeconds);

        return $response;
    }

    private function currentTimeTool()
    {
        return Tool:: as('datetime')
            ->for('Get current date and time')
            ->withBooleanParameter('ignore', 'Dummy parameter')
            ->using(function (string $ignore): string {
                return now()->toISOString();
            });
    }

    private function getTablesTool()
    {
        return Tool:: as('get_tables_schema')
            ->for('Get table schema (rows, type and foreign keys) of the database')
            ->withBooleanParameter('ignore', 'Dummy parameter')
            ->using(function (string $ignore): string {
                $tables = $this->getTablesForTools();
                $resp = implode(PHP_EOL, $tables);
                if (strlen($resp) > TOOLS_RESPONSE_MAX_CHARACTERS) {
                    throw new \Exception('[Tools: get_tables_schema] response is too big to send to AI.');
                }
                return $resp;
            });
    }

    private function queryDatabaseTool()
    {
        return Tool:: as('query_database')
            ->for("
                Query the database for information.
                1. Only support sql_mode=only_full_group_by
                2. Need to get schema with get_tables_schema first
                3. You have read-only access to the database. Don't use DML queries.
            "
            )
            ->withStringParameter('query', 'SQL query to execute')
            ->using(function (string $query): string {
                $this->ensureQueryIsSafe($query);
                $result = $this->evaluateQueryForTools($query);
                $resp = json_encode($result);
                if (strlen($resp) > TOOLS_RESPONSE_MAX_CHARACTERS) {
                    throw new \Exception('[Tools: query_database] response is too big to send to AI.');
                }
                return $resp;
            });
    }

    private function getDialect(): string
    {
        $databasePlatform = Schema::getConnection()->getDriverTitle() . ' ' . Schema::getConnection()->getServerVersion();
        return $databasePlatform;
    }

    private function getTablesForTools(): array
    {
        $tables = Schema::getTableListing();

        return Cache::rememberForever('askdb::tables_cache', function () use ($tables) {
            $result = [];
            foreach ($tables as $table) {
                if (in_array($table, IGNORE_TABLES)) {
                    continue;
                }

                $columns = Schema::getColumns($table);
                $foreignKeys = Schema::getForeignKeys($table);

                // Start constructing the table's structure.
                $output = "<$table> columns: ";

                // Prepare columns in the format: column(type_name)
                $columnsOutput = [];
                foreach ($columns as $column) {
                    $columnsOutput[] = "{$column['name']}({$column['type_name']})";
                }
                $output .= implode(", ", $columnsOutput);

                // Collect foreign keys and format as: column -> foreign_table.foreign_column
                if (!empty($foreignKeys)) {
                    $foreignKeysOutput = [];
                    foreach ($foreignKeys as $fk) {
                        foreach ($fk['columns'] as $index => $column) {
                            $foreignKeysOutput[] = "$column -> {$fk['foreign_table']}.{$fk['foreign_columns'][$index]}";
                        }
                    }
                    $output .= " keys: " . implode(", ", $foreignKeysOutput);
                }

                // Add the table output to the result array
                $result[] = $output;
            }

            return $result;
        });
    }

    private function evaluateQueryForTools(string $query)
    {
        $rawQuery = DB::raw($query)->getValue(DB::connection($this->connection)->getQueryGrammar());
        return DB::connection($this->connection)->select($rawQuery) ?? new \stdClass();
    }

    private function ensureQueryIsSafe(string $query): void
    {
        if (!STRICT_MODE) {
            return;
        }

        $query = strtolower($query);
        $forbiddenWords = ['insert ', 'update ', 'delete ', 'alter ', 'drop ', 'truncate ', 'create ', 'replace '];
        throw_if(Str::contains($query, $forbiddenWords), new \Exception($query));
    }

    private function generateSystemPrompt()
    {
        $dialect = $this->getDialect();
        $prompt = (string) view('gptprompts.askdb-system-prompt', [
            'dialect' => $dialect,
        ]);
        return rtrim($prompt, PHP_EOL);
    }
}
