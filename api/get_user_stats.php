<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = array();

// Build queries
$queries = [
    "total" => "SELECT COUNT(id) as count FROM user_login",
    "active" => "SELECT COUNT(id) as count FROM user_login WHERE status = 1",
    "inactive" => "SELECT COUNT(id) as count FROM user_login WHERE status = 0",
];

$data = [];

// Execute basic counts
foreach ($queries as $key => $query) {
    $res = mysqli_query($con, $query);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        $data[$key] = (int)$row['count'];
    }
    else {
        $data[$key] = 0;
    }
}

// Fetch by role
$role_query = "
    SELECT r.role, COUNT(u.id) as user_count 
    FROM user_login u 
    LEFT JOIN role r ON u.user_role = r.id 
    GROUP BY u.user_role
";

$role_res = mysqli_query($con, $role_query);
$roles_data = [];

if ($role_res) {
    while ($row = mysqli_fetch_assoc($role_res)) {
        $roleName = $row['role'] ? $row['role'] : 'Unassigned';
        $roles_data[] = [
            "name" => $roleName,
            "count" => (int)$row['user_count']
        ];
    }
}

$data['by_roles'] = $roles_data;

$response['Code'] = 200;
$response['data'] = $data;

echo json_encode($response);
exit();
?>
