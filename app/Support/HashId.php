<?php

namespace App\Support;

use Hashids\Hashids;
use App\Exceptions\InvalidHashIdException;

class HashId
{
    protected static ?Hashids $instance = null;

    protected static function instance(): Hashids
    {
        if (static::$instance === null) {
            static::$instance = new Hashids(
                config('app.key'),
                10
            );
        }
        return static::$instance;
    }

    public static function encode(int $id): string
    {
        return static::instance()->encode($id);
    }

    public static function decode(string $hash): ?int
    {
        $result = static::instance()->decode($hash);
        return !empty($result) ? $result[0] : null;
    }

    public static function decodeOrFail(string $hash): int
    {
        $id = static::decode($hash);
        if ($id === null) {
            throw new InvalidHashIdException;
        }
        return $id;
    }
}
