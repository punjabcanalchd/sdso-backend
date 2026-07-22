<?php

namespace App\Helpers;

class RSAHelper
{
    public static function decrypt(string $encryptedText): string
    {
        $privateKey = file_get_contents(
            storage_path('app/private/keys/private.pem')
        );

        $success = openssl_private_decrypt(
            base64_decode($encryptedText),
            $decrypted,
            $privateKey
        );

        if (!$success) {
            // If decryption fails (e.g. plain text sent during testing), return original text
            return $encryptedText;
        }

        $decoded = json_decode($decrypted, true);
        
        // If it was valid JSON, return the decoded string, otherwise return the raw decrypted string
        return (json_last_error() === JSON_ERROR_NONE && $decoded !== null) 
            ? (string) $decoded 
            : (string) $decrypted;
    }
}