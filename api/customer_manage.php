<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {

    $created_at = date("Y-m-d H:i:s");

    // 🔐 Basic escaping (IMPORTANT)
    $user_id = $_POST['user_id'];
    $name              = $_POST['customer'];
  
    $sql = "INSERT INTO customers_details(customer, status, created_at, created_by) VALUES ('".$name."','1','".$created_at."','".$user_id."')";

    if (mysqli_query($con, $sql)) {
        echo json_encode([
            "Code" => 200,
            "msg"  => "User saved successfully"
        ]);
    } else {
        echo json_encode([
            "Code" => 500,
            "msg"  => "DB Error",
            "error"=> mysqli_error($con)
        ]);
    }

    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_user_data'])) {

    $query = "SELECT id,customer,status,created_at,created_by FROM customers_details WHERE  status=1 ";

    $result = mysqli_query($con, $query);
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode([
        'Code'  => 200,
        'data'  => $data,
    ]);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_user_by_id'])) {

    $id = $_POST['id'];

    $query = "SELECT id,customer,status,created_at,created_by FROM customers_details WHERE  id='".$id."'";

    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);

    echo json_encode([
        'Code' => 200,
        'data' => $row
    ]);
    exit();
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])){

    $id        = $_POST['customer_id'];         
    $name      = $_POST['customer'];    
    $updatedAt = date("Y-m-d H:i:s");   

    $updateQuery = "
        UPDATE customers_details 
        SET 
            customer = '$name',
            last_updated = '$updatedAt'
        WHERE id = '$id'
    ";

    mysqli_query($con, $updateQuery);

    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'Code' => 200,
            'msg'  => 'Data updated successfully'
        ]);
    } else {
        echo json_encode([
            'Code' => 400,
            'msg'  => 'No changes made / Invalid ID'
        ]);
    }

    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {

    $id = $_POST['id'];

    $query = "UPDATE customers_details SET status = 0 WHERE id='".$id."'";

    if (mysqli_query($con, $query)) {

        echo json_encode([
            'Code' => 200,
            'msg'  => 'User deleted successfully'
        ]);

    } else {

        echo json_encode([
            'Code' => 500,
            'msg'  => 'Database error'
        ]);
    }

    exit();
}




///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>