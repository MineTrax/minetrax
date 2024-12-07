<?php

namespace App\Utils\Helpers;

use App\Services\MinecraftApiService;
use Intervention\Image\Laravel\Facades\Image;
use Http;
use Cache;

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
        $image = Image::read($imagePath);
        if ($size) {
            $image->resize($size, $size);
        }

        return $image;
    }

    public static function getSkinImageFromMinotar($type, $identifier, $size = null)
    {
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

        $data = self::httpGetWithCache($url);
        return Image::read($data);
    }

    public static function getSkinImageFromCrafatar($type, $identifier, $size = null)
    {
        $useUsernameForSkins = config('minetrax.use_username_for_skins');

        switch ($type) {
            case 'avatar':
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($identifier);
                } else {
                    $uuid = $identifier;
                }
                $url = 'https://crafatar.com/avatars/' . $uuid . '?size=' . $size;
                break;
            case 'skin':
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($identifier);
                } else {
                    $uuid = $identifier;
                }
                $url = 'https://crafatar.com/skins/' . $uuid;
                break;
            case 'render':
                if ($useUsernameForSkins) {
                    $uuid = MinecraftApiService::playerUsernameToUuid($identifier);
                } else {
                    $uuid = $identifier;
                }
                $url = 'https://crafatar.com/renders/body/' . $uuid . '?scale=' . $size;
            default:
                throw new \Exception('Invalid type');
        }

        $data = self::httpGetWithCache($url);
        return Image::read($data);
    }

    public static function getSkinImageFromMcHeads($type, $identifier, $size = null)
    {
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

        $data = self::httpGetWithCache($url);
        return Image::read($data);
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

    private static function httpGetWithCache($url)
    {
        $key = "imagecache::{$url}";
        return Cache::store('file')->remember($key, 60, function () use ($url) {
            return Http::get($url)->body();
        });
    }
}
