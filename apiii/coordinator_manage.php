<?php
require_once __DIR__ . '/env.php';

include($_SERVER['DOCUMENT_ROOT']. BASE_URL .'api/config/config.php');header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {

    $created_at = date("Y-m-d H:i:s");

    // 🔐 Basic escaping (IMPORTANT)
    $name              = $_POST['name'];
    $contact_no        = $_POST['contact_no'];
    $email_id          = $_POST['email_id'];
    $role              = $_POST['user_role'];
    $password          = $_POST['password'];
 

    $sql = "
        INSERT INTO user_login (
            name,
            contact_no,
            email_id,
            created_at,
            user_role,
            password,
            status
        ) VALUES (
            '$name',
            '$contact_no',
            '$email_id',
            '$created_at',
            '$role',
            '$password',
            1
        )
    ";

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

    $query = "SELECT id,name, password, contact_no, email_id, permission, status,user_role FROM user_login WHERE status=1 AND user_role=7";

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

    $query = "SELECT name, password, contact_no, email_id, permission, status,user_role FROM user_login WHERE id='".$id."'";

    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);

    echo json_encode([
        'Code' => 200,
        'data' => $row
    ]);
    exit();
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_details'])){
    
    $id   = $_POST['user_id'];
    $name              = $_POST['name'];
    $contact_no        = $_POST['contact_no'];
    $email_id          = $_POST['email_id'];
    $role              = $_POST['user_role'];
    $password          = $_POST['password'];

    
//  $insertQuery = "UPDATE customer_details SET customer_name='".$name."',last_updated_by='".$created_at."' where id='".$id."'";
 $updateQuery ="UPDATE user_login SET name='".$name."',password='".$password."',contact_no='".$contact_no."',email_id='".$email_id."' WHERE id='".$id."'";
 
        mysqli_query($con, $updateQuery);
        
        if (mysqli_affected_rows($con) > 0) {
            echo json_encode([
                'Code' => 200,
                'msg' => 'Data updated successfully'
            ]);
        } else {
            echo json_encode([
                'Code' => 400,
                'msg' => 'No changes made / Invalid ID'
            ]);
        }

    // echo json_encode($response);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {

    $id = $_POST['id'];

    $query = "UPDATE user_login SET status = 0 WHERE id='".$id."'";

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