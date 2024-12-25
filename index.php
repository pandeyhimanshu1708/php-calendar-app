<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Calendar with TinyMCE</title>

    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/gg63dftxs904yq8t5rs5qyu8xo1wnzpfo1rflntk3u6ic37t/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Open modal and initialize TinyMCE
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.addEventListener('click', function() {
                    const selectedDate = this.getAttribute('data-date');
                    document.getElementById('selected-date').textContent = selectedDate;
                    document.getElementById('modal').style.display = 'flex';

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
                    body: JSON.stringify({
                        date,
                        content
                    }),
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
            color: #333;
        }

        #calendar-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow: hidden;
        }

        #calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .nav-btn {
            cursor: pointer;
            padding: 8px 20px;
            border: none;
            background: #007bff;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s ease;
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
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
            background: #f9f9f9;
            cursor: pointer;
            position: relative;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .calendar-day:hover {
            background: #e0e0e0;
        }

        .calendar-day.highlight {
            background: #ffd700;
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            position: relative;
        }

        #close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
            color: #999;
        }

        #close-modal:hover {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow-x: auto;
        }

        table th,
        table td {
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

        @media (max-width: 768px) {
            #calendar {
                grid-template-columns: repeat(2, 1fr);
            }

            .calendar-day {
                padding: 10px;
            }

            .nav-btn {
                margin: 5px 0;
            }

            table th,
            table td {
                padding: 5px;
                font-size: 12px;
            }

            #calendar-container {
                padding: 10px;
            }

            .modal-content {
                max-width: 90%;
            }
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
    $currentDate = date('Y-m-d');
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
                $date = "$year-$month-" .str_pad($day, 2, '0', STR_PAD_LEFT);
                $isToday = $date === $currentDate;
                $hasEvent = in_array($date, $eventDates);
                $highlightClass = $isToday ? 'highlight' : '';
                $eventIndicator = $hasEvent ? '<span class="event-indicator"></span>' : '';

                echo "<div class='calendar-day $highlightClass' data-date='$date'>$day $eventIndicator</div>";
            }
            ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Event</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $events = $conn->query("SELECT event_date, content FROM events");
                while ($row = $events->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['event_date']}</td>
                            <td>{$row['content']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="modal" id="modal">
        <div class="modal-content">
            <span id="close-modal">&times;</span>
            <h2 id="selected-date"></h2>
            <textarea id="editor"></textarea>
            <button id="save-btn">Save Event</button>
        </div>
    </div>
</body>

</html>

