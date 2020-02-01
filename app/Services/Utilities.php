<?php

namespace App\Services;

use Illuminate\Support\Str;

class Utilities
{
    /**
     * Recursively snake-case an array's keys
     *
     * @param $array
     * @return array $array
     */
    public static function snakeCaseArrayKeys(array $array) {
        foreach (array_keys($array) as $key) {
            // Get a reference to the value of the key (avoid copy)
            // Then remove that array element
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key
            $transformedKey = Str::snake($key);

            // Recurse
            if (is_array($value)) {
                $value = static::snakeCaseArrayKeys($value);
            }

            // Store the transformed key with the referenced value
            $array[$transformedKey] = $value;

            // We'll be dealing with some large values, so memory cleanup is important
            unset($value);
        }

        return $array;
    }

    /**
     * Recursively camel-case an array's keys
     *
     * @param $array
     * @return array $array
     */
    public static function camelCaseArrayKeys($array) {
        foreach (array_keys($array) as $key) {
            // Get a reference to the value of the key (avoid copy)
            // Then remove that array element
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key
            $transformedKey = camel_case($key);

            // Recurse
            if (is_array($value)) {
                $value = static::camelCaseArrayKeys($value);
            }

            // Store the transformed key with the referenced value
            $array[$transformedKey] = $value;

            // We'll be dealing with some large values, so memory cleanup is important
            unset($value);
        }

        return $array;
    }
}
