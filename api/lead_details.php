<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); 

// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_lead_details'])){
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $contact_no = isset($_POST['contact_no']) ? $_POST['contact_no'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : '';

    $insertQuery = "SELECT id FROM user_lead_details WHERE name = '".$name."' AND contact_no = '".$contact_no."' AND email_id = '".$email."' AND branch_id = '".$branch_id."'";
    
    if (mysqli_num_rows(mysqli_query($con, $insertQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'You have already requested for signup.',
        ];
        echo json_encode($response);
        exit();
    } else {
        $insertQuery = "INSERT INTO user_lead_details(name, contact_no, email_id, branch_id, created_at) VALUES ('".$name."','".$contact_no."','".$email."','".$branch_id."','".$created_at."')";

        if (mysqli_query($con, $insertQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'User details saved successfully',
        ];
    } else {
        $response = [
            'Code' => 500,
            'msg' => 'Failed to save User details',
            'con_error' => mysqli_error($con),
        ];
    }
        
    }    
    

    echo json_encode($response);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_user_lead'])){
    $selectQuery = "SELECT
                    uld_tbl.id,
                    uld_tbl.name,
                    uld_tbl.contact_no,
                    uld_tbl.email_id,
                    branch_tbl.branch_name
                    FROM (SELECT *
                        FROM user_lead_details) AS uld_tbl
                    LEFT JOIN (SELECT *
                                FROM branch_details) AS branch_tbl
                        ON uld_tbl.branch_id = branch_tbl.id
                    ORDER BY uld_tbl.id DESC;";
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
    ];
    
    echo json_encode($response);
    exit();
    
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_request_user'])) {

    $name = mysqli_real_escape_string($con, $_POST['name'] ?? '');
    $contact_no = mysqli_real_escape_string($con, $_POST['contact_no'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $branch_id = mysqli_real_escape_string($con, $_POST['branch_id'] ?? '');
    $created_at = date("Y-m-d H:i:s");

    $leadQuery = "SELECT id FROM user_lead_details WHERE name='".$name."' AND contact_no='".$contact_no."' AND branch_id='".$branch_id."'";

    $leadResult = mysqli_query($con, $leadQuery);

    if (!$leadResult) {
        echo json_encode([
            'Code' => 500,
            'msg'  => 'Database error while searching User',
            'con_error' => mysqli_error($con)
        ]);
        exit();
    }

    if (mysqli_num_rows($leadResult) > 0) {
        $leadRow = mysqli_fetch_assoc($leadResult);
        $user_lead_id = $leadRow['id'];

        $searchRequestQuery = "SELECT id, status FROM user_signup_request 
                               WHERE user_lead_id='$user_lead_id'";
        $requestResult = mysqli_query($con, $searchRequestQuery);

        if (mysqli_num_rows($requestResult) > 0) {
            $requestRow = mysqli_fetch_assoc($requestResult);

            if ($requestRow['status'] == 0) {
                $response = [
                    'Code' => 409,
                    'msg'  => 'Your signup request is still pending. Please wait for approval.'
                ];
            } else if ($requestRow['status'] == 1) {
                $response = [
                    'Code' => 200,
                    'msg'  => 'Your signup request has been approved. You can now log in.',
                    'signup' => true
                ];
            } else {
                $response = [
                    'Code' => 409,
                    'msg'  => 'Your signup request has been rejected previously. Please contact support.'
                ];
            }

        } else {
            $insertQuery = "INSERT INTO user_signup_request (user_lead_id, created_at)
                            VALUES ('$user_lead_id', '$created_at')";

            if (mysqli_query($con, $insertQuery)) {
                $response = [
                    'Code' => 200,
                    'msg'  => 'Request submitted successfully'
                ];
            } else {
                $response = [
                    'Code' => 500,
                    'msg'  => 'Failed to submit request',
                    'con_error' => mysqli_error($con)
                ];
            }
        }

    } else {
        $response = [
            'Code' => 404,
            'msg'  => 'No Match found.'
        ];
    }

    echo json_encode($response);
    exit();
}





if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_signup_request_list'])){

    $selectQuery = "SELECT
                    user_signup_request.id,
                    user_signup_request.status,
                    user_signup_request.remark,
                    user_lead_details.name,
                    user_lead_details.contact_no,
                    user_lead_details.email_id,
                    branch_details.branch_name
                    FROM user_signup_request,
                    user_lead_details,
                    branch_details
                    WHERE user_signup_request.user_lead_id = user_lead_details.id
                        AND user_lead_details.branch_id = branch_details.id
                    ORDER BY user_signup_request.id DESC";
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
    ];
    
    echo json_encode($response);
    exit();
    
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_signup_request_status'])){

    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $remark = isset($_POST['remark']) ? $_POST['remark'] : '';

    $query = "UPDATE user_signup_request SET status='".$status."', updated_at='".$created_at."'";
  
    if (!empty($remark)) {
        $query .= ", remark='".$remark."'";
    }

    $query .= " WHERE id='".$id."'";    
    
    $result = mysqli_query($con, $query);

    if($result){
        $response = [
            'Code' => 200,
            'msg' => "Updated successfully",
        ];
    }else{
        $response = [
            'Code' => 500,
            'msg' => "Failed to update",
            'con_error' => mysqli_error($con),
        ];
    }
    
    echo json_encode($response);
    exit();
    
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>