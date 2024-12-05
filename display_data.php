<?php
include 'db.php';

$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
while ($row = $result->fetch_assoc()) {
    echo "<div class='event'>
            <h3>{$row['event_date']}</h3>
            <p>{$row['content']}</p>
          </div>";
}
$conn->close();
?>
