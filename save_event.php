<?php
include 'db.php';

// Get JSON data from fetch
$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'];
$content = $data['content'];

// Insert event into the database
$stmt = $conn->prepare("INSERT INTO events (event_date, content) VALUES (?, ?)");
$stmt->bind_param('ss', $date, $content);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>
