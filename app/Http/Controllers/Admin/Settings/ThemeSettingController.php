<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\FontType;
use App\Enums\ThemeType;
use App\Http\Controllers\Controller;
use App\Settings\ThemeSettings;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

class ThemeSettingController extends Controller
{

    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(ThemeSettings $settings): \Inertia\Response
    {
        return Inertia::render('Admin/Setting/ThemeSetting', [
            'settings' => $settings->toArray(),
            'themeList' => ThemeType::asSelectArray(),
            'fontList' => FontType::asSelectArray(),
        ]);
    }

    public function update(Request $request, ThemeSettings $themeSettings)
    {
        $request->validate([
            'color_mode' => ['required', 'in:light,dark'],
            'theme_name' => ['required', new EnumValue(ThemeType::class)],
            'primary_font' => ['required', new EnumValue(FontType::class)],
            'secondary_font' => ['required', new EnumValue(FontType::class)],
            'enable_home_hero_section' => ['required', 'boolean'],
            'home_hero_bg_image_light' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'home_hero_bg_image_dark' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'home_hero_bg_size_css' => ['nullable', 'in:contain,cover,auto'],
            'home_hero_bg_height_css' => ['nullable', 'string'],
            'home_hero_bg_position_css' => ['nullable', 'in:left top,left center,left bottom,right top,right center,right bottom,center top,center center,center bottom'],
            'home_hero_bg_repeat_css' => ['nullable', 'in:repeat,repeat-x,repeat-y,no-repeat,round,space'],
            'home_hero_bg_attachment_css' => ['nullable', 'in:scroll,fixed,local'],
            'show_join_box_in_home_hero' => ['required', 'boolean'],
        ]);

        $themeSettings->color_mode = $request->color_mode;
        $themeSettings->theme_name = $request->theme_name;
        $themeSettings->primary_font = $request->primary_font;
        $themeSettings->secondary_font = $request->secondary_font;

        $themeSettings->enable_home_hero_section = $request->enable_home_hero_section;
        $themeSettings->home_hero_bg_size_css = $request->home_hero_bg_size_css;
        $themeSettings->home_hero_bg_height_css = $request->home_hero_bg_height_css;
        $themeSettings->home_hero_bg_position_css = $request->home_hero_bg_position_css;
        $themeSettings->home_hero_bg_repeat_css = $request->home_hero_bg_repeat_css;
        $themeSettings->home_hero_bg_attachment_css = $request->home_hero_bg_attachment_css;
        $themeSettings->show_join_box_in_home_hero = $request->show_join_box_in_home_hero;


        if ($request->hasFile('home_hero_bg_image_light')) {
            $path = Storage::putFileAs(
                'public', $request->file('home_hero_bg_image_light'), 'home_hero_bg_image_light.'.$request->file('home_hero_bg_image_light')->getClientOriginalExtension()
            );
            $themeSettings->home_hero_bg_image_path_light = Storage::url($path) . '?hash='. \Str::random(8);
        }

        if ($request->hasFile('home_hero_bg_image_dark')) {
            $path = Storage::putFileAs(
                'public', $request->file('home_hero_bg_image_dark'), 'home_hero_bg_image_dark.'.$request->file('home_hero_bg_image_dark')->getClientOriginalExtension()
            );
            $themeSettings->home_hero_bg_image_path_dark = Storage::url($path) . '?hash='. \Str::random(8);
        }

        $themeSettings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Theme Settings Updated Successfully')]]);
    }
}
