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

$showResults = (!empty($examTitle) && !empty($subject));
$result = [];
$result2 = [];

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
}

// Prepare data for Chart.js
$students = [];
$quizScores = [];
$enhancedScores = [];
$quizTimes = [];
$enhancedTimes = [];

foreach ($result as $row) {
    $students[$row['student_name']] = $row;
}
foreach ($result2 as $row) {
    $students[$row['student_name']] = array_merge($students[$row['student_name']] ?? [], $row);
}

// Populate arrays
foreach ($students as $student => $data) {
    $quizScores[] = $data['score'] ?? 0;
    $enhancedScores[] = $data['score'] ?? 0;
    $quizTimes[] = $data['completion_time'] ?? 0;
    $enhancedTimes[] = $data['completion_time'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <?php } else { ?>
                <p class="text-center text-gray-700 mt-6">Please select both Exam Title and Subject to view marks.</p>
            <?php } ?>
        </div>


        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Chart Section -->
        <div class="w-full max-w-4xl mx-auto mt-8 bg-white shadow-lg rounded-lg p-6">
            <h3 class="text-xl font-bold text-green-700 text-center">Performance Comparison</h3>
            <canvas id="scoreChart"></canvas>
            <canvas id="timeChart" class="mt-6"></canvas>
        </div>

        <script>
            const ctx1 = document.getElementById('scoreChart').getContext('2d');
            const ctx2 = document.getElementById('timeChart').getContext('2d');

            const students = <?= json_encode(array_keys($students)) ?>;
            const quizScores = <?= json_encode($quizScores) ?>;
            const enhancedScores = <?= json_encode($enhancedScores) ?>;
            const quizTimes = <?= json_encode($quizTimes) ?>;
            const enhancedTimes = <?= json_encode($enhancedTimes) ?>;

            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: students,
                    datasets: [
                        { label: 'Quiz Marks', data: quizScores, backgroundColor: 'rgba(75, 192, 192, 0.6)' },
                        { label: 'Enhanced Quiz Marks', data: enhancedScores, backgroundColor: 'rgba(255, 99, 132, 0.6)' }
                    ]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: students,
                    datasets: [
                        { label: 'Quiz Completion Time', data: quizTimes, borderColor: 'blue', fill: false },
                        { label: 'Enhanced Quiz Completion Time', data: enhancedTimes, borderColor: 'red', fill: false }
                    ]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });
        </script>
    </div>
</body>
</html>
