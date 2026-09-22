<?php
// include($_SERVER['DOCUMENT_ROOT'] . '/SAR_payroll/api/config/config.php');
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();
$data = $_POST;

$userid = isset($data['userid']) ? $data['userid'] : '';

if ($userid) {
    
    $getusername = mysqli_query($con, "SELECT name FROM user_login WHERE id = '$userid'");
    $username = mysqli_fetch_assoc($getusername)['name'];
    $newname = str_replace(' ', '', $username); // remove space between name 
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // $profile_img = isset($_FILES['profile_img']) ? $_FILES['profile_img'] : null;
        
        $profile_img = isset($_FILES['profile_img']) ? $_FILES['profile_img'] : null;

        if ($profile_img) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/SAR_payroll/uploads/$userid/";
        
            // Delete old profile image(s)
            if (is_dir($upload_dir)) {
                $files = glob($upload_dir . '*'); // get all file names
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file); // delete file
                    }
                }
            }
        }

     
        $rolesql = mysqli_query($con, "SELECT user_role FROM user_login WHERE id = '" . mysqli_real_escape_string($con, $userid) . "'");
        if ($rolesql && mysqli_num_rows($rolesql) > 0) {
            $fetchroledetails = mysqli_fetch_assoc($rolesql);
            $roleid = $fetchroledetails['user_role'];

            $fetchrolesql = mysqli_query($con, "SELECT role FROM role WHERE id = '" . mysqli_real_escape_string($con, $roleid) . "'");
            $fetchrole = $fetchrolesql ? mysqli_fetch_assoc($fetchrolesql) : null;
            $role_name = $fetchrole['role'] ?? 'Unknown';

            $salarysql = mysqli_query($con, "SELECT salary FROM salary_master WHERE userid = '" . mysqli_real_escape_string($con, $userid) . "' AND status = 1");
            $sqlsalary = $salarysql ? mysqli_fetch_assoc($salarysql) : null;
            $emp_sal = $sqlsalary['salary'] ?? 0;
            $per_day_sal = ($emp_sal / 30);

            $fetchleavesql = mysqli_query($con, "SELECT * FROM leave_count_details WHERE userid = '$userid'");
            $fetch_totalleave = mysqli_fetch_assoc($fetchleavesql);
            $total_leave = $fetch_totalleave['total_leaves'] ?? 0;
            $leaves_taken = $fetch_totalleave['leaves_taken'] ?? 0;
            $remaining_leaves = $fetch_totalleave['remaining_leaves'] ?? 0;

            $extra_leave = max(0, $leaves_taken - 18);
            $deducted_salary = $extra_leave > 0 ? ($emp_sal - ($per_day_sal * $extra_leave)) : 0;

            $usrsql = mysqli_query($con, "SELECT id,name,contact_no,email_id,profile_img,password FROM user_login WHERE id = '$userid'");
            if ($usrsql && mysqli_num_rows($usrsql) > 0) {
                $userdetail = [];
                while ($row = mysqli_fetch_assoc($usrsql)) {
                  
                    $userdetail[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'contact_no' => $row['contact_no'],
                        'role' => $role_name,
                        'email' => $row['email_id'],
                        'status' => $row['status'],
                        'per_day_sal' => round($per_day_sal),
                        'actual_salary' => $emp_sal,
                        'deducted_salary' => round($deducted_salary),
                        'profile_image' => $row['profile_img'],
                        'password' => $row['password']
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
        } else {
            $response = [
                'Code' => 350,
                'msg' => "Role details not found for the user!",
            ];
        }
    } else {
        $response = [
            'Code' => 400,
            'msg' => "Invalid request method!",
        ];
    }
} else {
    $response = [
        'Code' => 401,
        'msg' => "User ID is missing!",
    ];
}

echo json_encode($response);
?>
