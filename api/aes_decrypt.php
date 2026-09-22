<?php

function openssl_decrypt_with_salted($encryptedBase64, $password) {
    $data = base64_decode($encryptedBase64);
    
    $salted = substr($data, 0, 8);
    if ($salted !== 'Salted__') {
        throw new Exception('Invalid data, not salted OpenSSL format');
    }

    $salt = substr($data, 8, 8);
    $ct = substr($data, 16);

    // OpenSSL key & IV generation method
    $keyAndIv = '';
    $prev = '';
    while (strlen($keyAndIv) < 48) { // 32 bytes key + 16 bytes IV for AES-256-CBC
        $prev = md5($prev . $password . $salt, true);
        $keyAndIv .= $prev;
    }

    $key = substr($keyAndIv, 0, 32);
    $iv  = substr($keyAndIv, 32, 16);

    $decrypted = openssl_decrypt($ct, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

$encryptedOtp = "U2FsdGVkX1+efkwq9UyHtCq7a+C2kJWBG4OArNrElPI=";
$password = "xx8921AHFFpojaspkeATustb25b698emnruaqjhasgKGHSasfabAjfsakkaw289";

try {
    $otp = openssl_decrypt_with_salted($encryptedOtp, $password);
    echo "Decrypted OTP: " . $otp;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
