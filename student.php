<?php
session_start();
include 'db.php'; // Ensure this file contains the PDO database connection logic

$student_name = $_SESSION['student_name'] ?? '';

// Fetch all unique subjects and exam titles for the dropdown
$sql = "SELECT DISTINCT subject, exam_title FROM exam";
$stmt = $conn->query($sql);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_exam = null;
$questions = [];
$quiz_completed = false;

// Handle form submission to select exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_exam'])) {
    $subject = $_POST['subject'];
    $exam_title = $_POST['exam_title'];

    // Check if the student has already completed this quiz
    $sql = "SELECT * FROM marks WHERE student_name = :student_name AND subject = :subject AND exam_title = :exam_title";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':student_name', $student_name);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':exam_title', $exam_title);
    $stmt->execute();
    $quiz_completed = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz_completed) {
        // Fetch the selected exam details
        $sql = "SELECT * FROM exam WHERE subject = :subject AND exam_title = :exam_title";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':exam_title', $exam_title);
        $stmt->execute();
        $selected_exam = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($selected_exam) {
            $questions = json_decode($selected_exam['questions'], true);
        }
    }
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $subject = $_POST['subject'];
    $exam_title = $_POST['exam_title'];
    $completion_time = $_POST['completion_time']; // Time taken in seconds

    // Calculate the score
    $score = 0;
    foreach ($_POST['answers'] as $index => $answer) {
        if ($answer == $_POST['correct_answers'][$index]) {
            $score++;
        }
    }

    // Insert the student's score and completion time into the `marks` table
    $sql = "INSERT INTO marks (student_name, exam_title, subject, score, completion_time) 
            VALUES (:student_name, :exam_title, :subject, :score, :completion_time)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':student_name', $student_name);
    $stmt->bindParam(':exam_title', $exam_title);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':score', $score);
    $stmt->bindParam(':completion_time', $completion_time);

    if ($stmt->execute()) {
        $success = "Quiz submitted successfully! Your score: $score";
        $quiz_completed = true; // Mark the quiz as completed
    } else {
        $error = "Error submitting quiz: " . $stmt->errorInfo()[2];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attend Quiz</title>
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
            <h1 class="text-3xl font-bold text-green-800 text-center mb-6">Attend Quiz</h1>

            <?php if (isset($error)): ?>
                <div class="text-red-600 text-center mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="text-green-600 text-center mb-6"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Form to select subject and exam title -->
            <?php if (!$selected_exam && !$quiz_completed): ?>
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

                    <!-- Start Exam Button -->
                    <input type="submit" name="start_exam" value="Start Exam"
                           class="w-full bg-green-600 text-white px-6 py-2 rounded-lg mt-6 hover:bg-green-700 transition-colors cursor-pointer">
                </form>
            <?php endif; ?>

            <!-- Display quiz questions -->
             
            <?php if ($selected_exam && !isset($_POST['submit_quiz']) && !$quiz_completed): ?>
                <!-- Timer -->
                <div class="text-center text-2xl font-bold text-green-800 mt-6" id="timer">00:00</div><br>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="subject" value="<?php echo htmlspecialchars($selected_exam['subject']); ?>">
                    <input type="hidden" name="exam_title" value="<?php echo htmlspecialchars($selected_exam['exam_title']); ?>">
                    <input type="hidden" name="completion_time" id="completion_time" value="0">

                    <div class="space-y-6">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="bg-green-50 p-6 rounded-lg border-l-4 border-green-600">
                                <p class="text-lg font-medium text-green-800 mb-4">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                                <?php foreach ($question['options'] as $optIndex => $option): ?>
                                    <label class="block mb-2">
                                        <input type="radio" name="answers[<?php echo $index; ?>]" value="<?php echo $optIndex + 1; ?>" required
                                               class="mr-2">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                <?php endforeach; ?>
                                <input type="hidden" name="correct_answers[<?php echo $index; ?>]" value="<?php echo $question['correct_answer']; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    

                    <!-- Submit Quiz Button -->
                    <input type="submit" name="submit_quiz" value="Submit Quiz"
                           class="w-full bg-green-600 text-white px-6 py-2 rounded-lg mt-6 hover:bg-green-700 transition-colors cursor-pointer">
                </form>

                <!-- Timer Script -->
                <script>
                    let startTime = Date.now();
                    let timerInterval = setInterval(() => {
                        let elapsedTime = Math.floor((Date.now() - startTime) / 1000);
                        let minutes = Math.floor(elapsedTime / 60);
                        let seconds = elapsedTime % 60;
                        document.getElementById('timer').textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                        document.getElementById('completion_time').value = elapsedTime;
                    }, 1000);
                </script>
            <?php endif; ?>

            <!-- Display completed status if the quiz is already completed -->
            <?php if ($quiz_completed): ?>
                <div class="text-center text-green-700 mt-6">
                    <p>You have already completed this quiz.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>