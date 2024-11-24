<?php
session_start(); // Start the session at the beginning
include_once("config.php"); // Include your database connection file

// Initialize messages
$error_message = '';
$success_message = '';   

if (isset($_POST['email']) && isset($_POST['password'])) {
    // Sanitize user inputs
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = trim($_POST["password"]);

    // Prepare the SQL statement using prepared statements
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email); // 's' means string

    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user with that email exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password using password_verify (assuming passwords are hashed)
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session
            $_SESSION['isLogin'] = true;
            $_SESSION['user_id'] = $user['id']; // Optionally store user ID
            $_SESSION['user_email'] = $user['email']; // Optionally store email
            $_SESSION['message'] = 'Successfully logged in!'; // Store success message

            // Redirect to the admin page
            header("Location: admin.php");
            exit();  // Stop further execution
        } else {
            // Invalid password
            $_SESSION['message'] = "Invalid email or password!"; // Store error message
        }
    } else {
        // No user found with the given email
        $_SESSION['message'] = "Invalid email or password!"; // Store error message
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>InfiKnight Admin Login Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Gaming Login Form Widget Tab Form,Login Forms,Sign up Forms,Registration Forms,News letter Forms,Elements"/>
    <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
    <link href="./css/admin-page.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<div class="popup-message" id="popup-message"></div>
    <div class="padding-all">
        <div class="header">
            <h1><a href="index.php"><img src="./img/dash-logo.png" alt=" "></a> Admin Login Form</h1>
        </div>

        <div class="design-w3l">
            <div class="mail-form-agile">
                <form action="admin_login.php" method="post">
                    <input type="text" name="email" id="email" placeholder="Enter email..." required=""/>
                    <input type="password"  name="password" id="password" class="padding" placeholder="Enter Password" required=""/>
                    <input type="submit" value="Login">
                </form>
            </div>
          <div class="clear"> </div>
        </div>
        
        <div class="footer">
        <p>© 2022 All Rights Reserved | Design by Team <a href="https://sumindra14.com.np/" > &infin; InfiKnight </a></p>
        </div>
    </div>

    <script>
        // Display popup message when page loads
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['message'])): ?>
                showPopupMessage("<?php echo $_SESSION['message']; ?>", 'success');
                <?php unset($_SESSION['message']); ?> // Clear message after showing
            <?php endif; ?>
        });

        // Function to show the popup message
        function showPopupMessage(message, type) {
            var popup = document.getElementById('popup-message');
            popup.innerHTML = message;
            popup.classList.add(type);  // Add success or error class
            popup.style.display = 'block';  // Show the popup
            setTimeout(function() {
                popup.style.display = 'none';  // Hide the popup after 5 seconds
            }, 5000);
        }
    </script>

</body>
</html>
