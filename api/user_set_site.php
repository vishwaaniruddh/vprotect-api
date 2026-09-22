<?php
// require_once __DIR__ . '/env.php';
// include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');

include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_user_set_panel'])) {
    // Default pagination values
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 25;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Count total records
    $countQuery =  "SELECT COUNT(*) AS total 
               FROM site_alltoment AS sa
               INNER JOIN user_login AS ul ON sa.user_id = ul.id
               WHERE ul.name IS NOT NULL AND ul.name != ''";
    $countResult = mysqli_query($con, $countQuery);
    $totalRecords = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Main data query
    $selectQuery = "SELECT
    sa.id,
    sa.site_id,
    sa.status,
    ul.name,
    s.site_name
FROM site_alltoment AS sa
INNER JOIN user_login AS ul
    ON sa.user_id = ul.id
LEFT JOIN sites AS s
    ON sa.site_id = s.id
ORDER BY sa.id DESC
LIMIT $limit OFFSET $offset";

    $result = mysqli_query($con, $selectQuery);

    $leads = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $leads[] = $row;
        }
    }

    $response = [
        'Code' => 200,
        'data' => $leads,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ]
    ];

    echo json_encode($response);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeStatus'])) {

    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';

    // 1️⃣ Get record
    $userRes = mysqli_query($con, "SELECT * FROM site_alltoment WHERE id='$id'");
    $userData = mysqli_fetch_assoc($userRes);

    // 2️⃣ Get site limit (agar site limit table hai to use karo)
    $siteRes = mysqli_query($con, "SELECT * FROM sites WHERE id='".$userData['site_id']."'");
    $siteData = mysqli_fetch_assoc($siteRes);

    // 3️⃣ Count active users per site
    $countRes = mysqli_query(
        $con,
        "SELECT COUNT(*) AS total 
         FROM site_alltoment 
         WHERE site_id='".$userData['site_id']."' AND status=1"
    );
    $countRow = mysqli_fetch_assoc($countRes);
    $activeCount = (int)$countRow['total'];

    // 4️⃣ Activate limit check (agar limit column hai sites table me)
    if ($status == 1 && isset($siteData['no_of_user_allotment']) && $activeCount >= $siteData['no_of_user_allotment']) {
        echo json_encode([
            'Code' => 201,
            'msg' => 'Site user limit reached'
        ]);
        exit();
    }

    // 5️⃣ Update
    $update = mysqli_query(
        $con,
        "UPDATE site_alltoment SET status='$status' WHERE id='$id'"
    );

    if ($update) {
        echo json_encode([
            'Code' => 200,
            'msg' => 'Status updated successfully'
        ]);
    } else {
        echo json_encode([
            'Code' => 500,
            'msg' => 'Update failed',
            'error' => mysqli_error($con)
        ]);
    }
}
?>