<?php
session_start();
include(__DIR__ . '/baseurl.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $url = BASE_API_URL."/admin_login.php";   
// $url = "https://sarsspl.com/FRUtopia/api/admin_login.php";

    $postFields = http_build_query([
        "email_id" => $email,
        "password" => $password,
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);

    if ($data && $data['Code'] == 200) {
        // Store in session
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['user_role'] = $data['user_role'];
    }

    header('Content-Type: application/json');
    echo $res;
}
?>
