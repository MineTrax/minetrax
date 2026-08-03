<?php

namespace App\Ai\Tools;

use App\Ai\Support\AskDbDatabase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DatabaseSchemaTool implements Tool
{
    public function __construct(protected AskDbDatabase $database) {}

    public function name(): string
    {
        return 'get_tables_schema';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get table schema (rows, type and foreign keys) of the database';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->database->tablesSummary();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
