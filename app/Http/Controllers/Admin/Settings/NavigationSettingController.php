<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Models\Download;
use App\Settings\GeneralSettings;
use App\Settings\NavigationSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NavigationSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(NavigationSettings $settings, GeneralSettings $generalSettings)
    {
        // Items which can be added to custom navbar
        $availableNavItems = config('minetrax.custom_nav_available_items_array');

        // Custom pages which can be added
        $customPageItems = CustomPage::select(['id', 'title', 'path', 'is_open_in_new_tab'])->get();
        foreach ($customPageItems as $item) {
            $availableNavItems[] = [
                'type' => 'custom-page',
                'name' => $item->title,
                'title' => $item->title,
                'id' => $item->id,
                'route' => 'custom-page.show',
                'route_params' => [
                    'path' => $item->path,
                ],
                'is_open_in_new_tab' => $item->is_open_in_new_tab,
                'key' => 'custom-page-'.$item->id,
            ];
        }
        // Downloads which can be added
        $downloadItems = Download::select(['id', 'name', 'slug'])->get();
        foreach ($downloadItems as $item) {
            $availableNavItems[] = [
                'type' => 'download',
                'name' => $item->name,
                'title' => $item->name,
                'id' => $item->id,
                'route' => 'download.show',
                'route_params' => [
                    'download' => $item->slug,
                ],
                'is_open_in_new_tab' => false,
                'key' => 'download-'.$item->id,
            ];
        }

        return Inertia::render('Admin/Setting/NavigationSetting', [
            'settings' => $settings->toArray(),
            'generalSettings' => $generalSettings->toArray(),
            'availableNavItems' => $availableNavItems,
        ]);
    }

    public function update(Request $request, NavigationSettings $settings, GeneralSettings $generalSettings)
    {
        $request->validate([
            'enable_custom_navbar' => 'required|boolean',
            'custom_navbar_data' => 'nullable|array',
            'enable_sticky_header_menu' => 'required|boolean',
            'enable_custom_footer' => 'required|boolean',
            'custom_footer_data.site_moto' => 'nullable|string',
            'custom_footer_data.style' => 'required_if:enable_custom_footer,true|in:variant_1,variant_2',
            'custom_footer_data.columns' => 'nullable|array',
            'custom_footer_data.columns.*' => 'nullable|array',
            'custom_footer_data.columns.*.title' => 'nullable|string',
            'custom_footer_data.columns.*.items' => 'nullable|array',
            'custom_footer_data.columns.*.items.*' => 'nullable|array',
            'custom_footer_data.columns.*.items.*.url' => 'nullable|string',
            'custom_footer_data.columns.*.items.*.title' => 'required_with:custom_footer_data.columns.*.items.*.url|nullable|string',
        ]);

        $navbarData = $request->input('custom_navbar_data');

        // Check in array if we have a dropdown type inside a dropdown type then we give error
        foreach ($navbarData as $sideList) {
            foreach ($sideList as $item) {
                if ($item['type'] === 'dropdown') {
                    foreach ($item['children'] as $child) {
                        if ($child['type'] === 'dropdown' || $child['type'] === 'component') {
                            return redirect()->back()
                                ->with(['toast' => ['type' => 'error', 'title' => __('You can not add a dropdown or component inside of a dropdown')]])
                                ->withErrors(['custom_navbar_data' => __('You can not add a dropdown or component inside of a dropdown')]);
                        }
                    }
                }
            }
        }

        $settings->enable_custom_navbar = $request->input('enable_custom_navbar');
        $settings->custom_navbar_data = $navbarData;
        $settings->enable_custom_footer = $request->input('enable_custom_footer');
        $settings->custom_footer_data = $request->input('custom_footer_data');
        $settings->save();

        $generalSettings->enable_sticky_header_menu = $request->input('enable_sticky_header_menu');
        $generalSettings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Navigation Settings Updated Successfully')]]);
    }
}
