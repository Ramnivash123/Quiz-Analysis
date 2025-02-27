<?php
session_start();

include 'db.php';

// Initialize variables
$email = '';
$password = '';
$error_message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and execute SQL statement to fetch user data from the "signup" table
    $stmt = $conn->prepare("SELECT * FROM tea_signup WHERE em = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if (password_verify($password, $result['pass'])) {
            // Password is correct, redirect to teacher.html
            $_SESSION['teacher_name'] = $result['na'];
            header("Location: teacherr.php");
            exit();
        } else {
            $error_message = "Invalid email or password";
        }
    } else {
        $error_message = "Invalid email or password";
    }
}

// Close connection
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 flex justify-center items-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-green-800 text-center mb-6">Teacher Sign In</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Email Field -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-green-700 mb-2">Email</label>
                <input type="text" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"
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
            if ($error_message) {
                echo '<div class="text-red-600 text-center mb-4">' . htmlspecialchars($error_message) . '</div>';
            }
            ?>
        </form>
    </div>
</body>
</html>