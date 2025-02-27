<?php
require 'vendor/autoload.php';

use Madcoda\Youtube\Youtube;

$youtube = new Youtube(['key' => 'AIzaSyCAs0C77PeerE8KmCWwZ9F1pbzWOxn1brQ']); // Add your API key

$videoUrl = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $command = strtolower(trim($_POST['command']));

    if (strpos($command, 'play') !== false) {
        $song = str_replace('play', '', $command);
        $song = trim($song);

        // Search for the video
        $searchResults = $youtube->searchVideos($song);
        
        if (!empty($searchResults)) {
            $videoId = $searchResults[0]->id->videoId; // Get first video result
            $videoUrl = "https://www.youtube.com/embed/$videoId?autoplay=1";
        } else {
            echo "<p class='text-center text-red-600 mt-4'>No video found!</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edu Learn - YouTube Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen">
    <nav class="sticky top-0 bg-green-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a class="text-2xl font-bold" href="#">Edu Learn</a>
                <a href="teacherr.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i class="fas fa-user mr-2"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- YouTube Player Section -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-green-700 mb-6">🎵 YouTube Song Player</h2>

            <form method="POST">
                <input type="text" name="command" placeholder="Type 'play song name'" required 
                    class="w-full px-5 py-3 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg mb-4">
                
                <button type="submit" 
                    class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition duration-200">
                    ▶ Play on YouTube
                </button>
            </form>
        </div>
    </div>

    <!-- Video Embed Section -->
    <?php if ($videoUrl): ?>
        <div class="mt-8">
            <iframe class="rounded-lg shadow-md border border-green-400" width="560" height="315" 
                src="<?php echo $videoUrl; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen>
            </iframe>
        </div>
    <?php endif; ?>

</body>
</html>
