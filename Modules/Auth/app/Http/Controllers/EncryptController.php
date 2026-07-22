<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Routing\Controller;

class EncryptController extends Controller
{
    public function encryptTest()
    {
        
        $publicKey = file_get_contents(storage_path('app/public/public.pem'));

        $plaintext = "Bhalu@123";

        openssl_public_encrypt($plaintext, $encrypted, $publicKey);
        $encryptedBase64 = base64_encode($encrypted);

        return response()->json([
            'plaintext' => $plaintext,
            'encrypted' => $encryptedBase64,
        ]);
    }
}
