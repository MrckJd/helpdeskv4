<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PSGCApiService
{
    public static string $provinceCode = '112400000';

    public static function getMunicipalities()
    {
        $response = Http::get("https://psgc.gitlab.io/api/provinces/" . self::$provinceCode . "/cities-municipalities/");

        if ($response->successful()) {
            $municipalities = $response->json();
            $municipalities = array_filter($municipalities, function ($item) {
                return $item['code'] !== '112402000';
            });
            $municipalities = array_values($municipalities);
            return $municipalities;
        }
        return response()->json(['error' => 'Failed to fetch municipalities'], 500);
    }

    public static function getBarangays(string $cityOrMunicipalityCode)
    {
        $response = Http::get("https://psgc.gitlab.io/api/cities-municipalities/{$cityOrMunicipalityCode}/barangays.json");

        if ($response->successful()) {
            return $response->json();
        }
        return response()->json(['error' => 'Failed to fetch barangays'], 500);
    }
}
