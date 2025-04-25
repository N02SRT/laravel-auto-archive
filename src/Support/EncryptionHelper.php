<?php
namespace N02srt\AutoArchive\Support;

use Illuminate\Support\Facades\Crypt;

class EncryptionHelper
{
    public static function encrypt(string $value): string
    {
        if (config('auto-archive.encryption.enabled')) {
            return Crypt::encryptString($value);
        }
        return $value;
    }

    public static function decrypt(string $value): string
    {
        if (config('auto-archive.encryption.enabled')) {
            return Crypt::decryptString($value);
        }
        return $value;
    }
}