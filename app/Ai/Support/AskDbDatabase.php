<?php

namespace App\Ai\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AskDbDatabase
{
    public const STRICT_MODE = true;

    public const IGNORE_TABLES = [
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

    public const TOOLS_RESPONSE_MAX_CHARACTERS = 100000;

    protected string $connection;

    public function __construct()
    {
        $this->connection = config('database.default');
    }

    public function dialect(): string
    {
        return Schema::getConnection()->getDriverTitle().' '.Schema::getConnection()->getServerVersion();
    }

    /**
     * Get a summary of all database tables (columns, types and foreign keys) for the AI.
     */
    public function tablesSummary(): string
    {
        $tables = $this->getTables();
        $summary = implode(PHP_EOL, $tables);
        if (strlen($summary) > self::TOOLS_RESPONSE_MAX_CHARACTERS) {
            throw new \Exception('[AskDB][Tools: get_tables_schema] response is too big to send to AI.');
        }

        return $summary;
    }

    /**
     * Run a read-only SQL query and return the result as JSON for the AI.
     */
    public function runSelect(string $query): string
    {
        Log::info("[AskDB: query_database]: $query");
        $this->ensureQueryIsSafe($query);

        $result = DB::connection($this->connection)->select($query);
        $response = json_encode($result);
        if (strlen($response) > self::TOOLS_RESPONSE_MAX_CHARACTERS) {
            throw new \Exception('[AskDB][Tools: query_database] response is too big to send to AI.');
        }

        return $response;
    }

    public function ensureQueryIsSafe(string $query): void
    {
        if (! self::STRICT_MODE) {
            return;
        }

        $query = strtolower($query);
        $forbiddenWords = ['insert ', 'update ', 'delete ', 'alter ', 'drop ', 'truncate ', 'create ', 'replace '];
        throw_if(Str::contains($query, $forbiddenWords), \Exception::class, $query);
    }

    /**
     * @return list<string>
     */
    protected function getTables(): array
    {
        // Limit to the connection's own database, some MySQL users can see every schema on the server.
        $tables = Schema::getTableListing(
            schema: DB::connection($this->connection)->getDatabaseName(),
            schemaQualified: false,
        );

        return Cache::rememberForever('askdb::tables_cache', function () use ($tables) {
            $result = [];
            foreach ($tables as $table) {
                if (in_array($table, self::IGNORE_TABLES)) {
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
                $output .= implode(', ', $columnsOutput);

                // Collect foreign keys and format as: column -> foreign_table.foreign_column
                if (! empty($foreignKeys)) {
                    $foreignKeysOutput = [];
                    foreach ($foreignKeys as $fk) {
                        foreach ($fk['columns'] as $index => $column) {
                            $foreignKeysOutput[] = "$column -> {$fk['foreign_table']}.{$fk['foreign_columns'][$index]}";
                        }
                    }
                    $output .= ' keys: '.implode(', ', $foreignKeysOutput);
                }

                // Add the table output to the result array
                $result[] = $output;
            }

            return $result;
        });
    }
}
