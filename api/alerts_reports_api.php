<?php

include __DIR__ . "/config/config.php";

header("Access-Control-Allow-Origin: *");

try {
    if (!$con) {
        throw new Exception("Database connection failed.");
    }

    $action = isset($_POST["action"])
        ? strtolower(trim($_POST["action"]))
        : "report";

    $page =
        isset($_POST["Page"]) && is_numeric($_POST["Page"])
            ? (int) $_POST["Page"]
            : 1;

    $records_per_page =
        isset($_POST["perpg"]) &&
        in_array($_POST["perpg"], [10, 25, 50, 75, 100])
            ? (int) $_POST["perpg"]
            : 25;

    $offset = ($page - 1) * $records_per_page;

    $date_condition = "1=1";

    $filter = isset($_POST["filter"])
        ? strtolower(trim($_POST["filter"]))
        : "day";

    switch ($filter) {
        case "week":
            $date_condition =
                "YEARWEEK(a.requested_at,1)=YEARWEEK(CURDATE(),1)";
            break;

        case "month":
            $date_condition = "MONTH(a.requested_at)=MONTH(CURDATE())
                 AND YEAR(a.requested_at)=YEAR(CURDATE())";
            break;

        case "custom":
            $from_date = isset($_POST["from_date"])
                ? mysqli_real_escape_string($con, $_POST["from_date"])
                : "";

            $to_date = isset($_POST["to_date"])
                ? mysqli_real_escape_string($con, $_POST["to_date"])
                : "";

            if (!empty($from_date) && !empty($to_date)) {
                $date_condition = "DATE(a.requested_at)
                     BETWEEN '$from_date'
                     AND '$to_date'";
            }

            break;

        case "day":
        default:
            $date_condition = "DATE(a.requested_at)=CURDATE()";
    }

    $countQuery = "
        SELECT COUNT(*) AS total
        FROM alert_otp_request a
        WHERE $date_condition
    ";

    $countResult = mysqli_query($con, $countQuery);

    if (!$countResult) {
        throw new Exception(mysqli_error($con));
    }

    $countRow = mysqli_fetch_assoc($countResult);

    $total_records = (int) $countRow["total"];

    $total_pages = ceil($total_records / $records_per_page);

    $query = "
        SELECT
            a.id,
            a.user_id,
            a.panel_id,
            a.type_access,
            a.requested_at,
            a.requested_status,
            a.remark,
            a.updated_at,
            a.updated_by,
            a.latitude,
            a.longitude,
            a.location,
            u.name AS requester_name,
            u.contact_no AS requester_contact,
            b.branch_name,
            up.name AS updater_name
        FROM alert_otp_request a
        LEFT JOIN user_login u ON a.user_id = u.id
        LEFT JOIN branch_code b ON a.panel_id = b.branch_code
        LEFT JOIN user_login up ON a.updated_by = up.id
        WHERE $date_condition
        ORDER BY a.id DESC
    ";

    if ($action != "excel") {
        $query .= " LIMIT $offset, $records_per_page";
    }

    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Parse ClientName and SiteName from branch_name
        $client_name = 'V-Protect';
        $site_name = $row['branch_name'] ?? '';
        if (!empty($row['branch_name'])) {
            $parts = explode('-', $row['branch_name'], 2);
            if (count($parts) > 1) {
                $client_name = trim($parts[0]);
                $site_name = trim($parts[1]);
            } else {
                $parts_space = explode(' ', $row['branch_name'], 2);
                if (count($parts_space) > 1) {
                    $client_name = trim($parts_space[0]);
                    $site_name = trim($parts_space[1]);
                }
            }
        }

        $device_name = $site_name;
        $device_number = $row['panel_id'] ?? '';
        $otp = '-'; // blank or dash
        $send_to = $row['requester_name'] ?? '';
        $send_by = $row['updater_name'] ?? ($row['updated_by'] ? 'User ID: ' . $row['updated_by'] : '-');
        $send_at = ($row['requested_status'] != 0) ? $row['updated_at'] : '-';
        $requested_time = $row['requested_at'] ?? '';

        // Wait time calculation
        $wait_time = '-';
        if ($row['requested_status'] != 0 && !empty($row['updated_at']) && !empty($row['requested_at'])) {
            $diff = strtotime($row['updated_at']) - strtotime($row['requested_at']);
            if ($diff < 0) $diff = 0;
            if ($diff < 60) {
                $wait_time = $diff . 's';
            } else {
                $mins = floor($diff / 60);
                $secs = $diff % 60;
                $wait_time = $mins . 'm ' . $secs . 's';
            }
        }

        $mobile_no = $row['requester_contact'] ?? '';

        // Status mapping
        $status_display = 'Un Used';
        if ($row['requested_status'] == 1) {
            $status_display = 'Used';
        } elseif ($row['requested_status'] == 2) {
            $status_display = 'Rejected';
        }

        $otp_type = 'Online';

        // Reason (access type + rejection remark if any)
        $reason = $row['type_access'] ?? '';
        if ($row['requested_status'] == 2 && !empty($row['remark'])) {
            $reason .= ' (Rejected: ' . $row['remark'] . ')';
        }

        // Add formatted fields to the row
        $row['client_name'] = $client_name;
        $row['site_name'] = $site_name;
        $row['device_name'] = $device_name;
        $row['device_number'] = $device_number;
        $row['otp'] = $otp;
        $row['send_to'] = $send_to;
        $row['send_by'] = $send_by;
        $row['send_at'] = $send_at;
        $row['requested_time'] = $requested_time;
        $row['wait_time'] = $wait_time;
        $row['mobile_no'] = $mobile_no;
        $row['status_display'] = $status_display;
        $row['otp_type'] = $otp_type;
        $row['reason'] = $reason;

        // Backward compatibility
        $row['name'] = $row['requester_name'] ?? '';
        $row['contact_no'] = $row['requester_contact'] ?? '';
        switch ($row["requested_status"]) {
            case 0:
                $row["status_text"] = "Pending";
                break;
            case 1:
                $row["status_text"] = "Accepted";
                break;
            case 2:
                $row["status_text"] = "Rejected";
                break;
            default:
                $row["status_text"] = "Unknown";
        }

        $data[] = $row;
    }

    if ($action == "excel") {
        // Disable displaying warnings or notices to prevent CSV corruption
        ini_set('display_errors', 0);
        error_reporting(0);

        header("Content-Type: text/csv");
        header('Content-Disposition: attachment; filename=otp_report.csv');

        $output = fopen("php://output", "w");

        // Put correct headers with explicit parameters to avoid deprecation warnings in PHP 8.x
        fputcsv($output, [
            "ClientName",
            "SiteName",
            "DeviceName",
            "DeviceNumber",
            "OTP",
            "SendTo",
            "SendBy",
            "SendAt",
            "RequestedTime",
            "Wait time",
            "MobileNo",
            "Status",
            "OTPType",
            "Reason"
        ], ",", '"', "\\");

        foreach ($data as $row) {
            fputcsv($output, [
                $row["client_name"],
                $row["site_name"],
                $row["device_name"],
                $row["device_number"],
                $row["otp"],
                $row["send_to"],
                $row["send_by"],
                $row["send_at"],
                $row["requested_time"],
                $row["wait_time"],
                $row["mobile_no"],
                $row["status_display"],
                $row["otp_type"],
                $row["reason"]
            ], ",", '"', "\\");
        }

        fclose($output);
        exit();
    }

    $showing_from = count($data) ? $offset + 1 : 0;

    $showing_to = $offset + count($data);

    echo json_encode([
        "Code" => count($data) ? 200 : 201,

        "msg" => count($data) ? "Data found" : "No data found",

        "current_page" => $page,

        "per_page" => $records_per_page,

        "showing_from" => $showing_from,

        "showing_to" => $showing_to,

        "total_records" => $total_records,

        "total_pages" => $total_pages,

        "data" => $data,
    ]);
} catch (Exception $e) {
    echo json_encode([
        "Code" => 500,

        "msg" => $e->getMessage(),

        "data" => [],
    ]);
}

exit();

?>
