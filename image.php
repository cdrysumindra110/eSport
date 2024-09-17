<?php
require_once 'config.php';

// Ensure you have a valid tournament ID from the request
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : 0;

if ($tournament_id) {
    $stmt = $conn->prepare("SELECT bannerimg FROM tournaments WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $stmt->bind_result($bannerimg);
        if ($stmt->fetch()) {
            // Output the image
            header("Content-Type: image/jpeg"); // Adjust as necessary for your image type
            echo $bannerimg;
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Image not found.";
        }
        $stmt->close();
    } else {
        header("HTTP/1.0 500 Internal Server Error");
        echo "Database error: " . $conn->error;
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "Invalid tournament ID.";
}

$conn->close();
?>
