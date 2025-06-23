<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

include 'config.php';

$role = $_GET['role'] ?? null;

if ($role === 'hod') {
    // HOD is requesting pending leaves with faculty names
    $sql = "SELECT leaves.*, faculty.name AS faculty_name 
            FROM leaves 
            JOIN faculty ON leaves.faculty_id = faculty.id 
            WHERE status = 'Pending' 
            ORDER BY applied_at DESC";

    $result = $conn->query($sql);
    $leaves = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $leaves[] = $row;
        }
        echo json_encode($leaves);
    } else {
        echo json_encode(["error" => "Query error: " . $conn->error]);
    }

} else if (isset($_GET['faculty_id'])) {
    // Normal leave history for a faculty
    $faculty_id = $_GET['faculty_id'];
    $sql = "SELECT * FROM leaves WHERE faculty_id = ? ORDER BY applied_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $leaves = [];
    while ($row = $result->fetch_assoc()) {
        $leaves[] = $row;
    }
    echo json_encode($leaves);
    $stmt->close();

} else {
    echo json_encode(["error" => "Missing faculty_id or invalid role"]);
}

$conn->close();
?>
