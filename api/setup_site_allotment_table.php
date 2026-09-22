<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$sql = "CREATE TABLE IF NOT EXISTS `site_allotment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_manager_id` int(11) NOT NULL,
  `branch_code_id` int(11) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`branch_manager_id`) REFERENCES `branch_details`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`branch_code_id`) REFERENCES `branch_code`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if (mysqli_query($con, $sql)) {
    echo json_encode([
        'Code' => 200,
        'msg' => 'site_allotment table created successfully.'
    ]);
}
else {
    echo json_encode([
        'Code' => 500,
        'msg' => 'Error creating table: ' . mysqli_error($con)
    ]);
}

exit();
?>
