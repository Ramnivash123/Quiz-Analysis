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

$showResults = (!empty($examTitle) && !empty($subject)); // Fixed syntax error
$quizTimesAssoc = [];
$enhancedTimesAssoc = [];
$students = [];

if ($showResults) {
    // Query marks tables and create associative arrays
    $sql = "SELECT m.student_name, m.completion_time 
            FROM marks m
            JOIN exam e ON m.exam_title = e.exam_title AND m.subject = e.subject
            WHERE e.teacher_name = ? AND m.exam_title = ? AND m.subject = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$teacherName, $examTitle, $subject]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $quizTimesAssoc[$row['student_name']] = round($row['completion_time'] / 60, 2);
    }

    $sql2 = "SELECT m.student_name, m.completion_time 
            FROM marks2 m
            JOIN exam e ON m.exam_title = e.exam_title AND m.subject = e.subject
            WHERE e.teacher_name = ? AND m.exam_title = ? AND m.subject = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$teacherName, $examTitle, $subject]);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $enhancedTimesAssoc[$row['student_name']] = round($row['completion_time'] / 60, 2);
    }

    // Get and sort unique student names
    $students = array_unique(array_merge(
        array_keys($quizTimesAssoc),
        array_keys($enhancedTimesAssoc)
    ));
    sort($students);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Edu Learn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="flex flex-col min-h-screen bg-white">
    <!-- Navigation -->
    <nav class="sticky top-0 bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a class="text-2xl font-bold" href="#">Edu Learn</a>
                
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-8 text-green-800">Leaderboard</h1>

        <!-- Form -->
        <form method="POST" class="bg-green-50 p-6 rounded-lg shadow-md mb-8">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="exam_title" class="block text-sm font-medium text-green-800">Exam Title</label>
                    <select name="exam_title" id="exam_title" class="mt-1 block w-full p-2 border border-green-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">Select Exam Title</option>
                        <?php foreach ($examTitles as $row) { ?>
                            <option value="<?= $row['exam_title'] ?>" <?= $examTitle === $row['exam_title'] ? 'selected' : '' ?>>
                                <?= $row['exam_title'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-green-800">Subject</label>
                    <select name="subject" id="subject" class="mt-1 block w-full p-2 border border-green-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $row) { ?>
                            <option value="<?= $row['subject'] ?>" <?= $subject === $row['subject'] ? 'selected' : '' ?>>
                                <?= $row['subject'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                Show Results
            </button>
        </form>

        <?php if ($showResults) { ?>
            <!-- Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <canvas id="completionChart"></canvas>
            </div>

            <!-- Table -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <table class="w-full">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="p-3 text-left">Student Name</th>
                            <th class="p-3 text-left">Quiz Time (minutes)</th>
                            <th class="p-3 text-left">Enhanced Quiz Time (minutes)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) { ?>
                            <tr class="border-b border-green-200 hover:bg-green-50 transition">
                                <td class="p-3"><?= htmlspecialchars($student) ?></td>
                                <td class="p-3"><?= $quizTimesAssoc[$student] ?? 'N/A' ?></td>
                                <td class="p-3"><?= $enhancedTimesAssoc[$student] ?? 'N/A' ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Chart Script -->
            <script>
                const ctx = document.getElementById('completionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($students) ?>,
                        datasets: [{
                            label: 'Standard Quiz Time',
                            data: <?= json_encode(array_values(array_replace(array_fill_keys($students, null), $quizTimesAssoc))) ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Enhanced Quiz Time',
                            data: <?= json_encode(array_values(array_replace(array_fill_keys($students, null), $enhancedTimesAssoc))) ?>,
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'x',
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Completion Time (minutes)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Students'
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Quiz Completion Time Comparison'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.raw} mins`;
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        <?php } else { ?>
            <p class="text-center text-gray-600">Please select both Exam Title and Subject to view results.</p>
        <?php } ?>
    </main>

    
</body>
</html>
