<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

final class SecureRouteId
{
    public static function encrypt(int|string $id): string
    {
        return Crypt::encryptString((string) $id);
    }

    public static function decrypt(string $value): int
    {
        try {
            $id = Crypt::decryptString($value);
        } catch (DecryptException) {
            abort(404);
        }

        if (! ctype_digit($id) || (int) $id < 1) {
            abort(404);
        }

        return (int) $id;
    }
}
