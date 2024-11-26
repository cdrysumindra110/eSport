<?php
include('../config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['isLogin']) || $_SESSION['isLogin'] !== true) {
    header('Location: ../admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    $article_id = $_GET['id'];

    // SQL query to delete the article
    $sql = "DELETE FROM news_articles WHERE id = $article_id";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to the previous page after successful deletion
        header('Location: contents.php?success_message=Article deleted successfully');
        exit;
    } else {
        // Handle errors
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "No article ID specified.";
}
?>
