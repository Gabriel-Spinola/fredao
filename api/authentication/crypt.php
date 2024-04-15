<?php
namespace Fredao\Crypt;

final class Crypt
{
    // TODO - user .env for the following values
    public static string $key = "123";
    public static string $iv;

    /**
     * @return string aes-256-cbc-hmac-sha256
     */
    public static function cipher_method(): string
    {
        return openssl_get_cipher_methods()[25];
    }

    /**
     * @return string|false — the encrypted string on success or false on failure.
     */
    public static function encrypt(string $data): string|false
    {
        return openssl_encrypt($data, self::cipher_method(), self::$key, 0 /*Self::$iv*/);
    }

    /**
     * @return string|false — the decrypted string on success or false on failure.
     */
    public static function decrypt(string $encrypted): string|false
    {
        return openssl_decrypt($encrypted, self::cipher_method(), self::$key, 0);
    }
}