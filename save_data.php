<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$date = $data['date'];
$content = $data['content'];

$stmt = $conn->prepare("INSERT INTO events (event_date, content) VALUES (?, ?)");
$stmt->bind_param('ss', $date, $content);
if ($stmt->execute()) {
    echo "Event saved successfully!";
} else {
    echo "Failed to save event.";
}
$stmt->close();
$conn->close();
?>
