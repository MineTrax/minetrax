<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public ?string $title_home;

    public ?string $title_suffix;

    public array $meta;

    public ?string $robots_txt;

    public ?string $inject_at_head;

    public ?string $inject_at_body_start;

    public ?string $inject_at_body_end;

    public ?string $favicon_path;

    public static function group(): string
    {
        return 'seo';
    }

    public function reset(): void
    {
        $this->title_home = null;
        $this->title_suffix = null;
        $this->meta = [];
        $this->robots_txt = null;
        $this->inject_at_head = null;
        $this->inject_at_body_start = null;
        $this->inject_at_body_end = null;
        $this->favicon_path = null;
        $this->save();
    }
}
