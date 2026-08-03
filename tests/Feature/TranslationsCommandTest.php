<?php

use App\Console\Commands\ManageTranslationsCommand;

test('parse translation response handles ai output variations', function (string $response, ?array $expected) {
    $chunk = ['Hello' => 'Hello', 'World' => 'World'];
    $command = app(ManageTranslationsCommand::class);

    $result = (fn () => $this->parseTranslationResponse($response, $chunk))->call($command);

    expect($result)->toBe($expected);
})->with([
    'plain json' => [
        '{"Hello":"Bonjour","World":"Monde"}',
        ['Hello' => 'Bonjour', 'World' => 'Monde'],
    ],
    'json fenced with language' => [
        "```json\n{\"Hello\":\"Bonjour\",\"World\":\"Monde\"}\n```",
        ['Hello' => 'Bonjour', 'World' => 'Monde'],
    ],
    'json fenced without language' => [
        "```\n{\"Hello\":\"Bonjour\"}\n```",
        ['Hello' => 'Bonjour'],
    ],
    'invalid json' => [
        'sorry, I cannot translate that',
        null,
    ],
    'extra keys are filtered out' => [
        '{"Hello":"Bonjour","Unknown":"Nope"}',
        ['Hello' => 'Bonjour'],
    ],
]);
