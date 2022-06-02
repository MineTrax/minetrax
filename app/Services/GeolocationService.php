<?php


namespace App\Services;


use App\Models\Country;
use Illuminate\Support\Facades\Log;

class GeolocationService
{
    public function getCountryIdFromIP($ip)
    {
        if (!$ip) {
            $country = Country::where('iso_code', null)->first();
            return $country?->id;
        }

        $isoCode = null;
        try {
            $isoCode = geoip($ip)->iso_code;
        } catch (\Exception $e) {
            Log::error($e);
        }
        $country = Country::where('iso_code', $isoCode)->first();
        return $country?->id;
    }

    public function getCountryFromIP($ip, $select = ['id', 'name', 'iso_code', 'flag'])
    {
        if (!$ip) {
            return Country::where('iso_code', null)->select($select)->first();
        }

        $isoCode = null;
        try {
            $isoCode = geoip($ip)->iso_code;
        } catch (\Exception $e) {
            Log::error($e);
        }
        return Country::where('iso_code', $isoCode)->select($select)->first();
    }
}
