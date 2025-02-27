<?php
session_start();
include 'db.php'; // Ensure this file contains the PDO database connection logic

$teacher_name = $_SESSION['teacher_name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_exam'])) {
    // Retrieve form data
    $exam_title = $_POST['exam_title'];
    $subject = $_POST['subject'];
    $timer = $_POST['timer'];

    // Prepare questions data
    $questions = [];
    for ($i = 0; $i < count($_POST['question']); $i++) {
        $questions[] = [
            'question' => $_POST['question'][$i],
            'options' => [
                $_POST['opt1'][$i],
                $_POST['opt2'][$i],
                $_POST['opt3'][$i],
                $_POST['opt4'][$i]
            ],
            'correct_answer' => $_POST['answer'][$i]
        ];
    }

    // Convert questions array to JSON
    $questions_json = json_encode($questions);

    // Insert data into the `exam` table
    $sql = "INSERT INTO exam (exam_title, subject, timer, teacher_name, questions) 
            VALUES (:exam_title, :subject, :timer, :teacher_name, :questions)";
    $stmt = $conn->prepare($sql);

    // Bind parameters using PDO
    $stmt->bindParam(':exam_title', $exam_title);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':timer', $timer, PDO::PARAM_INT);
    $stmt->bindParam(':teacher_name', $teacher_name);
    $stmt->bindParam(':questions', $questions_json);

    if ($stmt->execute()) {
        $success = "Exam created successfully!";
    } else {
        $error = "Error creating exam: " . $stmt->errorInfo()[2];
    }

    $stmt = null; // Close the statement
    $conn = null; // Close the connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam Manually</title>
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
            <h1 class="text-3xl font-bold text-green-800 text-center mb-6">Create Exam Manually</h1>

            <?php if (isset($error)): ?>
                <div class="text-red-600 text-center mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="text-green-600 text-center mb-6"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <!-- Exam Details -->
                <div class="space-y-6">
                    <div>
                        <label for="exam_title" class="block text-sm font-medium text-green-700 mb-2">Exam Title:</label>
                        <input type="text" id="exam_title" name="exam_title" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-green-700 mb-2">Subject:</label>
                        <input type="text" id="subject" name="subject" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="timer" class="block text-sm font-medium text-green-700 mb-2">Timer (minutes):</label>
                        <input type="number" id="timer" name="timer" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <!-- Questions Container -->
                <div id="questions-container" class="space-y-6 mt-8">
                    <div class="question-block bg-green-50 p-6 rounded-lg border-l-4 border-green-600">
                        <label class="block text-sm font-medium text-green-700 mb-2">Question 1:</label>
                        <input type="text" name="question[]" placeholder="Enter your question here" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-4">
                        <label class="block text-sm font-medium text-green-700 mb-2">Options:</label>
                        <input type="text" name="opt1[]" placeholder="Option A" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-2">
                        <input type="text" name="opt2[]" placeholder="Option B" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-2">
                        <input type="text" name="opt3[]" placeholder="Option C" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-2">
                        <input type="text" name="opt4[]" placeholder="Option D" required
                               class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-4">
                        <label class="block text-sm font-medium text-green-700 mb-2">Correct Answer:</label>
                        <select name="answer[]" required
                                class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Correct Answer</option>
                            <option value="1">Option A</option>
                            <option value="2">Option B</option>
                            <option value="3">Option C</option>
                            <option value="4">Option D</option>
                        </select>
                    </div>
                </div>

                <!-- Add Question Button -->
                <button type="button" id="add-question"
                        class="w-full bg-green-600 text-white px-6 py-2 rounded-lg mt-6 hover:bg-green-700 transition-colors">
                    Add Another Question
                </button>

                <!-- Save Exam Button -->
                <input type="submit" name="save_exam" value="Save Exam"
                       class="w-full bg-green-600 text-white px-6 py-2 rounded-lg mt-6 hover:bg-green-700 transition-colors cursor-pointer">
            </form>
        </div>
    </div>

    <script>
        document.getElementById('add-question').addEventListener('click', function() {
            const container = document.getElementById('questions-container');
            const questionCount = container.children.length + 1;

            const questionBlock = document.createElement('div');
            questionBlock.className = 'question-block bg-green-50 p-6 rounded-lg border-l-4 border-green-600';
            questionBlock.innerHTML = `
                <label class="block text-sm font-medium text-green-700 mb-2">Question ${questionCount}:</label>
                <input type="text" name="question[]" placeholder="Enter your question here" required
                       class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-4">
                <label class="block text-sm font-medium text-green-700 mb-2">Options:</label>
                <input type="text" name="opt1[]" placeholder="Option A" required
                       class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-2">
                <input type="text" name="opt2[]" placeholder="Option B" required
                       class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-2">
                <input type="text" name="opt3[]" placeholder="Option C" required
                       class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-2">
                <input type="text" name="opt4[]" placeholder="Option D" required
                       class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 mb-4">
                <label class="block text-sm font-medium text-green-700 mb-2">Correct Answer:</label>
                <select name="answer[]" required
                        class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Select Correct Answer</option>
                    <option value="1">Option A</option>
                    <option value="2">Option B</option>
                    <option value="3">Option C</option>
                    <option value="4">Option D</option>
                </select>
            `;
            container.appendChild(questionBlock);
        });
    </script>
</body>
</html>