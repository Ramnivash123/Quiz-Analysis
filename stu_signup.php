<?php
include 'db.php'; // Ensure this file establishes a PDO connection

// Initialize $success_message variable
$success_message = "";

// Process signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Sanitize and validate email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Prepare SQL statement using PDO
        $stmt = $conn->prepare("INSERT INTO stu_signup (na, em, pass) VALUES (:name, :email, :password)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            $success_message = "New record created successfully";
            // Redirect to stu_sign.html
            header("Location: stu_signin.php");
            exit();
        } else {
            echo "Error: Could not execute query.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 flex justify-center items-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-green-800 text-center mb-6">Student Sign Up</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Name Field -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-green-700 mb-2">Name</label>
                <input type="text" id="name" name="name" required
                    class="w-full px-4 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Email Field -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-green-700 mb-2">Email</label>
                <input type="text" id="email" name="email" required
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
                <input type="submit" value="Sign Up"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-300 cursor-pointer">
            </div>

            <!-- Success or Error Message -->
            <?php
            if (!empty($success_message)) {
                echo '<div class="text-green-600 text-center mb-4">' . $success_message . '</div>';
            }
            if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($success_message)) {
                echo '<div class="text-red-600 text-center mb-4">Error: ' . $conn->error . '</div>';
            }
            ?>
        </form>
    </div>
</body>
</html>