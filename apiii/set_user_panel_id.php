<?php
include($_SERVER['DOCUMENT_ROOT'] . '/FRUtopia/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$datetime = date('Y-m-d H:i:s');

$data = $_POST;

$userid     = $data['userid'] ?? '';
$panelid    = $data['panelid'] ?? '';
$created_by = $data['created_by'] ?? '';

$_set_count = 0;
$panel_set_to_user_count = 0;

$panel_query = "SELECT * FROM panel_list WHERE panel_id = '$panelid'";
$panel_result = mysqli_query($con, $panel_query);
$fetch_panel_details = mysqli_fetch_assoc($panel_result);

$user_panel_data = mysqli_query($con, "SELECT * FROM user_set_panel_data WHERE panel_id='$panelid'");

if (mysqli_num_rows($user_panel_data) > 0) {

    // while ($fetchall = mysqli_fetch_assoc($user_panel_data)) {
    //     if ($fetchall['user_id'] == $userid && $fetchall['status'] == 1) {
    //         $_set_count = 1;
    //     } else {
    //         $panel_set_to_user_count++;
    //     }
    // }
    
    while ($fetchall = mysqli_fetch_assoc($user_panel_data)) {

    // agar same user pehle se active hai
        if ($fetchall['user_id'] == $userid && $fetchall['status'] == 1) {
            $_set_count = 1;
        }
    
        // sirf active users ko count karo
        if ($fetchall['status'] == 1) {
            $panel_set_to_user_count++;
        }
    }

    if ($_set_count == 0) {

        if ($panel_set_to_user_count == $fetch_panel_details['no_of_user_allotment']) {
            $response = [
                'Code' => 201,
                'msg' => 'Panel Already Set to '.$fetch_panel_details['no_of_user_allotment'].' different users. Unable to set.',
            ];
        } else {
            mysqli_query(
                $con,
                "INSERT INTO user_set_panel_data(user_id,panel_id,created_at,updated_at,created_by,updated_by)
                 VALUES('$userid','$panelid','$datetime','$datetime','$created_by','$created_by')"
            );

            $response = [
                'Code' => 200,
                'msg' => 'Panel Set Successfully!!',
            ];
        }

    } else {
        $response = [
            'Code' => 202,
            'msg' => 'Panel already set to this user.',
        ];
    }

} else {

    mysqli_query(
        $con,
        "INSERT INTO user_set_panel_data(user_id,panel_id,created_at,updated_at,created_by,updated_by)
         VALUES('$userid','$panelid','$datetime','$datetime','$created_by','$created_by')"
    );

    $response = [
        'Code' => 200,
        'msg' => 'Panel Set Successfully!!',
    ];
}

echo json_encode($response);
exit;
