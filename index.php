<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Full Calendar with TinyMCE</title>

    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/gg63dftxs904yq8t5rs5qyu8xo1wnzpfo1rflntk3u6ic37t/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Open modal and initialize TinyMCE
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.addEventListener('click', function () {
                    const selectedDate = this.getAttribute('data-date');
                    document.getElementById('selected-date').textContent = selectedDate;
                    document.getElementById('modal').style.display = 'block';

                    tinymce.init({
                        selector: '#editor',
                        plugins: 'link lists table wordcount',
                        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | numlist bullist | link',
                        menubar: false
                    });
                });
            });

            // Close modal
            document.getElementById('close-modal').addEventListener('click', () => {
                document.getElementById('modal').style.display = 'none';
                tinymce.remove('#editor');
            });

            // Save event data
            document.getElementById('save-btn').addEventListener('click', async () => {
                const date = document.getElementById('selected-date').textContent;
                const content = tinymce.get('editor').getContent();

                const response = await fetch('save_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ date, content }),
                });

                if (response.ok) {
                    alert('Event saved successfully!');
                    location.reload();
                } else {
                    alert('Failed to save event.');
                }
            });

            // Navigation buttons for month/year
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const newYear = btn.getAttribute('data-year');
                    const newMonth = btn.getAttribute('data-month');
                    window.location.href = `?year=${newYear}&month=${newMonth}`;
                });
            });
        });
    </script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9;
        }

        #calendar-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        #calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .nav-btn {
            cursor: pointer;
            padding: 5px 15px;
            border: 1px solid #ccc;
            background: #007bff;
            color: white;
            border-radius: 5px;
            font-size: 14px;
        }

        .nav-btn:hover {
            background: #0056b3;
        }

        #calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-header {
            font-weight: bold;
            text-align: center;
            background-color: #f3f3f3;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .calendar-day {
            padding: 20px;
            border: 1px solid #ddd;
            text-align: center;
            background: #f9f9f9;
            cursor: pointer;
            position: relative;
            border-radius: 5px;
        }

        .calendar-day:hover {
            background: #e0e0e0;
        }

        .event-indicator {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 10px;
            height: 10px;
            background: red;
            border-radius: 50%;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            position: relative;
        }

        #close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background: #007bff;
            color: white;
        }

        table td {
            background: #f9f9f9;
        }
    </style>
</head>

<body>
    <?php
    // Get the current month and year
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');

    $firstDayOfMonth = date('w', strtotime("$year-$month-01"));
    $daysInMonth = date('t', strtotime("$year-$month-01"));
    $prevMonth = $month - 1 == 0 ? 12 : $month - 1;
    $nextMonth = $month + 1 == 13 ? 1 : $month + 1;
    $prevYear = $month - 1 == 0 ? $year - 1 : $year;
    $nextYear = $month + 1 == 13 ? $year + 1 : $year;

    $events = $conn->query("SELECT event_date FROM events")->fetch_all(MYSQLI_ASSOC);
    $eventDates = array_column($events, 'event_date');
    ?>

    <div id="calendar-container">
        <div id="calendar-header">
            <button class="nav-btn" data-year="<?= $prevYear ?>" data-month="<?= $prevMonth ?>">Previous</button>
            <h2><?= date('F Y', strtotime("$year-$month-01")) ?></h2>
            <button class="nav-btn" data-year="<?= $nextYear ?>" data-month="<?= $nextMonth ?>">Next</button>
        </div>

        <div id="calendar">
            <?php
            // Week headers
            $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($daysOfWeek as $day) {
                echo "<div class='calendar-header'>$day</div>";
            }

            // Blank spaces for the first week
            for ($i = 0; $i < $firstDayOfMonth; $i++) {
                echo "<div></div>";
            }

            // Days with events
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $hasEvent = in_array($date, $eventDates) ? "<div class='event-indicator'></div>" : "";
                echo "<div class='calendar-day' data-date='$date'>$day $hasEvent</div>";
            }
            ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span id="close-modal">&times;</span>
            <h2>Add Event for <span id="selected-date"></span></h2>
            <textarea id="editor"></textarea>
            <button id="save-btn">Save</button>
        </div>
    </div>

    <!-- Display Events -->
    <div id="event-list">
        <h2>Event List</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Event</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['event_date']}</td>
                        <td>{$row['content']}</td>
                      </tr>";
            }
            ?>
        </table>
    </div>
</body>

</html>
