<?php
require_once __DIR__ . '/env.php';

include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');


// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_branch'])) {

    $user_id = $_POST['user_id'];
    $branch_name = isset($_POST['branch_name']) ? mysqli_real_escape_string($con, $_POST['branch_name']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($con, $_POST['address']) : '';
    $branch_code = isset($_POST['branch_code']) ? mysqli_real_escape_string($con, $_POST['branch_code']) : '';

    $selectQuery = "SELECT id FROM branch_code WHERE branch_code = '" . $branch_code . "' ";

    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Branch Code already exists.'
        ];
        echo json_encode($response);
        exit();
    }
    else {
        $insertQuery = "INSERT INTO branch_code (branch_name, address, branch_code, status, created_at, created_by) VALUES ('" . $branch_name . "','" . $address . "','" . $branch_code . "', 1 ,'" . $created_at . "','" . $user_id . "')";

        if (mysqli_query($con, $insertQuery)) {
            $response = [
                'Code' => 200,
                'msg' => 'Data saved successfully'
            ];
        }
        else {
            $response = [
                'Code' => 500,
                'msg' => 'Failed to save details',
                'con_error' => mysqli_error($con)
            ];
        }

    }

    echo json_encode($response);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_branch'])) {

    // Pagination parameters
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Count total records
    $count_query = mysqli_query($con, "SELECT COUNT(*) as total_records FROM branch_code WHERE status = 1");
    $count_data = mysqli_fetch_assoc($count_query);
    $total_records = $count_data['total_records'];
    $total_pages = ceil($total_records / $limit);

    $selectQuery = "SELECT * FROM branch_code WHERE status = 1 ORDER BY id DESC LIMIT $offset, $limit";
    $result = mysqli_query($con, $selectQuery);

    $leads = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $leads[] = $row;
        }
    }

    $response = [
        'Code' => 200,
        'data' => $leads,
        'pagination' => [
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'limit' => $limit
        ]
    ];

    echo json_encode($response);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branch'])) {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($con, $_POST['id']) : '';
    $branch_name = isset($_POST['branch_name']) ? mysqli_real_escape_string($con, $_POST['branch_name']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($con, $_POST['address']) : '';
    $branch_code = isset($_POST['branch_code']) ? mysqli_real_escape_string($con, $_POST['branch_code']) : '';
    $updated_by = isset($_POST['user_id']) ? mysqli_real_escape_string($con, $_POST['user_id']) : '';

    // Check if new branch_code already exists on a different ID
    $checkQuery = "SELECT id FROM branch_code WHERE branch_code = '$branch_code' AND id != '$id'";
    if (mysqli_num_rows(mysqli_query($con, $checkQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Branch Code already exists on another record.'
        ];
        echo json_encode($response);
        exit();
    }

    $updateQuery = "UPDATE branch_code SET branch_name = '" . $branch_name . "' ,address='" . $address . "',branch_code='" . $branch_code . "',updated_at='" . $created_at . "', updated_by='" . $updated_by . "' WHERE id = '" . $id . "' ";

    if (mysqli_query($con, $updateQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Branch updated successfully'
        ];
    }
    else {
        $response = [
            'Code' => 500,
            'msg' => 'Failed to update branch',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    $id = mysqli_real_escape_string($con, $_POST['id']);

    $deleteQuery = "DELETE FROM branch_code WHERE id = '$id'";
    $result = mysqli_query($con, $deleteQuery);

    if ($result) {
        $response = [
            'Code' => 200,
            'message' => 'Deleted Successfully'
        ];
    }
    else {
        $response = [
            'Code' => 400,
            'message' => 'Delete Failed',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>