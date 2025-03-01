<?php
include 'db.php';
session_start();

// Retrieve teacher_name from session
if (!isset($_SESSION['teacher_name'])) {
    $teacherQuery = "SELECT DISTINCT teacher_name FROM exam WHERE teacher_id = ?";
    $teacherStmt = $conn->prepare($teacherQuery);
    $teacherStmt->execute([$_SESSION['teacher_id']]);
    $teacherData = $teacherStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($teacherData) {
        $_SESSION['teacher_name'] = $teacherData['teacher_name'];
    }
}

$teacherName = $_SESSION['teacher_name'] ?? '';

// Fetch unique exam titles and subjects
$examQuery = "SELECT DISTINCT exam_title FROM exam WHERE teacher_name = ?";
$examStmt = $conn->prepare($examQuery);
$examStmt->execute([$teacherName]);
$examTitles = $examStmt->fetchAll(PDO::FETCH_ASSOC);

$subjectQuery = "SELECT DISTINCT subject FROM exam WHERE teacher_name = ?";
$subjectStmt = $conn->prepare($subjectQuery);
$subjectStmt->execute([$teacherName]);
$subjects = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$examTitle = $_POST['exam_title'] ?? '';
$subject = $_POST['subject'] ?? '';
$selectedStudent = $_POST['student_name'] ?? '';

$showResults = (!empty($examTitle) && !empty($subject));
$result = [];
$result2 = [];
$completionTimes = []; // Store quiz and enhanced quiz completion times
$students = [];

if ($showResults) {
    // Query marks table
    $sql = "SELECT m.student_name, m.score, m.completion_time 
            FROM marks m
            JOIN exam e ON m.exam_title = e.exam_title AND m.subject = e.subject
            WHERE e.teacher_name = ? AND m.exam_title = ? AND m.subject = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$teacherName, $examTitle, $subject]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query marks2 table
    $sql2 = "SELECT m.student_name, m.score, m.completion_time 
            FROM marks2 m
            JOIN exam e ON m.exam_title = e.exam_title AND m.subject = e.subject
            WHERE e.teacher_name = ? AND m.exam_title = ? AND m.subject = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$teacherName, $examTitle, $subject]);
    $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Get unique student names
    $students = array_unique(array_merge(
        array_column($result, 'student_name'),
        array_column($result2, 'student_name')
    ));

    // Prepare data for the chart
    if (!empty($selectedStudent)) {
        $timeDifferences = []; // Store student names and their time differences
        foreach ($result as $quizRow) {
            foreach ($result2 as $enhancedRow) {
                if ($quizRow['student_name'] === $enhancedRow['student_name']) {
                    $timeDifference = abs($quizRow['completion_time'] - $enhancedRow['completion_time']);
                    $timeDifferences[] = [
                        'student_name' => $quizRow['student_name'],
                        'time_difference' => $timeDifference
                    ];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
</head>
<body class="bg-green-50 flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="sticky top-0 bg-green-700 text-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a class="text-2xl font-bold tracking-wide" href="#">Edu Learn</a>
            <div class="flex items-center">
                <a href="teacherr.php" class="text-white hover:text-gray-200 transition">Profile</a>
            </div>
        </div>
    </nav>

    <!-- Leaderboard Section -->
    <div class="flex-grow flex flex-col items-center px-4 py-6">
        <div class="w-full max-w-4xl bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-bold text-green-700 text-center mb-6">Leaderboard</h2>
            
            <!-- Selection Form -->
            <form method="POST" class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-center">
                <select name="exam_title" class="p-2 border border-green-300 rounded-md" required>
                    <option value="">Select Exam Title</option>
                    <?php foreach ($examTitles as $row) { ?>
                        <option value="<?= $row['exam_title']; ?>" <?= ($examTitle == $row['exam_title']) ? 'selected' : ''; ?>>
                            <?= $row['exam_title']; ?>
                        </option>
                    <?php } ?>
                </select>

                <select name="subject" class="p-2 border border-green-300 rounded-md" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $row) { ?>
                        <option value="<?= $row['subject']; ?>" <?= ($subject == $row['subject']) ? 'selected' : ''; ?>>
                            <?= $row['subject']; ?>
                        </option>
                    <?php } ?>
                </select>

                <select name="student_name" class="p-2 border border-green-300 rounded-md">
                    <option value="">Select Student</option>
                    <?php foreach ($students as $student) { ?>
                        <option value="<?= $student; ?>" <?= ($selectedStudent == $student) ? 'selected' : ''; ?>>
                            <?= $student; ?>
                        </option>
                    <?php } ?>
                </select>

                <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded-md hover:bg-green-800 transition">
                    Show Marks
                </button>
            </form>

            <?php if ($showResults) { ?>
                <!-- Leaderboard Table for Quiz Marks -->
                <h3 class="text-xl font-bold text-green-700 text-center mt-6">Quiz Marks</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="w-full border-collapse border border-green-300 rounded-lg">
                        <thead>
                            <tr class="bg-green-700 text-white text-left">
                                <th class="px-5 py-3">Student Name</th>
                                <th class="px-5 py-3">Score</th>
                                <th class="px-5 py-3">Completion Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($result)) {
                                foreach ($result as $row) {
                                    echo "<tr class='border-b border-green-200 hover:bg-green-100 transition'>";
                                    echo "<td class='px-5 py-3 text-gray-900 font-medium'>{$row['student_name']}</td>";
                                    echo "<td class='px-5 py-3 text-gray-900'>{$row['score']}</td>";
                                    echo "<td class='px-5 py-3 text-gray-900'>{$row['completion_time']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='px-5 py-3 text-center text-gray-900'>No records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Leaderboard Table for Enhanced Quiz Marks -->
                <h3 class="text-xl font-bold text-green-700 text-center mt-6">Enhanced Quiz Marks</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-green-300 rounded-lg">
                        <thead>
                            <tr class="bg-green-700 text-white text-left">
                                <th class="px-5 py-3">Student Name</th>
                                <th class="px-5 py-3">Score</th>
                                <th class="px-5 py-3">Completion Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($result2)) {
                                foreach ($result2 as $row) {
                                    echo "<tr class='border-b border-green-200 hover:bg-green-100 transition'>";
                                    echo "<td class='px-5 py-3 text-gray-900 font-medium'>{$row['student_name']}</td>";
                                    echo "<td class='px-5 py-3 text-gray-900'>{$row['score']}</td>";
                                    echo "<td class='px-5 py-3 text-gray-900'>{$row['completion_time']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='px-5 py-3 text-center text-gray-900'>No records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Scatter Chart for Quiz vs Enhanced Quiz Completion Times -->
                <!-- ... (rest of your HTML code) -->

                <?php if (!empty($selectedStudent)) { ?>
                    <h3 class="text-xl font-bold text-green-700 text-center mt-6">Time Difference Between Quiz and Enhanced Quiz for <?= $selectedStudent; ?></h3>
                    <div class="mt-6">
                        <canvas id="timeDifferenceChart"></canvas>
                    </div>

                    <script>
                        // Prepare data for the chart
                        const timeDifferences = <?php echo json_encode($timeDifferences); ?>;

                        const studentNames = timeDifferences.map(item => item.student_name);
                        const timeDiffs = timeDifferences.map(item => item.time_difference);

                        // Render the bar chart
                        const ctx = document.getElementById('timeDifferenceChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar', // Use a bar chart for better readability
                            data: {
                                labels: studentNames, // Student names on the x-axis
                                datasets: [{
                                    label: 'Time Difference (seconds)',
                                    data: timeDiffs, // Time differences on the y-axis
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Student Name'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Time Difference (seconds)'
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const studentName = context.label;
                                                const timeDiff = context.raw;
                                                return `${studentName}: ${timeDiff}s`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php } ?>
            <?php } else { ?>
                <p class="text-center text-gray-700 mt-6">Please select both Exam Title and Subject to view marks.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
