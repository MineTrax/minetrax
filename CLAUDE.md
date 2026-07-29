<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- laravel/telescope (TELESCOPE) - v5
- livewire/livewire (LIVEWIRE) - v4
- tightenco/ziggy (ZIGGY) - v2
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v5
- phpunit/phpunit (PHPUNIT) - v13
- rector/rector (RECTOR) - v2
- @inertiajs/vue3 (INERTIA_VUE) - v3
- eslint (ESLINT) - v10
- laravel-echo (ECHO) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== spatie/laravel-medialibrary rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>

# Project Instructions

## Frontend Bundling

- Do not run `npm run build` or `npm run dev`. The dev server is already running and will pick up changes automatically.

## Running Tests Fast

The suite is 928 tests (924 pass, 4 skip) over 91 migrations. **Use `--tia`** — it is set up and verified here, and turns a whole-suite check into ~30s instead of ~6 minutes.

```bash
php -d memory_limit=3G vendor/bin/pest --compact --tia
```

- Never run two test processes at once. Every run shares the single `minetrax` test database (set in `phpunit.xml`), so a second run wipes the first one's data mid-flight. Wait for one to finish.
- **Pass `-d memory_limit=3G` and invoke `vendor/bin/pest` directly.** `php artisan test` spawns a child process that does not inherit the limit, and the default 512M is exhausted whenever a run produces a lot of failures — Laravel Nightwatch's `ExceptionSensor` serialises source frames for every exception, so a broken run dies with an OOM instead of printing which tests failed.
- All test files are Pest functional style (`it()` / `test()`). **Do not add a `class FooTest extends TestCase`** — a single PHPUnit-style class makes `--tia` abort with `Tia mode requires Pest tests`.

## Tia Engine (Test Impact Analysis)

`--tia` records which files each test touches, then replays cached results for tests no changed file can affect. It needs a coverage driver; PCOV 1.0.12 is installed for PHP 8.4 (thread-safe build, matching the ZTS runtime).

Measured on this repo:

| Situation | Result |
|---|---|
| First run (records baseline) | full cost |
| No change | 928 replayed, **~31s** |
| Comment/whitespace-only edit | 928 replayed, **~31s** (normalised, no invalidation) |
| One service changed | 257 affected re-run, 671 replayed, ~104s — and it **caught all 6 real failures** |
| Without `--tia` | 371s measured before the socket fix below; expect meaningfully less now, but it has not been re-measured |

- A change re-runs the affected tests for real, so a green `--tia` run is trustworthy. It does not serve a stale pass.
- Reverting a change counts as another change: the affected tests re-run once more, then the next run settles back to full replay.
- Flags: `--tia --filtered` runs only affected files, `--tia --fresh` discards the graph and re-records, `--no-tia` forces a full run, `--baseline` prints the cache directory.
- State lives outside the repo in `~/.pest/tia/<project-key>/`, keyed off the git remote, so nothing needs gitignoring. **If a run is killed midway it leaves a partial graph** — delete that directory and re-record rather than trusting a replay from it.
- The graph auto-rebuilds when `composer.lock`, `phpunit.xml`, `vite.config.*`, a node lockfile, `tsconfig`/`jsconfig`, or the PHP version changes.
- To enable permanently, add `pest()->tia()->always();` to `tests/Pest.php`, or set `PEST_TIA=1`.
- Still narrow the run when you already know the target: `--filter=someTest` or a path is fine and needs no coverage driver.

## Tests That Dispatch Store Commands

`QUEUE_CONNECTION=sync` in tests, so `RunCommandQueueJob` executes inline and opens a **real TCP socket** to the `Server::factory()` host with a 10s connect timeout (`MinecraftWebQuery`). That is 10s of dead wait per dispatched command.

Any test that creates a `Server` and dispatches package commands must fake the job:

```php
Queue::fake([RunCommandQueueJob::class]);
```

The `command_queues` and `store_order_deliveries` rows are written *before* dispatch, so assertions on them still hold. Only assert on the queue row's post-run `status`/`output` if you actually let the job run. Four files were costing ~180s between them before this was added.

## Known Slow Spot

`AskDbToolsTest > tables summary` takes ~39s on its own: `AskDbDatabase::getTables()` calls `Schema::getColumns()` and `Schema::getForeignKeys()` per table, which is ~180 `information_schema` queries across the 90-table schema. Pre-existing, not a test bug — do not "fix" it by weakening the assertion.

## Verifying Behaviour With `--agent`

`pest-plugin-agent` runs an arbitrary snippet inside a real Feature test — with `RefreshDatabase`, factories, and Laravel fakes all live. Use it instead of `php artisan tinker` when you need to prove a code path actually works, since tinker hits the real dev database and has no transaction rollback.

```bash
./vendor/bin/pest --agent='$u = \App\Models\User::factory()->create(); $this->actingAs($u)->get("/dashboard")->assertOk();'
```

- Single-quote the whole snippet; use double quotes for PHP strings inside it.
- Use fully qualified class names (`\App\Models\User`) — no `use` imports are generated.
- Directory-scoped `beforeEach()` hooks do not apply. Inline any setup you need.
- Each `--agent` costs ~25s here, nearly all of it `RefreshDatabase` migrations. Batch related assertions into one snippet rather than running several probes.
- This is a verification probe, not a substitute for a committed test. Per the testing rules above, still write a real test for the change.
