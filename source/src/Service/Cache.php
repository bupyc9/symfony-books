<?php

declare(strict_types=1);

namespace App\Service;

class Cache
{
    public static function createKey(string $tag): string
    {
        return \str_replace(['\\', ':', '{', '}', '@', '(', ')', '/'], '_', $tag);
    }
}
