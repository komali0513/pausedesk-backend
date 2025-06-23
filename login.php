<?php
include 'config.php';

$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;
$role = $data->role; // 'faculty' or 'hod'

$table = $role === "faculty" ? "faculty" : "hod";

$sql = "SELECT * FROM $table WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "Invalid credentials"]);
}
$conn->close();
?>
