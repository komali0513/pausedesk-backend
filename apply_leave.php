<?php
// ✅ CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// ✅ Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Include DB connection
include 'config.php';

// ✅ Include mail function
require_once 'send_mail.php';

// ✅ Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input);

// ✅ Validate input
if (!$data) {
    echo json_encode(["error" => "No input received or invalid JSON"]);
    exit();
}

$faculty_id = $data->faculty_id ?? null;
$leave_type = $data->leave_type ?? null;
$start_date = $data->start_date ?? null;
$end_date = $data->end_date ?? null;
$reason = $data->reason ?? null;

if (!$faculty_id || !$leave_type || !$start_date || !$end_date || !$reason) {
    echo json_encode(["error" => "Missing required fields"]);
    exit();
}

// ✅ Insert into database
$sql = "INSERT INTO leaves (faculty_id, leave_type, start_date, end_date, reason, status)
        VALUES ('$faculty_id', '$leave_type', '$start_date', '$end_date', '$reason', 'Pending')";

if ($conn->query($sql) === TRUE) {
    // ✅ Fetch faculty name
    $facultyQuery = $conn->query("SELECT name FROM faculty WHERE id = $faculty_id");
    $facultyRow = $facultyQuery->fetch_assoc();
    $faculty_name = $facultyRow['name'];

    // ✅ Fetch HOD email
    $hodResult = $conn->query("SELECT email FROM hod LIMIT 1");
    if ($hodResult && $hodResult->num_rows > 0) {
        $hodEmail = $hodResult->fetch_assoc()['email'];

        // ✅ Compose email
        $subject = "New Leave Application from $faculty_name";
        $body = "Dear HOD,\n\n"
              . "$faculty_name has applied for leave:\n"
              . "- Type: $leave_type\n"
              . "- From: $start_date\n"
              . "- To: $end_date\n"
              . "- Reason: $reason\n\n"
              . "Please login to pauseDesk to review and take action.\n\n"
              . "Regards,\npauseDesk System";

        // ✅ Send email
        sendMail($hodEmail, $subject, $body);
    }

    echo json_encode(["message" => "Leave applied successfully and HOD notified"]);
} else {
    echo json_encode(["error" => "Database error: " . $conn->error]);
}

$conn->close();
