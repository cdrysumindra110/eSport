<?php
include_once('config.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE verify_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Update the user's status to verified
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE verify_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $success_verify = "Your email has been verified successfully!";
        header("Location: signin.php?success_verify=" . urlencode($success_verify));
        exit();
    } else {
        $error_verify = "Invalid verification link.";
        header("Location: signin.php?error_verify=" . urlencode($error_verify));
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
