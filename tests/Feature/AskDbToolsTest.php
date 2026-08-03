<?php

use App\Ai\Support\AskDbDatabase;

test('run select blocks unsafe queries', function (string $query) {
    expect(fn () => (new AskDbDatabase)->runSelect($query))->toThrow(Exception::class);
})->with([
    'insert' => 'insert into users (name) values ("x")',
    'update' => 'update users set name = "x"',
    'delete' => 'delete from users',
    'alter' => 'alter table users add column hacked int',
    'drop' => 'drop table users',
    'truncate' => 'truncate table users',
    'create' => 'create table hacked (id int)',
    'replace' => 'replace into users (name) values ("x")',
]);

test('run select executes read-only query and returns json', function () {
    $result = (new AskDbDatabase)->runSelect('select 1 as one');

    expect(json_decode($result, true))->toBe([['one' => 1]]);
});

test('tables summary lists tables and excludes ignored ones', function () {
    $summary = (new AskDbDatabase)->tablesSummary();

    expect($summary)->toContain('<users> columns:')
        ->not->toContain('<migrations>');
});
