<?php
session_start();

// Include database connection
include 'db.php';

// Initialize error variable
$error = '';

// Handle login attempt
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate email input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        try {
            // Prepare PDO statement
            $stmt = $conn->prepare("SELECT id, na, pass FROM stu_signup WHERE em = :email");
            // Bind parameters
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch the result
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Verify password
                if (password_verify($password, $row['pass'])) {
                    // Successful login
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Store user details in session
                    $_SESSION['student_id'] = $row['id'];
                    $_SESSION['student_name'] = $row['na'];

                    // Redirect to student dashboard
                    header("Location: studentt.php");
                    exit();
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 flex justify-center items-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-green-800 text-center mb-6">Student Sign In</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Email Field -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-green-700 mb-2">Email</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-green-700 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Submit Button -->
            <div class="mb-6">
                <input type="submit" value="Sign In"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-300 cursor-pointer">
            </div>

            <!-- Error Message -->
            <?php
            if (!empty($error)) {
                echo '<div class="text-red-600 text-center mb-4">' . htmlspecialchars($error) . '</div>';
            }
            ?>
        </form>
    </div>
</body>
</html>