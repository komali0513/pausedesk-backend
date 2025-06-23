<?php
include 'config.php';
include 'cors.php';

// Get POST body data
$data = json_decode(file_get_contents("php://input"));

$leave_id = $data->leave_id ?? '';
$status = $data->status ?? '';
$hod_reason = $data->hod_reason ?? '';

if (!$leave_id || !$status) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// Update leave status
$sql = "UPDATE leaves 
        SET status = '$status', hod_reason = '$hod_reason' 
        WHERE id = $leave_id";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "Leave $status successfully"]);
} else {
    echo json_encode(["error" => "Database update failed"]);
}
?>
