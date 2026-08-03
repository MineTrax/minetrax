<?php

namespace App\Ai\Tools;

use App\Ai\Support\AskDbDatabase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class QueryDatabaseTool implements Tool
{
    public function __construct(protected AskDbDatabase $database) {}

    public function name(): string
    {
        return 'query_database';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "
            Query the database for information.
            1. Only support sql_mode=only_full_group_by
            2. Need to get schema with get_tables_schema first
            3. You have read-only access to the database. Don't use DML queries.
            4. Database structure follow Laravel recommendations. (is for Laravel based project)
            5. Some relationship uses pivot table, Eg: players -> player_user -> users, define which player belongs to which user.
            6. Remember, `players` table have little changes in naming convension of columns compared to other player related tables, Eg: It has `uuid` instead of `player_uuid`, `username` instead of `player_username`.
        ";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->database->runSelect($request['query']);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('SQL query to execute')->required(),
        ];
    }
}
