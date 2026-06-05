<?php

namespace App\Services;

class FileEncrypter
{
    /**
     * Get the raw 32-byte key from APP_KEY.
     */
    public static function getKey(): string
    {
        $appKey = env('APP_KEY');
        if (str_starts_with($appKey, 'base64:')) {
            $key = base64_decode(substr($appKey, 7));
        } else {
            $key = hash('sha256', $appKey, true);
        }
        if (strlen($key) !== 32) {
            $key = hash('sha256', $key, true);
        }
        return $key;
    }

    /**
     * Encrypt data using AES-256-CBC.
     */
    public static function encrypt(string $data): string
    {
        $key = self::getKey();
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return $iv . $ciphertext;
    }

    /**
     * Decrypt data using AES-256-CBC.
     */
    public static function decrypt(string $encryptedData): string
    {
        if (strlen($encryptedData) < 17) {
            return $encryptedData; // Too short to be encrypted
        }
        $key = self::getKey();
        $iv = substr($encryptedData, 0, 16);
        $ciphertext = substr($encryptedData, 16);
        $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted === false ? $encryptedData : $decrypted;
    }
}
