<?php
include(__DIR__ . '/config/config.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');


// ob_clean();
$created_at = date('Y-m-d H:i:s');
$todays_date = date('Y-m-d');

$response = array();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_branch'])) {

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


    $user_id = $_POST['user_id'];
    $branch_name = isset($_POST['branch_name']) ? mysqli_real_escape_string($con, $_POST['branch_name']) : ''; // Manager Name
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : '';
    $contact = isset($_POST['contact']) ? mysqli_real_escape_string($con, $_POST['contact']) : '';
    $branch_code = isset($_POST['branch_code']) ? mysqli_real_escape_string($con, $_POST['branch_code']) : ''; // Added

    $selectQuery = "SELECT id FROM branch_details WHERE email = '" . $email . "' ";

    if (mysqli_num_rows(mysqli_query($con, $selectQuery)) > 0) {
        $response = [
            'Code' => 409,
            'msg' => 'Manager email already exists.'
        ];
        echo json_encode($response);
        exit();
    }
    else {
        // We inject branch_code temporarily into an unused spot or assume the column exists (dynamically schema-dependent)
        // Since we didn't explicitly alter table, let's assume `branch_code` column has been or will be added to `branch_details`
        $insertQuery = "INSERT INTO branch_details (branch_name, contact, email, status, created_at, created_by, branch_code) VALUES ('" . $branch_name . "','" . $contact . "','" . $email . "','1','" . $created_at . "','" . $user_id . "', '" . $branch_code . "')";
        
    
        if (mysqli_query($con, $insertQuery)) {
            
            $created_branch_details_id = $con->insert_id ; 
            
            mysqli_query($con,"insert into user_login(name,password,contact_no,email_id,user_role,status,work_hr,created_at,updated_at) 
            values('".$branch_name."','".$contact."','".$contact."','".$email."',8,1,9,'".$created_at."','".$created_at."')");
            
            $created_user_id = $con->insert_id ;
            
            mysqli_query($con,"update branch_details set created_user_id = '".$created_user_id."' where id='".$created_branch_details_id."'");
            
            
            $response = [
                'Code' => 200,
                'msg' => 'Data saved successfully'
            ];
        }
        else {
            $response = [
                'Code' => 500,
                'msg' => 'Failed to save details. Make sure branch_code column exists in branch_details table.',
                'con_error' => mysqli_error($con)
            ];
        }

    }

    echo json_encode($response);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_all_branch'])) {

    // DataTables parameters
    $limit = isset($_POST['length']) ? (int)$_POST['length'] : 10;
    $offset = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;

    // Search parameter
    $searchValue = isset($_POST['search']['value']) ? mysqli_real_escape_string($con, $_POST['search']['value']) : '';

    $whereClause = "WHERE status = 1";
    if (!empty($searchValue)) {
        $whereClause .= " AND (branch_name LIKE '%$searchValue%' OR email LIKE '%$searchValue%' OR contact LIKE '%$searchValue%' OR branch_code LIKE '%$searchValue%')";
    }

    // Order parameters 
    $orderBy = "ORDER BY id DESC";
    $columns = array(
        0 => 'id',
        1 => 'branch_name',
        2 => 'branch_code',
        3 => 'email',
        4 => 'contact',
        5 => 'status'
    );
    if (isset($_POST['order'])) {
        $columnIndex = $_POST['order'][0]['column'];
        $columnName = isset($columns[$columnIndex]) ? $columns[$columnIndex] : 'id';
        $columnSortOrder = $_POST['order'][0]['dir'] === 'asc' ? 'asc' : 'desc';
        $orderBy = "ORDER BY $columnName $columnSortOrder";
    }

    // Count total records
    $count_query = mysqli_query($con, "SELECT COUNT(*) as total_records FROM branch_details WHERE status = 1");
    $count_data = mysqli_fetch_assoc($count_query);
    $recordsTotal = (int)$count_data['total_records'];

    // Count records after filter
    if (!empty($searchValue)) {
        $filter_query = mysqli_query($con, "SELECT COUNT(*) as filtered_records FROM branch_details $whereClause");
        $filter_data = mysqli_fetch_assoc($filter_query);
        $recordsFiltered = (int)$filter_data['filtered_records'];
    }
    else {
        $recordsFiltered = $recordsTotal;
    }

    // Fetch Managers along with their associated branch code details if available. 
    $selectQuery = "SELECT * FROM branch_details $whereClause $orderBy " . ($limit > 0 ? "LIMIT $offset, $limit" : "");
    $result = mysqli_query($con, $selectQuery);

    $leads = array();
    if ($result && mysqli_num_rows($result) > 0) {
        $sr = $offset + 1;
        while ($row = mysqli_fetch_assoc($result)) {

            $statusBadge = $row['status'] == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';

            $branch_id = htmlspecialchars($row['id'] ?? '', ENT_QUOTES);
            $safeName = htmlspecialchars($row['branch_name'] ?? '', ENT_QUOTES);
            $safeBranchCode = htmlspecialchars($row['branch_code'] ?? '', ENT_QUOTES);
            $safeEmail = htmlspecialchars($row['email'] ?? '', ENT_QUOTES);
            $safeContact = htmlspecialchars($row['contact'] ?? '', ENT_QUOTES);




            $actionButtons = "
            <div class='btn-group btn-group-sm' role='group'>
                <button class='btn btn-outline-primary' onclick=\"openEditModal('{$branch_id}', '{$safeName}', '{$safeBranchCode}', '{$safeEmail}', '{$safeContact}')\" title='Edit'>
                    <i class='ph ph-pencil-simple'></i>
                </button>
                <button class='btn btn-outline-danger' onclick=\"deleteManager('{$branch_id}')\" title='Delete'>
                    <i class='ph ph-trash'></i>
                </button>
            </div>";

            $leads[] = [
                "id"=>$branch_id,
                "sr_no" => $sr++,
                "branch_name" => $row['branch_name'] ?? '',
                "branch_code" => '<span class="badge border border-primary text-primary bg-light">' . ($row['branch_code'] ?? 'Not Assigned') . '</span>',
                "email" => $row['email'] ?? '',
                "contact" => $row['contact'] ?? '',
                "status" => $statusBadge,
                "action" => $actionButtons
            ];
        }
    }

    $response = [
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $leads,
        'Code' => 200
    ];

    echo json_encode($response);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branch'])) {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($con, $_POST['id']) : '';
    $branch_name = isset($_POST['branch_name']) ? mysqli_real_escape_string($con, $_POST['branch_name']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : '';
    $contact = isset($_POST['contact']) ? mysqli_real_escape_string($con, $_POST['contact']) : '';
    $branch_code = isset($_POST['branch_code']) ? mysqli_real_escape_string($con, $_POST['branch_code']) : ''; // added

    $updateQuery = "UPDATE branch_details SET branch_name = '" . $branch_name . "' ,contact='" . $contact . "',email='" . $email . "', branch_code='" . $branch_code . "', updated_at='" . $created_at . "' WHERE id = '" . $id . "' ";

    if (mysqli_query($con, $updateQuery)) {
        $response = [
            'Code' => 200,
            'msg' => 'Manager updated successfully'
        ];
    }
    else {
        $response = [
            'Code' => 500,
            'msg' => 'Failed to update branch',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    $id = mysqli_real_escape_string($con, $_POST['id']);

    $deleteQuery = "DELETE FROM branch_details WHERE id = '$id'";
    $result = mysqli_query($con, $deleteQuery);

    if ($result) {
        $response = [
            'Code' => 200,
            'message' => 'Deleted Successfully'
        ];
    }
    else {
        $response = [
            'Code' => 400,
            'message' => 'Delete Failed',
            'con_error' => mysqli_error($con)
        ];
    }

    echo json_encode($response);
    exit();
}


// NEW ENDPOINT: Fetch active Branch Codes for the UI Dropdown
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_active_branch_codes'])) {
    $selectQuery = "SELECT id, branch_code, branch_name FROM branch_code WHERE status = 1 ORDER BY branch_name ASC";
    $result = mysqli_query($con, $selectQuery);

    $branches = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $branches[] = $row;
        }
    }

    $response = [
        'Code' => 200,
        'data' => $branches
    ];

    echo json_encode($response);
    exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>