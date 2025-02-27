<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="min-h-screen bg-gradient-to-br from-green-50 to-green-100 font-sans flex flex-col m-0">
    <!-- Navigation -->
    <nav class="sticky top-0 bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a class="text-2xl font-bold" href="#">Edu Learn</a>
                
                    <a href="index.html" 
                        class="flex items-center space-x-2 bg-white bg-opacity-20 text-white px-6 py-2 rounded-full
                      transition-all duration-300 hover:bg-white hover:text-green-800 hover:shadow-md">
                        <span>Logout</span>
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
            </div>
        </div>
    </nav>
    <!-- Header -->
    <header>
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-graduation-cap text-3xl"></i>
                <div class="text-2xl font-bold text-center">
                    <?php
                    session_start();
                    if (isset($_SESSION['student_name'])) {
                        $student_name = htmlspecialchars($_SESSION['student_name']);
                        echo "Welcome, $student_name!";
                    } else {
                        header("Location: signin.php");
                        exit();
                    }
                    ?>
                </div>
            </div>
            
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl p-8 transform hover:scale-[1.02] transition-transform duration-300">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-tasks text-green-600 mr-3"></i>
                    Assignments Dashboard
                </h1>
                <p class="text-xl text-gray-600">Access your assignments and track your scores easily.</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Assignments Card -->
                <div class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-md transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-center flex-col">
                        <div class="bg-green-600 text-white p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-book-open text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">View Assignments</h2>
                        <p class="text-gray-600">Check your pending and completed assignments</p>
                        <div class="mt-4">
                            <a href="student.php" class="mr-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">Normal</a>
                            <a href="student2.php" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">Enhanced</a>
                        </div>
                    </div>
                </div>

                <!-- Scores Card -->
                <a href="marks.php" 
                   class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-md 
                          transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-center flex-col">
                        <div class="bg-green-600 text-white p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">View Scores</h2>
                        <p class="text-gray-600">Track your performance and progress</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 px-8">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 md:mb-0">
                <i class="fas fa-school text-xl"></i>
                <p>&copy; 2025 Your School Name. All Rights Reserved.</p>
            </div>
            <div class="flex space-x-6">
                <a href="contact.html" class="text-white hover:text-green-400 transition-colors">
                    <i class="fas fa-envelope mr-2"></i>Contact Us
                </a>
                <a href="#" class="text-white hover:text-green-400 transition-colors">
                    <i class="fas fa-question-circle mr-2"></i>Help
                </a>
            </div>
        </div>
    </footer>
</body>
</html>