<?php

use App\Ai\Agents\PunishmentInsightsAgent;
use App\Jobs\GeneratePunishmentInsightsJob;
use App\Models\PlayerPunishment;

beforeEach(function () {
    config()->set('minetrax.banwarden.ai_insights_enabled', true);
    config()->set('ai.enabled', true);
    config()->set('ai.provider', 'openai');
    config()->set('ai.providers.openai.key', 'fake-key');
});

test('job generates structured punishment insights', function () {
    PunishmentInsightsAgent::fake([
        ['score' => 42, 'insights' => ['One', 'Two', 'Three', 'Four', 'Five']],
    ])->preventStrayPrompts();

    $punishment = PlayerPunishment::factory()->create();

    (new GeneratePunishmentInsightsJob($punishment))->handle();

    $punishment->refresh();
    expect($punishment->insights['status'])->toBe('generated')
        ->and($punishment->insights['score'])->toBe(42)
        ->and($punishment->insights['insights'])->toBe(['One', 'Two', 'Three', 'Four', 'Five']);

    PunishmentInsightsAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Punishment Details:'));
});

test('job generates insights when punishment has no ip address', function () {
    PunishmentInsightsAgent::fake([
        ['score' => 42, 'insights' => ['One', 'Two', 'Three', 'Four', 'Five']],
    ])->preventStrayPrompts();

    $punishment = PlayerPunishment::factory()->create(['ip_address' => null]);

    (new GeneratePunishmentInsightsJob($punishment))->handle();

    expect($punishment->refresh()->insights['status'])->toBe('generated')
        ->and($punishment->insights['score'])->toBe(42);
});

test('job skips when ai insights are disabled', function () {
    config()->set('minetrax.banwarden.ai_insights_enabled', false);
    PunishmentInsightsAgent::fake()->preventStrayPrompts();

    $punishment = PlayerPunishment::factory()->create();

    (new GeneratePunishmentInsightsJob($punishment))->handle();

    expect($punishment->refresh()->insights)->toBeNull();
    PunishmentInsightsAgent::assertNeverPrompted();
});

test('job skips when insights are already generated', function () {
    PunishmentInsightsAgent::fake()->preventStrayPrompts();

    $punishment = PlayerPunishment::factory()->create([
        'insights' => ['status' => 'generated', 'score' => 10, 'insights' => ['Old']],
    ]);

    (new GeneratePunishmentInsightsJob($punishment))->handle();

    expect($punishment->refresh()->insights['score'])->toBe(10);
    PunishmentInsightsAgent::assertNeverPrompted();
});

test('job fails before marking as generating when ai is misconfigured', function () {
    config()->set('ai.enabled', false);
    PunishmentInsightsAgent::fake()->preventStrayPrompts();

    $punishment = PlayerPunishment::factory()->create();

    expect(fn () => (new GeneratePunishmentInsightsJob($punishment))->handle())
        ->toThrow(Exception::class, 'AI feature is not enabled');

    expect($punishment->refresh()->insights)->toBeNull();
    PunishmentInsightsAgent::assertNeverPrompted();
});

test('job failure resets insights to null', function () {
    $punishment = PlayerPunishment::factory()->create([
        'insights' => ['status' => 'generating'],
    ]);

    (new GeneratePunishmentInsightsJob($punishment))->failed();

    expect($punishment->refresh()->insights)->toBeNull();
});
