<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Settings\SeoSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

class SeoSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(SeoSettings $settings): \Inertia\Response
    {
        return Inertia::render('Admin/Setting/SeoSetting', [
            'settings' => $settings->toArray(),
        ]);
    }

    public function update(Request $request, SeoSettings $settings): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title_home' => 'nullable|string',
            'title_suffix' => 'nullable|string',
            'meta' => 'array|nullable',
            'meta.*.name' => 'required|string',
            'meta.*.content' => 'required|string',
            'inject_at_head' => 'nullable|string',
            'inject_at_body_start' => 'nullable|string',
            'inject_at_body_end' => 'nullable|string',
            'favicon' => 'nullable|file|mimes:ico,png,svg|max:512',
            // 'robots_txt' => 'nullable|string',
        ]);

        if ($request->hasFile('favicon')) {
            $path = Storage::putFileAs(
                'public', $request->file('favicon'), 'favicon.'.$request->file('favicon')->getClientOriginalExtension()
            );
            $settings->favicon_path = Storage::url($path);
        }
        $settings->title_home = $request->input('title_home') ?? null;
        $settings->title_suffix = $request->input('title_suffix') ?? null;
        $settings->meta = $request->input('meta') ?? [];
        $settings->inject_at_head = $request->input('inject_at_head') ?? null;
        $settings->inject_at_body_start = $request->input('inject_at_body_start') ?? null;
        $settings->inject_at_body_end = $request->input('inject_at_body_end') ?? null;
        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('SEO Settings Updated Successfully')]]);
    }
}
