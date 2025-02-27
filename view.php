<?php
session_start();
include 'db.php'; // Ensure this file contains the PDO database connection logic

// Fetch all unique subjects and exam titles for the dropdown
$sql = "SELECT DISTINCT subject, exam_title FROM exam";
$stmt = $conn->query($sql);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_exam = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_exam'])) {
    $subject = $_POST['subject'];
    $exam_title = $_POST['exam_title'];

    // Fetch the selected exam details
    $sql = "SELECT * FROM exam WHERE subject = :subject AND exam_title = :exam_title";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':exam_title', $exam_title);
    $stmt->execute();
    $selected_exam = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Exam Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <h1 class="text-3xl font-bold text-green-800 text-center mb-6">View Exam Details</h1>

            <!-- Form to select subject and exam title -->
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

                <!-- View Exam Button -->
                <input type="submit" name="view_exam" value="View Exam"
                       class="w-full bg-green-600 text-white px-6 py-2 rounded-lg mt-6 hover:bg-green-700 transition-colors cursor-pointer">
            </form>

            <!-- Display selected exam details -->
            <?php if ($selected_exam): ?>
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-green-800 mb-4">Exam Details</h2>
                    <div class="space-y-4">
                        <p><strong>Exam Title:</strong> <?php echo htmlspecialchars($selected_exam['exam_title']); ?></p>
                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($selected_exam['subject']); ?></p>
                        <p><strong>Timer:</strong> <?php echo htmlspecialchars($selected_exam['timer']); ?> minutes</p>
                        <p><strong>Teacher Name:</strong> <?php echo htmlspecialchars($selected_exam['teacher_name']); ?></p>
                        <p><strong>Created At:</strong> <?php echo htmlspecialchars($selected_exam['created_at']); ?></p>
                        <div>
                            <strong>Questions:</strong>
                            <?php
                            $questions = json_decode($selected_exam['questions'], true);
                            foreach ($questions as $index => $question): ?>
                                <div class="bg-green-50 p-4 rounded-lg mt-4">
                                    <p><strong>Question <?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question']); ?></p>
                                    <p><strong>Options:</strong></p>
                                    <ul class="list-disc list-inside">
                                        <?php foreach ($question['options'] as $option): ?>
                                            <li><?php echo htmlspecialchars($option); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p><strong>Correct Answer:</strong> Option <?php echo htmlspecialchars($question['correct_answer']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>