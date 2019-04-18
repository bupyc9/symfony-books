<?php

declare(strict_types=1);

namespace App\Service;

class Cache
{
    public static function createKey(string $tag, array $data = []): string
    {
        $tag = \str_replace(['\\', ':', '{', '}', '@', '(', ')', '/'], '_', $tag);

        return \md5($tag.\json_encode($data));
    }
}
