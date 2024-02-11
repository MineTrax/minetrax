<?php

namespace App\Utils\Helpers;

use App\Services\MinecraftApiService;
use Http;
use Intervention\Image\Facades\Image;

class MinecraftSkinUtils
{
    public static function getDefaultSkinImage($type, $size = null)
    {
        switch ($type) {
            case 'avatar':
                $imagePath = public_path('images/alex.png');
                break;
            case 'skin':
                $imagePath = public_path('images/alex_skin.png');
                break;
            case 'render':
                $imagePath = public_path('images/alex_render.png');
                break;
            default:
                throw new \Exception('Invalid type');
        }
        $image = Image::make($imagePath);
        if ($size) {
            $image->resize($size, $size);
        }

        return $image;
    }

    public static function getSkinImageFromMinotar($type, $identifier, $size = null)
    {
        $fetchAvatarFromUrlUsingCurl = config('minetrax.fetch_avatar_from_url_using_curl');

        switch ($type) {
            case 'avatar':
                $url = "https://minotar.net/avatar/$identifier";
                if ($size) {
                    $url = "https://minotar.net/avatar/{$identifier}/{$size}";
                }
                break;
            case 'skin':
                $url = "https://minotar.net/skin/$identifier";
                break;
            case 'render':
                throw new \Exception('Render image not supported by Minotar');
            default:
                throw new \Exception('Invalid type');
        }

        $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

        return Image::cache(function ($image) use ($data) {
            return $image->make($data);
        }, 60, true); // Cache lifetime is in minutes
    }

    public static function getSkinImageFromCrafatar($type, $identifier, $size = null)
    {
        $useUsernameForSkins = config('minetrax.use_username_for_skins');
        $fetchAvatarFromUrlUsingCurl = config('minetrax.fetch_avatar_from_url_using_curl');

        switch ($type) {
            case 'avatar':
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($identifier);
                } else {
                    $uuid = $identifier;
                }
                $url = 'https://crafatar.com/avatars/'.$uuid.'?size='.$size;
                break;
            case 'skin':
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($identifier);
                } else {
                    $uuid = $identifier;
                }
                $url = 'https://crafatar.com/skins/'.$uuid;
                break;
            case 'render':
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($identifier);
                } else {
                    $uuid = $identifier;
                }
                $url = 'https://crafatar.com/renders/body/'.$uuid.'?scale='.$size;
            default:
                throw new \Exception('Invalid type');
        }

        $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

        return Image::cache(function ($image) use ($data) {
            return $image->make($data);
        }, 60, true); // Cache lifetime is in minutes
    }

    public static function getSkinImageFromMcHeads($type, $identifier, $size = null)
    {
        $fetchAvatarFromUrlUsingCurl = config('minetrax.fetch_avatar_from_url_using_curl');

        switch ($type) {
            case 'avatar':
                $url = "https://mc-heads.net/avatar/$identifier";
                if ($size) {
                    $url = "https://mc-heads.net/avatar/{$identifier}/{$size}";
                }
                break;
            case 'skin':
                $url = "https://mc-heads.net/skin/$identifier";
                break;
            case 'render':
                $url = "https://mc-heads.net/body/$identifier";
                if ($size) {
                    $size = ($size * 20);
                    $url = "https://mc-heads.net/body/{$identifier}/{$size}";
                }
                break;
            default:
                throw new \Exception('Invalid type');
        }

        $data = $fetchAvatarFromUrlUsingCurl ? Http::get($url)->body() : $url;

        return Image::cache(function ($image) use ($data) {
            return $image->make($data);
        }, 60, true); // Cache lifetime is in minutes
    }

    public static function uploadSkinToMineSkin($file, $skinType): array
    {
        $response = Http::attach('file', file_get_contents($file), $file->getClientOriginalName())
            ->post('https://api.mineskin.org/generate/upload', [
                'visibility' => 1,
                'variant' => $skinType === 'alex' ? 'slim' : 'classic',
            ]);

        if ($response->failed()) {
            throw new \Exception(\Arr::get($response->json(), 'error') ?? __('Failed to upload skin to MineSkin. Please try again later'));
        }

        return $response->json();
    }
}
