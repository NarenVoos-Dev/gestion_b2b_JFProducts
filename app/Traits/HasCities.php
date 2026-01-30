<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

trait HasCities
{
    /**
     * Get all cities from the JSON file.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getCities()
    {
        return Cache::remember('all_cities', 3600, function () {
            $path = resource_path('json/cities.json');
            
            if (!File::exists($path)) {
                return collect([]);
            }

            $json = json_decode(File::get($path), true);
            return collect($json['data'] ?? []);
        });
    }

    /**
     * Get a specific city by ID.
     *
     * @param int|string|null $id
     * @return array|null
     */
    public static function getCityById($id)
    {
        if (!$id) {
            return null;
        }

        return self::getCities()->firstWhere('id', (int)$id);
    }
    
    /**
     * Get city name by ID.
     *
     * @param int|string|null $id
     * @return string
     */
    public static function getCityNameById($id)
    {
        $city = self::getCityById($id);
        return $city['name'] ?? 'Desconocida';
    }
    
    /**
     * Get array for select options
     */
    public static function getCitiesForSelect()
    {
        return self::getCities()->mapWithKeys(function ($item) {
             return [$item['id'] => $item['name']];
        })->toArray();
    }
}
