<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'services');

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$service_id = $_GET['service_id'];
$result = $conn->query("SELECT id, name FROM specializations WHERE service_id = $service_id");

$specializations = [];
while ($row = $result->fetch_assoc()) {
    $specializations[] = $row;
}

echo json_encode($specializations);
$conn->close();
?>
