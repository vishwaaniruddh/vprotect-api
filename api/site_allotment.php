<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/config/config.php'); 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$created_at = date('Y-m-d H:i:s');
$response = array();

// Helper to check POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['Code' => 405, 'msg' => 'Method Not Allowed']);
    exit();
}

// 1. GET ALL MAPPINGS (Now returns all panels with assignment status)
if (isset($_POST['get_all_mappings'])) {
    // Select all panels, left join mapping and branch details
    $query = "
        SELECT 
            p.panel_id,
            m.id as mapping_id,
            m.created_at,
            b.id as manager_id,
            b.branch_name,
            b.branch_code
        FROM panel_list p
        LEFT JOIN bm_panel_mapping m ON p.panel_id COLLATE utf8mb4_unicode_ci = m.panel_id COLLATE utf8mb4_unicode_ci
        LEFT JOIN branch_details b ON m.branch_manager_id = b.id
        ORDER BY p.id DESC
    ";

    $result = mysqli_query($con, $query);
    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }

    echo json_encode([
        'Code' => 200,
        'data' => $data
    ]);
    exit();
}

// 2. ASSIGN PANELS TO A MANAGER
if (isset($_POST['assign_panels'])) {
    $manager_id = isset($_POST['branch_manager_id']) ? (int)$_POST['branch_manager_id'] : 0;
    $panel_ids_raw = isset($_POST['panel_ids']) ? $_POST['panel_ids'] : ''; // Expected comma separated or JSON string
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($manager_id <= 0 || empty($panel_ids_raw)) {
        echo json_encode(['Code' => 400, 'msg' => 'Manager ID and Panel IDs are required.']);
        exit();
    }

    // Parse panel_ids (could be JSON array or comma-separated)
    $panel_ids_array = [];
    if (is_string($panel_ids_raw)) {
        // Try decoding as JSON if it's a string array, else explode by comma
        $decoded = json_decode($panel_ids_raw, true);
        if (is_array($decoded)) {
            $panel_ids_array = $decoded;
        } else {
            $panel_ids_array = array_map('trim', explode(',', $panel_ids_raw));
        }
    } else if (is_array($panel_ids_raw)) {
        $panel_ids_array = $panel_ids_raw;
    }

    $panel_ids_array = array_filter($panel_ids_array); // Remove empty values

    if (empty($panel_ids_array)) {
        echo json_encode(['Code' => 400, 'msg' => 'No valid Panel IDs provided.']);
        exit();
    }

    $errors = [];
    $successCount = 0;

    foreach ($panel_ids_array as $pid) {
        $panel_id = mysqli_real_escape_string($con, trim($pid));

        if (empty($panel_id)) continue;

        // Check if panel is already mapped to ANY manager
        $checkQuery = "SELECT id, branch_manager_id FROM bm_panel_mapping WHERE panel_id COLLATE utf8mb4_unicode_ci = '$panel_id' COLLATE utf8mb4_unicode_ci";
        $checkResult = mysqli_query($con, $checkQuery);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $existing = mysqli_fetch_assoc($checkResult);
            if ($existing['branch_manager_id'] == $manager_id) {
                // Already assigned to this manager, silently skip or add to errors
                $errors[] = "Panel $panel_id is already assigned to this manager.";
            } else {
                $errors[] = "Panel $panel_id is actively assigned to another manager.";
            }
        } else {
            // Insert new mapping
            $insertQuery = "INSERT INTO bm_panel_mapping (branch_manager_id, panel_id, created_at, created_by) 
                            VALUES ($manager_id, '$panel_id', '$created_at', $user_id)";
            
            if (mysqli_query($con, $insertQuery)) {
                $successCount++;
            } else {
                $errors[] = "Failed to assign $panel_id: " . mysqli_error($con);
            }
        }
    }

    if ($successCount > 0 && empty($errors)) {
        echo json_encode(['Code' => 200, 'msg' => "$successCount panels successfully assigned."]);
    } else if ($successCount > 0 && !empty($errors)) {
        echo json_encode(['Code' => 206, 'msg' => "$successCount assigned. Some failed.", 'errors' => $errors]);
    } else {
        echo json_encode(['Code' => 400, 'msg' => 'Failed to assign any panels.', 'errors' => $errors]);
    }
    
    exit();
}

// 3. UNASSIGN/DELETE MAPPING
if (isset($_POST['delete_mapping'])) {
    $mapping_id = isset($_POST['mapping_id']) ? (int)$_POST['mapping_id'] : 0;

    if ($mapping_id <= 0) {
        echo json_encode(['Code' => 400, 'msg' => 'Invalid mapping ID.']);
        exit();
    }

    $deleteQuery = "DELETE FROM bm_panel_mapping WHERE id = $mapping_id";
    if (mysqli_query($con, $deleteQuery)) {
        echo json_encode(['Code' => 200, 'msg' => 'Panel unassigned successfully.']);
    } else {
        echo json_encode(['Code' => 500, 'msg' => 'Failed to unassign panel.', 'error' => mysqli_error($con)]);
    }
    
    exit();
}

// 4. GET ACTIVE MANAGERS FOR DROPDOWN
if (isset($_POST['get_active_managers'])) {
    $query = "SELECT id, branch_name, branch_code FROM branch_details WHERE status = 1 ORDER BY branch_name ASC";
    $result = mysqli_query($con, $query);
    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    echo json_encode(['Code' => 200, 'data' => $data]);
    exit();
}

// 5. GET UNASSIGNED PANELS FOR DROPDOWN
if (isset($_POST['get_available_panels'])) {
    // Select panels that don't exist in the mapping table
    $query = "
        SELECT p.panel_id 
        FROM panel_list p
        LEFT JOIN bm_panel_mapping m ON p.panel_id COLLATE utf8mb4_unicode_ci = m.panel_id COLLATE utf8mb4_unicode_ci
        WHERE m.id IS NULL
        ORDER BY p.panel_id ASC
    ";
    
    $result = mysqli_query($con, $query);
    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    echo json_encode(['Code' => 200, 'data' => $data]);
    exit();
}

echo json_encode(['Code' => 400, 'msg' => 'Invalid Request']);
?>
