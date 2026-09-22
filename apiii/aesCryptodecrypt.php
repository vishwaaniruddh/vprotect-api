<?php
function cryptoJsAesDecrypt($passphrase, $base64Encrypted) {
    $data = base64_decode($base64Encrypted);

    // Check for OpenSSL Salted__ prefix
    if (substr($data, 0, 8) !== "Salted__") {
        throw new Exception("Invalid data or not encrypted with passphrase method");
    }

    $salt = substr($data, 8, 8);
    $ciphertext = substr($data, 16);

    // OpenSSL key & IV derivation (EVP_BytesToKey)
    $key_iv = '';
    $prev = '';
    while (strlen($key_iv) < 48) {
        $prev = md5($prev . $passphrase . $salt, true);
        $key_iv .= $prev;
    }

    $key = substr($key_iv, 0, 32);  // AES-256 key
    $iv = substr($key_iv, 32, 16);  // IV

    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($decrypted === false) {
        throw new Exception("Decryption failed — possibly incorrect key or malformed ciphertext");
    }

    return $decrypted;
}

// Example usage:
// $encrypted_otp = "U2FsdGVkX1+efkwq9UyHtCq7a+C2kJWBG4OArNrElPI=";
// $key = "xx8921AHFFpojaspkeATustb25b698emnruaqjhasgKGHSasfabAjfsakkaw289";

// try {
//     $otp = cryptoJsAesDecrypt($key, $encrypted_otp);
//     echo "✅ Decrypted OTP: $otp";
// } catch (Exception $e) {
//     echo "❌ Error: " . $e->getMessage();
// }


?>