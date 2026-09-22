<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow the following methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow the following headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If this is a preflight request, respond and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once __DIR__ . '/env.php';

include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');
header('Content-Type: application/json');

$response = array();

$userid = isset($_GET['userid']) ? $_GET['userid'] : (isset($_POST['userid']) ? $_POST['userid'] : '');

if (empty($userid)) {
    $response = [
        'Code' => 400,
        'msg' => "User ID is missing or invalid!",
    ];
    echo json_encode($response);
    exit;
}

$query = "
    SELECT 
        q.question, 
        a.answer, 
        a.created_at 
    FROM user_security_que_ans a 
    JOIN user_security_question q ON a.question_id = q.id 
    WHERE a.user_id = '" . mysqli_real_escape_string($con, $userid) . "' 
    ORDER BY a.id ASC
";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $answers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $answers[] = [
            'question' => $row['question'],
            'answer' => $row['answer'],
            'created_at' => $row['created_at']
        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'Answers fetched successfully',
        'data' => $answers,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "No answers found for this user.",
    ];
}

echo json_encode($response);
?>
