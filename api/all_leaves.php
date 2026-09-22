<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$data = $_POST;

$response = array();

// $userid = isset($data['userid']) ? $data['userid'] : '' ;

$all_leaves = mysqli_query($con, "select * from leave_count_details ");
if (mysqli_num_rows($all_leaves) > 0) {
    $userdetail = [];
    while ($fetchallleaves = mysqli_fetch_assoc($all_leaves)) {
        $userid = $fetchallleaves['userid'];
        $total_leaves = $fetchallleaves['total_leaves'];
        $leaves_taken = $fetchallleaves['leaves_taken'];
        $remaining_leaves = $fetchallleaves['remaining_leaves'];

        $usernamesql = mysqli_query($con, "select name,contact_no from user_login where id='$userid' ");
        $fetch_username = mysqli_fetch_assoc($usernamesql);
        $username = $fetch_username['name'];

        $userdetail[] = [
            'userid' => $userid,
            'name' => $username,
            'contact_no' => $fetch_username['contact_no'],
            'total_leaves' => $total_leaves,
            'leaves_taken' => $leaves_taken,
            'remaining_leaves' => $remaining_leaves

        ];
    }
    $response = [
        'Code' => 200,
        'msg' => 'User Details fetched successfully',
        'data' => $userdetail,
    ];
} else {
    $response = [
        'Code' => 250,
        'msg' => "Unable to fetch Details!!",
    ];
}

echo json_encode($response);
?>