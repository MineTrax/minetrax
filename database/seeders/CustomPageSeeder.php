<?php

namespace Database\Seeders;

use App\Models\CustomPage;
use Illuminate\Database\Seeder;

class CustomPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countriesInDb = CustomPage::exists();
        if ($countriesInDb) {
            return;
        }

        $body = file_get_contents(resource_path('markdown/rules.md'));
        CustomPage::create([
            'title' => 'Rules',
            'path' => 'rules',
            'body' => $body,
            'is_visible' => true,
            'is_in_navbar' => true
        ]);

        $body = file_get_contents(resource_path('markdown/policy.md'));
        CustomPage::create([
            'title' => 'Privacy Policy',
            'path' => 'privacy-policy',
            'body' => $body,
            'is_visible' => false,
            'is_in_navbar' => true
        ]);

        $body = file_get_contents(resource_path('markdown/terms.md'));
        CustomPage::create([
            'title' => 'Terms of Service',
            'path' => 'terms',
            'body' => $body,
            'is_visible' => false,
            'is_in_navbar' => true
        ]);
    }
}
