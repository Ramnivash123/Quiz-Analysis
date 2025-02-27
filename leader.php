<?php
session_start();
include 'db.php'; // Ensure this file contains the PDO database connection logic

// Fetch all unique subjects and exam titles for the dropdown
$sql = "SELECT DISTINCT subject, exam_title FROM marks";
$stmt = $conn->query($sql);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

$leaderboard = [];

// Handle form submission to filter by subject and exam title
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    $subject = $_POST['subject'];
    $exam_title = $_POST['exam_title'];

    // Fetch leaderboard data based on selected subject and exam title
    $sql = "SELECT student_name, score, completion_time FROM marks 
            WHERE subject = :subject AND exam_title = :exam_title 
            ORDER BY score DESC, completion_time ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':exam_title', $exam_title);
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="sticky top-0 bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a class="text-2xl font-bold" href="#">Edu Learn</a>
                <a href="teacherr.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i class="fas fa-user mr-2"></i>
                    <span>Profile</span>
                </a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-green-800 text-center mb-6">Leaderboard</h1>

            <!-- Form to filter by subject and exam title -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="space-y-6">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-green-700 mb-2">Subject:</label>
                        <select id="subject" name="subject" required
                                class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?php echo htmlspecialchars($exam['subject']); ?>">
                                    <?php echo htmlspecialchars($exam['subject']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="exam_title" class="block text-sm font-medium text-green-700 mb-2">Exam Title:</label>
                        <select id="exam_title" name="exam_title" required
                                class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Exam Title</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?php echo htmlspecialchars($exam['exam_title']); ?>">
                                    <?php echo htmlspecialchars($exam['exam_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Filter Button -->
                <input type="submit" name="filter" value="Filter"
                       class="w-full bg-green-600 text-white px-6 py-2 rounded-lg mt-6 hover:bg-green-700 transition-colors cursor-pointer">
            </form>

            <!-- Display Leaderboard -->
            <?php if (!empty($leaderboard)): ?>
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-green-800 mb-4">Leaderboard for <?php echo htmlspecialchars($_POST['subject']); ?> - <?php echo htmlspecialchars($_POST['exam_title']); ?></h2>
                    <canvas id="leaderboardChart"></canvas>
                </div>

                <!-- Chart.js Script -->
                <script>
                    const leaderboardData = <?php echo json_encode($leaderboard); ?>;

                    const labels = leaderboardData.map(entry => entry.student_name);
                    const scores = leaderboardData.map(entry => entry.score);
                    const completionTimes = leaderboardData.map(entry => Math.floor(entry.completion_time / 60)); // Convert to minutes

                    const ctx = document.getElementById('leaderboardChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Score',
                                    data: scores,
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Completion Time (minutes)',
                                    data: completionTimes,
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y', // Horizontal bar chart
                            scales: {
                                x: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>