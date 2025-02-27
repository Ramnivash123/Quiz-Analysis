<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="min-h-screen bg-gradient-to-br from-green-50 to-green-100 font-sans flex flex-col m-0">
    
    <!-- Header -->
    <header>
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
        <?php
        session_start();
        if (!isset($_SESSION['teacher_name'])) {
            header("Location: signin.php");
            exit();
        }
        $teacher_name = htmlspecialchars($_SESSION['teacher_name']);
        ?>
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-chalkboard-teacher text-3xl"></i>
                <div class="text-2xl font-bold">
                    Welcome, <?php echo $teacher_name; ?>!
                </div>
            </div>
           
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl p-8 transform hover:scale-[1.02] transition-transform duration-300">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-chalkboard-teacher text-green-600 mr-3"></i>
                    Teacher Dashboard
                </h1>
                <p class="text-xl text-gray-600">Manage your classroom activities and monitor student progress</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Create Assignments Card -->
                <a href="teacher.php" class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-md transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-center flex-col">
                        <div class="bg-green-600 text-white p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus-circle text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Create Assignments</h2>
                        <p class="text-gray-600">Create new assignments and tests</p>
                    </div>
                </a>

                <!-- View Submissions Card -->
                <a href="view.php" class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-md transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-center flex-col">
                        <div class="bg-green-600 text-white p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-eye text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">View Submissions</h2>
                        <p class="text-gray-600">Review student submissions</p>
                    </div>
                </a>

                <!-- Leaderboard Card -->
                <a href="leader.php" class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-md transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-center flex-col">
                        <div class="bg-green-600 text-white p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-trophy text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Leaderboard</h2>
                        <p class="text-gray-600">Track student performance</p>
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