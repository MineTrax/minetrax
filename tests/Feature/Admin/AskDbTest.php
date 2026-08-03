<?php

use App\Ai\Agents\AskDbAgent;
use App\Models\User;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Laravel\Ai\Responses\Data\ToolCall;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first();

    config()->set('minetrax.askdb_enabled', true);
    config()->set('ai.enabled', true);
    config()->set('ai.provider', 'openai');
    config()->set('ai.providers.openai.key', 'fake-key');
});

test('guest is redirected from askdb page', function () {
    $this->get(route('admin.ask-db.index'))
        ->assertRedirect();
});

test('staff member without permission cannot access askdb', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $this->actingAs($moderator)
        ->get(route('admin.ask-db.index'))
        ->assertForbidden();

    $this->actingAs($moderator)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'how many players?'])
        ->assertForbidden();
});

test('askdb query returns 403 when feature is disabled', function () {
    config()->set('minetrax.askdb_enabled', false);

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'how many players?'])
        ->assertForbidden();
});

test('askdb query returns ai response as html with usage', function () {
    AskDbAgent::fake(['Hello **there**'])->preventStrayPrompts();

    $response = $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'How many players joined today?'])
        ->assertSuccessful()
        ->assertJsonPath('data.type', 'assistant')
        ->assertJsonPath('data.usage.promptTokens', 0)
        ->assertJsonPath('data.usage.completionTokens', 0)
        ->assertJsonPath('data.toolCalls', []);

    expect($response->json('data.content'))->toContain('<strong>there</strong>');

    AskDbAgent::assertPrompted(fn ($prompt) => $prompt->prompt === 'How many players joined today?');

    expect(Conversation::count())->toBe(1)
        ->and(ConversationMessage::where('role', 'user')->count())->toBe(1)
        ->and(ConversationMessage::where('role', 'assistant')->count())->toBe(1)
        ->and(Conversation::first()->user_id)->toBe($this->superAdmin->id);
});

test('askdb query returns tools used by the agent', function () {
    AskDbAgent::fake([
        new ToolCall('tool_1', 'query_database', ['query' => 'select 1 as one']),
        'There is **one** row',
    ])->preventStrayPrompts();

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'How many rows?'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.toolCalls')
        ->assertJsonPath('data.toolCalls.0.name', 'query_database')
        ->assertJsonPath('data.toolCalls.0.arguments.query', 'select 1 as one')
        ->assertJsonPath('data.toolCalls.0.result', '[{"one":1}]');
});

test('consecutive askdb queries continue the same conversation', function () {
    AskDbAgent::fake(['First answer', 'Second answer'])->preventStrayPrompts();

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'First question'])
        ->assertSuccessful();

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'Second question'])
        ->assertSuccessful();

    expect(Conversation::count())->toBe(1)
        ->and(ConversationMessage::count())->toBe(4);
});

test('askdb index renders chat history', function () {
    AskDbAgent::fake(['Hello **there**'])->preventStrayPrompts();

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'How many players joined today?'])
        ->assertSuccessful();

    $this->actingAs($this->superAdmin)
        ->get(route('admin.ask-db.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Admin/AskDb/IndexAskDb', false)
            ->where('featureEnabled', true)
            ->has('chatHistory', 2)
            ->where('chatHistory.0.type', 'user')
            ->where('chatHistory.0.content', 'How many players joined today?')
            ->where('chatHistory.1.type', 'assistant')
            ->where('chatHistory.1.content', fn ($content) => str_contains($content, '<strong>there</strong>')));
});

test('askdb reset clears conversation history', function () {
    AskDbAgent::fake(['Hello **there**'])->preventStrayPrompts();

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'How many players joined today?'])
        ->assertSuccessful();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.ask-db.reset'))
        ->assertRedirect();

    expect(Conversation::count())->toBe(0)
        ->and(ConversationMessage::count())->toBe(0);

    $this->actingAs($this->superAdmin)
        ->get(route('admin.ask-db.index'))
        ->assertInertia(fn ($page) => $page->has('chatHistory', 0));
});

test('askdb query validates prompt', function (array $payload) {
    AskDbAgent::fake()->preventStrayPrompts();

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('prompt');
})->with([
    'missing prompt' => [[]],
    'too long prompt' => [['prompt' => str_repeat('a', 1001)]],
]);

test('askdb query returns friendly error when ai is misconfigured', function () {
    config()->set('ai.enabled', false);

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.ask-db.query'), ['prompt' => 'How many players joined today?'])
        ->assertInternalServerError()
        ->assertJsonPath('message', 'Failed processing your request! Try again after rephrasing your question.');
});
