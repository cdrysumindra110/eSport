
<?php
include('../config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['isLogin']) || $_SESSION['isLogin'] !== true) {
    header('Location: ../admin_login.php');
    exit;
}

// Handle deletion of the tournament
if (isset($_GET['id'])) {
    $tournamentid = $_GET['id'];
  
    // SQL query to delete the tournament
    $sql = "DELETE FROM tournaments WHERE id = $tournamentid";  // Use $tournamentid instead of $id
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to the tournaments page after successful deletion
        header('Location: mytournaments.php?success_message=Tournament deleted successfully');
        exit;
    } else {
        // Handle errors
        echo "Error deleting record: " . $conn->error;
    }
  } else {
    echo "No tournament ID specified.";
  }
  
  
?>
