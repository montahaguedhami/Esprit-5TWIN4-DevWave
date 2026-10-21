<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class TuniBeachCache
{
    private const GENERATION_KEY = 'tunibeach:v1:cache-generation';

    public static function generation(): int
    {
        return (int) Cache::get(self::GENERATION_KEY, 1);
    }

    public static function invalidate(): void
    {
        Cache::forever(self::GENERATION_KEY, self::generation() + 1);
    }
}
