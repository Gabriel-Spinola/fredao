<?php 
namespace Fredao\Crypt;

final class Crypt 
{
    public static string $aes_256_sha256 = openssl_get_cipher_methods()[25];

    // TODO - user .env for the following values
    public static string $key; 
    public static string $iv;

    /**
     * @return string|false — the encrypted string on success or false on failure.
     */
    public static function encrypt(string $data): string|false
    {
        return openssl_encrypt($data, self::$aes_256_sha256, self::$key, 0 /*Self::$iv*/); 
    }

    /**
     * @return string|false — the decrypted string on success or false on failure.
     */
    public static function decrypt(string $encrypted): string|false
    {
        return openssl_decrypt($encrypted, self::$aes_256_sha256, self::$key, 0);
    }
} 