<?php
include 'config.php';
include 'cors.php';
require_once 'send_mail.php'; // use require_once for safety

// Read the JSON payload from frontend
$data = json_decode(file_get_contents("php://input"), true);

// Extract data with default fallback
$id = $data['id'] ?? '';
$status = $data['status'] ?? '';
$hod_reason = $data['hod_reason'] ?? '';

if (empty($id) || empty($status) || empty($hod_reason)) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

// ✅ 1. Update leave status in the database
$sql = "UPDATE leaves SET status = ?, hod_reason = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $status, $hod_reason, $id);

if ($stmt->execute()) {
    // ✅ 2. Get faculty details to send email
    $query = "SELECT f.email, f.name, l.leave_type, l.start_date, l.end_date 
              FROM leaves l 
              JOIN faculty f ON l.faculty_id = f.id 
              WHERE l.id = ?";
    $stmt2 = $conn->prepare($query);
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $result = $stmt2->get_result();

    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $name = $row['name'];
        $leave_type = $row['leave_type'];
        $start = $row['start_date'];
        $end = $row['end_date'];

        // ✅ 3. Prepare email content
        $subject = "Leave Application $status - pauseDesk";
        $body = "Hi $name,\n\nYour leave application for *$leave_type* from $start to $end has been **$status** by the HOD.\n\nHOD's Reason: $hod_reason\n\nRegards,\npauseDesk Team";

        // ✅ 4. Send email
        sendMail($email, $subject, $body);
    }

    echo json_encode(["message" => "Leave $status successfully"]);
} else {
    echo json_encode(["error" => "Error updating leave status"]);
}
?>
