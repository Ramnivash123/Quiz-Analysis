<?php
session_start();
include 'db.php'; // Ensure this file contains the PDO database connection logic

$student_name = $_SESSION['student_name'] ?? '';

// Fetch the student's marks from the `marks` table
$sql = "SELECT * FROM marks WHERE student_name = :student_name ORDER BY completed_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':student_name', $student_name);
$stmt->execute();
$marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Marks</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="sticky top-0 bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a class="text-2xl font-bold" href="#">Edu Learn</a>
                <a href="studentt.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i class="fas fa-user mr-2"></i>
                    <span>Profile</span>
                </a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-green-800 text-center mb-6">Your Quiz Results</h1>

            <?php if (empty($marks)): ?>
                <p class="text-center text-green-700">No quiz results found.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($marks as $mark): ?>
                        <div class="bg-green-50 p-6 rounded-lg border-l-4 border-green-600">
                            <p><strong>Exam Title:</strong> <?php echo htmlspecialchars($mark['exam_title']); ?></p>
                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($mark['subject']); ?></p>
                            <p><strong>Score:</strong> <?php echo htmlspecialchars($mark['score']); ?></p>
                            <p><strong>Completion Time:</strong> <?php echo floor($mark['completion_time'] / 60); ?> minutes <?php echo $mark['completion_time'] % 60; ?> seconds</p>
                            <p><strong>Completed At:</strong> <?php echo htmlspecialchars($mark['completed_at']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>