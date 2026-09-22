<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
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
    $countQuery = "SELECT COUNT(*) AS total FROM user_set_panel_data";
    $countResult = mysqli_query($con, $countQuery);
    $totalRecords = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Main data query with LIMIT and OFFSET
    $selectQuery = "SELECT
        uspd.id,
        uspd.panel_id,
        uspd.status,
        ul.name
    FROM user_set_panel_data AS uspd
    LEFT JOIN user_login AS ul
        ON uspd.user_id = ul.id
    ORDER BY uspd.id DESC
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
    $userRes = mysqli_query($con, "SELECT * FROM user_set_panel_data WHERE id='$id'");
    $userData = mysqli_fetch_assoc($userRes);

    // 2️⃣ Get panel limit
    $panelRes = mysqli_query($con, "SELECT * FROM panel_list WHERE panel_id='".$userData['panel_id']."'");
    $panelData = mysqli_fetch_assoc($panelRes);

    // 3️⃣ Count active users
    $countRes = mysqli_query(
        $con,
        "SELECT COUNT(*) AS total 
         FROM user_set_panel_data 
         WHERE panel_id='".$userData['panel_id']."' AND status=1"
    );
    $countRow = mysqli_fetch_assoc($countRes);
    $activeCount = (int)$countRow['total'];

    // 4️⃣ Activate limit check
    if ($status == 1 && $activeCount >= $panelData['no_of_user_allotment']) {
        echo json_encode([
            'Code' => 201,
            'msg' => 'Panel user limit reached'
        ]);
        exit();
    }

    // 5️⃣ Update
    $update = mysqli_query(
        $con,
        "UPDATE user_set_panel_data SET status='$status' WHERE id='$id'"
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


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>