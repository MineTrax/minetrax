<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\FontType;
use App\Enums\ThemeType;
use App\Http\Controllers\Controller;
use App\Settings\ThemeSettings;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeSettingController extends Controller
{

    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(ThemeSettings $settings): \Inertia\Response
    {
        return Inertia::render('Admin/Setting/ThemeSetting',[
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
            'secondary_font' => ['required', new EnumValue(FontType::class)]
        ]);

        $themeSettings->color_mode = $request->color_mode;
        $themeSettings->theme_name = $request->theme_name;
        $themeSettings->primary_font = $request->primary_font;
        $themeSettings->secondary_font = $request->secondary_font;

        $themeSettings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Theme Settings Updated Successfully')]]);
    }
}
