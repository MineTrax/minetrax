<?php

namespace App\Console\Commands;

use App\Services\AiService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ManageTranslationsCommand extends Command
{
    protected $signature = 'translations:manage
        {action : unused = find keys in en.json not used in code. missing = find keys in code not in en.json. sync = run both unused + missing. translate = AI-translate missing keys to target locale JSON files.}
        {--remove : Remove unused keys without confirmation}
        {--add : Add missing keys without confirmation}
        {--locale=* : Target locale(s) for translate action (e.g. --locale=hi --locale=fr). Omit for all.}';

    protected $description = 'Manage translation keys: find unused, find missing, sync, or translate locale JSON files using AI.';

    /** @var list<string> */
    // phpcs:ignore
    protected array $translationPatterns = [
        // __('key') and __("key")
        '/\_\_\(\s*\'([^\']+)\'\s*[\),]/',
        '/\_\_\(\s*"([^"]+)"\s*[\),]/',
        // trans('key') and trans("key")
        '/(?<!\w)trans\(\s*\'([^\']+)\'\s*[\),]/',
        '/(?<!\w)trans\(\s*"([^"]+)"\s*[\),]/',
        // trans_choice('key', ...)
        '/trans_choice\(\s*\'([^\']+)\'\s*,/',
        '/trans_choice\(\s*"([^"]+)"\s*,/',
        // Lang::get, Lang::choice, Lang::trans, Lang::transChoice
        '/Lang::(get|choice|trans|transChoice)\(\s*\'([^\']+)\'\s*[\),]/',
        '/Lang::(get|choice|trans|transChoice)\(\s*"([^"]+)"\s*[\),]/',
        // @lang('key') and @choice('key')
        '/@(lang|choice)\(\s*\'([^\']+)\'\s*[\),]/',
        '/@(lang|choice)\(\s*"([^"]+)"\s*[\),]/',
        // $t('key') and $t("key")
        '/\$t\(\s*\'([^\']+)\'\s*[\),]/',
        '/\$t\(\s*"([^"]+)"\s*[\),]/',
        // $trans.get('key')
        '/\$trans\.get\(\s*\'([^\']+)\'\s*[\),]/',
        '/\$trans\.get\(\s*"([^"]+)"\s*[\),]/',
        // Template literals: __(`key`), trans(`key`), $t(`key`)
        '/\_\_\(\s*`([^`\$]+)`\s*[\),]/',
        '/(?<!\w)trans\(\s*`([^`\$]+)`\s*[\),]/',
        '/\$t\(\s*`([^`\$]+)`\s*[\),]/',
    ];

    /** @var list<string> */
    protected array $fileExtensions = ['php', 'vue', 'js', 'ts', 'jsx', 'tsx'];

    /** @var list<string> */
    protected array $scanPaths = ['app', 'resources'];

    public function handle(): int
    {
        $action = $this->argument('action');

        if (!in_array($action, ['unused', 'missing', 'sync', 'translate'])) {
            $this->error('Invalid action. Use: unused, missing, sync, or translate');

            return self::FAILURE;
        }

        if ($action === 'translate') {
            return $this->handleTranslate();
        }

        $keysInCode = $this->scanCodeForKeys();
        $keysInJson = $this->getJsonTranslationKeys();

        $this->info("Found <comment>{$keysInCode->count()}</comment> translation keys in code.");
        $this->info("Found <comment>{$keysInJson->count()}</comment> keys in en.json.");
        $this->newLine();

        if (in_array($action, ['unused', 'sync'])) {
            $this->handleUnused($keysInCode, $keysInJson);
        }

        if (in_array($action, ['missing', 'sync'])) {
            $this->handleMissing($keysInCode, $keysInJson);
        }

        return self::SUCCESS;
    }

    /**
     * Scan all code files for translation keys.
     *
     * @return Collection<int, string>
     */
    protected function scanCodeForKeys(): Collection
    {
        $keys = collect();

        foreach ($this->scanPaths as $scanPath) {
            $fullPath = base_path($scanPath);
            if (!is_dir($fullPath)) {
                continue;
            }

            $files = $this->getFilesWithExtensions($fullPath);
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $keys = $keys->merge($this->extractKeysFromContent($content));
            }
        }

        return $keys->unique()->values();
    }

    /**
     * Get all files matching supported extensions in a directory.
     *
     * @return list<string>
     */
    protected function getFilesWithExtensions(string $directory): array
    {
        $pattern = '/\.(' . implode('|', $this->fileExtensions) . ')$/i';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files = new RegexIterator($iterator, $pattern);

        $result = [];
        foreach ($files as $file) {
            $path = $file->getPathname();
            // Skip vendor, node_modules, and backup directories
            if (preg_match('#[\\/](vendor|node_modules|backup)[\\/]#', $path)) {
                continue;
            }
            $result[] = $path;
        }

        return $result;
    }

    /**
     * Extract translation keys from file content.
     *
     * @return list<string>
     */
    protected function extractKeysFromContent(string $content): array
    {
        $keys = [];

        foreach ($this->translationPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                // Lang:: and @lang patterns capture the key in group 2, others in group 1
                $captureGroup = (str_contains($pattern, 'Lang::') || str_contains($pattern, '@(lang'))
                    ? 2
                    : 1;
                foreach ($matches[$captureGroup] as $key) {
                    $key = trim($key);
                    if ($key !== '' && !$this->isPhpVariable($key)) {
                        $keys[] = $key;
                    }
                }
            }
        }

        return $keys;
    }

    /**
     * Check if a string looks like a PHP variable or expression.
     */
    protected function isPhpVariable(string $key): bool
    {
        return str_starts_with($key, '$') || str_contains($key, '->');
    }

    /**
     * Get all keys from en.json.
     *
     * @return Collection<int, string>
     */
    protected function getJsonTranslationKeys(): Collection
    {
        $path = lang_path('en.json');
        if (!File::exists($path)) {
            $this->error('en.json not found!');

            return collect();
        }

        $translations = json_decode(File::get($path), true);

        return collect(array_keys($translations));
    }

    /**
     * Handle finding and optionally removing unused keys.
     *
     * @param  Collection<int, string>  $keysInCode
     * @param  Collection<int, string>  $keysInJson
     */
    protected function handleUnused($keysInCode, $keysInJson): void
    {
        $unused = $keysInJson->diff($keysInCode)->values();

        if ($unused->isEmpty()) {
            $this->info('No unused translation keys found.');

            return;
        }

        $this->warn("Found <comment>{$unused->count()}</comment> unused translation keys:");
        $this->newLine();

        $unused->each(function (string $key) {
            $this->line("  <fg=red>- </>{$key}");
        });

        $this->newLine();

        if ($this->option('remove') || $this->confirm("Remove these {$unused->count()} unused keys from all locale JSON files?")) {
            $this->removeUnusedKeys($unused);
        }
    }

    /**
     * Remove unused keys from all locale JSON files.
     *
     * @param  Collection<int, string>  $unusedKeys
     */
    protected function removeUnusedKeys($unusedKeys): void
    {
        $jsonFiles = glob(lang_path('*.json'));

        foreach ($jsonFiles as $file) {
            $translations = json_decode(File::get($file), true);
            $before = count($translations);

            foreach ($unusedKeys as $key) {
                unset($translations[$key]);
            }

            $after = count($translations);
            File::put($file, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

            $locale = basename($file);
            $removed = $before - $after;
            $this->info("  {$locale}: removed {$removed} keys ({$before} → {$after})");
        }

        $this->newLine();
        $this->info('Unused keys removed from all locale files.');
    }

    /**
     * Handle finding and optionally adding missing keys.
     *
     * @param  Collection<int, string>  $keysInCode
     * @param  Collection<int, string>  $keysInJson
     */
    protected function handleMissing($keysInCode, $keysInJson): void
    {
        $missing = $keysInCode->diff($keysInJson)->values();

        if ($missing->isEmpty()) {
            $this->info('No missing translation keys found.');

            return;
        }

        $this->warn("Found <comment>{$missing->count()}</comment> missing translation keys:");
        $this->newLine();

        $missing->each(function (string $key) {
            $this->line("  <fg=yellow>+ </>{$key}");
        });

        $this->newLine();

        if ($this->option('add') || $this->confirm("Add these {$missing->count()} missing keys to en.json (key = value)?")) {
            $this->addMissingKeys($missing);
        }
    }

    /**
     * Add missing keys to en.json with key as the default value.
     *
     * @param  Collection<int, string>  $missingKeys
     */
    protected function addMissingKeys($missingKeys): void
    {
        $path = lang_path('en.json');
        $translations = json_decode(File::get($path), true);

        foreach ($missingKeys as $key) {
            $translations[$key] = $key;
        }

        ksort($translations, SORT_NATURAL | SORT_FLAG_CASE);

        File::put($path, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

        $this->info("Added {$missingKeys->count()} missing keys to en.json.");
    }

    /**
     * Handle AI translation of missing keys for target locales.
     */
    protected function handleTranslate(): int
    {
        $enPath = lang_path('en.json');
        if (!File::exists($enPath)) {
            $this->error('en.json not found!');

            return self::FAILURE;
        }

        $enTranslations = json_decode(File::get($enPath), true);
        $targetLocales = $this->getTargetLocales();

        if (empty($targetLocales)) {
            $this->error('No target locale JSON files found.');

            return self::FAILURE;
        }

        $this->info('Source: <comment>en.json</comment> (' . count($enTranslations) . ' keys)');
        $this->info('Targets: <comment>' . implode(', ', $targetLocales) . '</comment>');
        $this->newLine();

        try {
            $aiService = app(AiService::class);
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $chunkSize = config('translations.chunk_size', 50);

        foreach ($targetLocales as $locale) {
            $this->translateLocale($aiService, $enTranslations, $locale, $chunkSize);
        }

        return self::SUCCESS;
    }

    /**
     * Get target locales from --locale option or auto-detect from existing JSON files.
     *
     * @return list<string>
     */
    protected function getTargetLocales(): array
    {
        $locales = $this->option('locale');

        if (!empty($locales)) {
            return $locales;
        }

        // Auto-detect from existing JSON files, excluding en.json
        $files = glob(lang_path('*.json'));

        return collect($files)
            ->map(fn($file) => basename($file, '.json'))
            ->reject(fn($locale) => $locale === 'en')
            ->values()
            ->all();
    }

    /**
     * Translate missing keys for a single locale.
     *
     * @param  array<string, string>  $enTranslations
     */
    protected function translateLocale(AiService $aiService, array $enTranslations, string $locale, int $chunkSize): void
    {
        $localePath = lang_path("{$locale}.json");
        $localeTranslations = File::exists($localePath)
            ? json_decode(File::get($localePath), true) ?? []
            : [];

        // Find keys in en.json that are missing in the target locale
        $missingKeys = array_diff_key($enTranslations, $localeTranslations);

        if (empty($missingKeys)) {
            $this->info("<comment>{$locale}</comment>: already up to date.");

            return;
        }

        $this->info("<comment>{$locale}</comment>: translating {$this->countKeys($missingKeys)} missing keys...");

        $chunks = array_chunk($missingKeys, $chunkSize, true);
        $translated = 0;

        $maxRetries = config('translations.retries', 5);

        foreach ($chunks as $index => $chunk) {
            $this->output->write('  Chunk ' . ($index + 1) . '/' . count($chunks) . '... ');

            $result = null;
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                $result = $this->translateChunk($aiService, $chunk, $locale);
                if ($result !== null) {
                    break;
                }
                if ($attempt < $maxRetries) {
                    $cooldown = $this->calculateCooldownSeconds($attempt);
                    $this->output->write("  Retrying ({$attempt}/{$maxRetries}) in {$cooldown} second(s)... ");
                    sleep($cooldown);
                }
            }

            if ($result === null) {
                $this->error("failed after {$maxRetries} attempts, skipping chunk.");

                continue;
            }

            foreach ($result as $key => $value) {
                $localeTranslations[$key] = $value;
            }

            $translated += count($result);
            $this->info('<comment>' . count($result) . '</comment> keys translated.');

            // Save after each chunk so progress is visible in the file
            ksort($localeTranslations, SORT_NATURAL | SORT_FLAG_CASE);
            File::put($localePath, json_encode($localeTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
        }

        $this->info("  <comment>{$locale}</comment>: {$translated} keys translated and saved.");
        $this->newLine();
    }

    /**
     * Translate a chunk of keys using AI.
     *
     * @param  array<string, string>  $chunk
     * @return array<string, string>|null
     */
    protected function translateChunk(AiService $aiService, array $chunk, string $locale): ?array
    {
        $localeName = $this->getLocaleName($locale);
        $rules = $this->getTranslationRules($locale);

        $systemPrompt = <<<PROMPT
        You are a professional translator. Translate the given JSON key-value pairs from English to {$localeName}.

        Rules:
        - Return ONLY a valid JSON object with the same keys and translated values.
        - NEVER modify the JSON keys in any way. Keys must be returned character-for-character identical to the input, including all punctuation, colons, spaces, and special characters. Only translate the values.
        - Preserve all placeholders like :name, :count, :attribute exactly as they are.
        - Preserve any HTML tags exactly as they are.
        - Do not translate proper nouns unless they have well-known translations.
        - Do not add any explanation, markdown, or wrapping around the JSON.
        {$rules}
        PROMPT;

        $userPrompt = json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $response = $aiService->simplePrompt(
                systemPrompt: $systemPrompt,
                userPrompt: $userPrompt,
                temperature: 0.3,
                maxTokens: 40960,
            );

            // Clean response - strip markdown code fences if present
            $response = preg_replace('/^```(?:json)?\s*/i', '', trim($response));
            $response = preg_replace('/\s*```$/', '', $response);

            $decoded = json_decode($response, true);
            if (!is_array($decoded)) {
                $this->error('failed (invalid JSON response from AI, skipping chunk)');

                return null;
            }

            // Only return keys that were in the original chunk
            return array_intersect_key($decoded, $chunk);
        } catch (\Exception $e) {
            $this->error("failed ({$e->getMessage()})");

            return null;
        }
    }

    /**
     * Get the human-readable name for a locale.
     */
    protected function getLocaleName(string $locale): string
    {
        $names = config('translations.locale_names', []);

        return $names[$locale] ?? locale_get_display_name($locale, 'en') ?: $locale;
    }

    /**
     * Get translation rules formatted for the AI prompt.
     */
    protected function getTranslationRules(string $locale): string
    {
        $allRules = config('translations.rules', []);
        $rules = $allRules['default'] ?? [];

        if (isset($allRules[$locale])) {
            $rules = array_merge($rules, $allRules[$locale]);
        }

        if (empty($rules)) {
            return '';
        }

        return collect($rules)->map(fn($rule) => "- {$rule}")->implode("\n");
    }

    /**
     * Calculate cooldown seconds for retry attempt with progressive backoff.
     * Progression: 1st attempt = 1s, 2nd = 5s, 3rd = 10s, 4th = 15s, etc.
     */
    protected function calculateCooldownSeconds(int $attempt): int
    {
        if ($attempt === 1) {
            return 1;
        }

        return 5 * $attempt - 5;
    }

    /**
     * Count keys in an array (avoids in_array IDE hint).
     *
     * @param  array<string, string>  $array
     */
    protected function countKeys(array $array): int
    {
        return count($array);
    }
}
