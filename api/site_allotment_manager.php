<?php
include(__DIR__ . '/config/config.php'); 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = array();

// 1. Fetch sites dynamically assigned/unassigned for a specific manager
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_sites_for_manager'])) {

    $manager_id = isset($_POST['manager_id']) ? (int)$_POST['manager_id'] : 0;

    if ($manager_id <= 0) {
        echo json_encode(['Code' => 400, 'msg' => 'Manager ID is required']);
        exit;
    }

    // Query fetches ALL active branch codes
    // It Left Joins the site_allotment table to check who currently owns it
    $sql = "
        SELECT 
            bc.id,
            bc.branch_name,
            bc.branch_code,
            sa.branch_manager_id as assigned_manager_id,
            bm.branch_name as assigned_manager_name
        FROM 
            branch_code bc
        LEFT JOIN 
            site_allotment sa ON bc.id = sa.branch_code_id AND sa.status = 1
        LEFT JOIN
            branch_details bm ON sa.branch_manager_id = bm.id
        WHERE 
            bc.status = 1
        ORDER BY 
            bc.branch_name ASC
    ";

    $result = mysqli_query($con, $sql);
    $data = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {

            // Logic flags for UI rendering
            $is_mine = ($row['assigned_manager_id'] == $manager_id);
            $is_others = (!empty($row['assigned_manager_id']) && $row['assigned_manager_id'] != $manager_id);

            $data[] = [
                'id' => $row['id'],
                'branch_name' => $row['branch_name'],
                'branch_code' => $row['branch_code'],
                'is_assigned_to_me' => $is_mine,
                'is_assigned_to_other' => $is_others,
                'assigned_manager_name' => $row['assigned_manager_name'] ?? ''
            ];
        }
    }

    echo json_encode(['Code' => 200, 'data' => $data]);
    exit();
}

// 2. Perform BULK insert/update for assigned sites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_allotment'])) {
    $manager_id = isset($_POST['manager_id']) ? (int)$_POST['manager_id'] : 0;

    // site_ids arrives as a JSON encoded array of checked checkboxes
    $site_ids_json = isset($_POST['site_ids']) ? $_POST['site_ids'] : '[]';
    $site_ids = json_decode($site_ids_json, true);

    if ($manager_id <= 0) {
        echo json_encode(['Code' => 400, 'msg' => 'Manager ID is required']);
        exit;
    }

    if (!is_array($site_ids)) {
        $site_ids = [];
    }

    // Start Transaction
    mysqli_begin_transaction($con);

    try {
        // Find existing assigned sites for this manager
        $existing_query = mysqli_query($con, "SELECT branch_code_id FROM site_allotment WHERE branch_manager_id = $manager_id");
        $existing_sites = [];
        while ($r = mysqli_fetch_assoc($existing_query)) {
            $existing_sites[] = $r['branch_code_id'];
        }

        // Determine Additions and Removals
        $sites_to_add = array_diff($site_ids, $existing_sites);
        $sites_to_remove = array_diff($existing_sites, $site_ids);

        // Process Removals (We hard-delete instead of soft-delete for cleaner mapping logic, or status=0)
        // Hard-deleting for simplicity so other managers can immediately claim them
        if (count($sites_to_remove) > 0) {
            $remove_str = implode(',', array_map('intval', $sites_to_remove));
            mysqli_query($con, "DELETE FROM site_allotment WHERE branch_manager_id = $manager_id AND branch_code_id IN ($remove_str)");
        }

        // Process Additions
        if (count($sites_to_add) > 0) {
            $created_at = date('Y-m-d H:i:s');
            // Assuming current session user ID is 1 for testing since not explicitly provided. Fix as necessary if session exists.
            $created_by = 1;

            $insert_values = [];
            foreach ($sites_to_add as $site_id) {
                $site_id = (int)$site_id;
                // Double check it isn't assigned to someone else (safety check)
                $check = mysqli_query($con, "SELECT id FROM site_allotment WHERE branch_code_id = $site_id AND branch_manager_id != $manager_id");
                if (mysqli_num_rows($check) == 0) {
                    $insert_values[] = "($manager_id, $site_id, 1, '$created_at', $created_by)";
                }
            }

            if (count($insert_values) > 0) {
                $insert_sql = "INSERT INTO site_allotment (branch_manager_id, branch_code_id, status, created_at, created_by) VALUES " . implode(',', $insert_values);
                mysqli_query($con, $insert_sql);
            }
        }

        mysqli_commit($con);
        echo json_encode(['Code' => 200, 'msg' => 'Allocations updated successfully.']);

    }
    catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['Code' => 500, 'msg' => 'Error saving allocations: ' . $e->getMessage()]);
    }

    exit();
}
?>
