<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

class GeneralSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(GeneralSettings $settings): \Inertia\Response
    {
        return Inertia::render('Admin/Setting/GeneralSetting', [
            'settings' => $settings->toArray(),
        ]);
    }

    public function update(Request $request, GeneralSettings $settings): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'site_name' => 'required|string|max:50',
            'enable_mcserver_onlineplayersbox' => 'required|boolean',
            'enable_mcserver_statuspingbox' => 'required|boolean',
            'enable_ingamechat' => 'required|boolean',
            'enable_shoutbox' => 'required|boolean',
            'enable_onlineuserbox' => 'required|boolean',
            'enable_newuserbox' => 'required|boolean',
            'enable_didyouknowbox' => 'required|boolean',
            'enable_welcomebox' => 'required|boolean',
            'welcomebox_content' => 'required_if:enable_welcomebox,true|nullable|string|max:1000',
            'enable_socialbox' => 'required|boolean',
            'youtube_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'twitch_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'threads_url' => 'nullable|url|max:255',
            'discord_invite_url' => 'nullable|url|max:255',
            'enable_discordbox' => 'required|boolean',
            'discord_server_id' => 'required_if:enable_discordbox,true|nullable|string|max:255',
            'enable_voteforserverbox' => 'required|boolean',
            'voteforserverbox_content.*.url' => 'required_if:enable_voteforserverbox,true|nullable|url',
            'voteforserverbox_content.*.name' => 'required_if:enable_voteforserverbox,true|nullable|string|max:50',
            'enable_donation_box' => 'required|boolean',
            'donation_box_url' => 'required_if:enable_donation_box,true|nullable|url',
            'photo_light' => 'sometimes|nullable|image|max:100',
            'photo_dark' => 'sometimes|nullable|image|max:100',
            'enable_status_feed' => 'required|boolean',
            'header_broadcast_text' => 'nullable|string|max:1000',
            'header_broadcast_url' => 'nullable|url|max:1000',
            'enable_topplayersbox' => 'required|boolean',
        ]);
        $settings->site_name = $request->input('site_name');
        $settings->enable_mcserver_onlineplayersbox = $request->input('enable_mcserver_onlineplayersbox');
        $settings->enable_mcserver_statuspingbox = $request->input('enable_mcserver_statuspingbox');
        $settings->enable_ingamechat = $request->input('enable_ingamechat');
        $settings->enable_shoutbox = $request->input('enable_shoutbox');
        $settings->enable_onlineuserbox = $request->input('enable_onlineuserbox');
        $settings->enable_newuserbox = $request->input('enable_newuserbox');
        $settings->enable_didyouknowbox = $request->input('enable_didyouknowbox');
        $settings->enable_status_feed = $request->input('enable_status_feed');

        $settings->enable_welcomebox = $request->input('enable_welcomebox');
        $settings->welcomebox_content = $request->input('welcomebox_content');

        $settings->enable_voteforserverbox = $request->input('enable_voteforserverbox');
        $settings->voteforserverbox_content = $request->input('voteforserverbox_content');

        $settings->enable_socialbox = $request->input('enable_socialbox');
        $settings->youtube_url = $request->input('youtube_url');
        $settings->facebook_url = $request->input('facebook_url');
        $settings->twitter_url = $request->input('twitter_url');
        $settings->twitch_url = $request->input('twitch_url');
        $settings->tiktok_url = $request->input('tiktok_url');
        $settings->linkedin_url = $request->input('linkedin_url');
        $settings->discord_invite_url = $request->input('discord_invite_url');
        // $settings->threads_url = $request->input('threads_url');

        $settings->enable_discordbox = $request->input('enable_discordbox');
        $settings->discord_server_id = $request->input('discord_server_id');

        $settings->enable_donation_box = $request->input('enable_donation_box');
        $settings->donation_box_url = $request->input('donation_box_url');

        $settings->header_broadcast_text = $request->input('header_broadcast_text') ?? null;
        $settings->header_broadcast_url = $request->input('header_broadcast_url') ?? null;

        $settings->enable_topplayersbox = $request->input('enable_topplayersbox');

        // Has Photo?
        if ($request->hasFile('photo_light')) {
            $path = Storage::putFileAs(
                'public', $request->file('photo_light'), 'site_header_logo_light.'.$request->file('photo_light')->getClientOriginalExtension()
            );
            $settings->site_header_logo_path_light = Storage::url($path).'?hash='.\Str::random(8);
        }

        if ($request->hasFile('photo_dark')) {
            $path = Storage::putFileAs(
                'public', $request->file('photo_dark'), 'site_header_logo_dark.'.$request->file('photo_dark')->getClientOriginalExtension()
            );
            $settings->site_header_logo_path_dark = Storage::url($path).'?hash='.\Str::random(8);
        }

        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('General Settings Updated Successfully')]]);
    }
}
