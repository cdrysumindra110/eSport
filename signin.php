<?php
// Start the session
session_start();

// Include the config file
require_once 'config.php';

// Initialize messages
$error_message = '';
$success_message = '';

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    $uname = $_SESSION['username'];
}

// Check if a success message is set in the URL for signup
if (isset($_GET['success_signup'])) {
    $success_message = htmlspecialchars($_GET['success_signup']);
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($success_message)."', 'success'); }</script>";
}

// Check if the login form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Sanitize inputs
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // Query to check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user was found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Valid login, set session variables
            $_SESSION['isSignin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['uname'];

            // Redirect to the dashboard with a success message
            header("Location: dashboard.php?success_signin=" . urlencode("Successfully logged in!"));
            exit();
        } else {
            // Password incorrect
            $error_message = "Invalid email or password. Please try again.";
        }
    } else {
        // User does not exist
        $error_message = "User Not Registered, Please Signup.";
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign In Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,800">
  <link rel="stylesheet" href="./css/signin.css">
  <style>
    .popup-message {
      display: none;
      padding: 15px;
      margin: 20px;
      border-radius: 5px;
      color: white;
      position: fixed;
      top: 15px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
    }
    .popup-message.success {
      background-color: #4CAF50; /* Green */
    }
    .popup-message.error {
      background-color: #f44336; /* Red */
    }
  </style>
</head>
<body>
  <div class="popup-message" id="popup-message"></div>
  
  <div class="container" id="container">
    <div class="form-container sign-in-container">
      <form action="signin.php" method="post">
        <h1>Sign in</h1>
        <div class="social-container">
          <a class="social-icon" id="google-signin" title="Sign with Google"><i class="fab fa-google"></i></a>
          <a class="social-icon" id="facebook-signin" title="Sign with Facebook"><i class="fab fa-facebook-f"></i></a>
          <a class="social-icon" id="twitch-signin" title="Sign with Twitch"><i class="fab fa-twitch"></i></a>
          <a class="social-icon" id="discord-signin" title="Sign with Discord"><i class="fab fa-discord"></i></a>
        </div>
        <span>| or |</span>
        <input type="email" id="email" name="email" placeholder="Enter your Email id" required />
        <input type="password" id="password" name="password" placeholder="Enter your Password" required />
        <a href="#" id="forgot-password">Forgot your password?</a>
        <button type="submit" id="signin-button" name="signin-button">Sign In</button>
      </form>
    </div>
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-right">
          <div class="logo-container">
            <a href="./index.php"><img src="./img/logo.png" alt="Logo"></a>
          </div>
          <h1>Welcome !</h1>
          <p>Don't have an account?</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

// Function to show the popup message
function showPopupMessage(message, type) {
  const popup = document.getElementById('popup-message');
  popup.textContent = message;
  popup.className = 'popup-message'; // Reset to default
  if (type === 'success') {
    popup.classList.add('success');
  } else if (type === 'error') {
    popup.classList.add('error');
  }
  popup.style.display = 'block';
  setTimeout(() => {
    popup.style.display = 'none';
  }, 3000); // Hide after 3 seconds
}

// Example usage for PHP error and success messages
document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($success_message)): ?>
    showPopupMessage(<?php echo json_encode($success_message); ?>, 'success');
  <?php elseif (!empty($error_message)): ?>
    showPopupMessage(<?php echo json_encode($error_message); ?>, 'error');
  <?php endif; ?>
});

function toggleContainerAndRedirect() {
  const container = document.getElementById('container');
  container.classList.add('hidden'); 

  setTimeout(function() {
    window.location.href = 'signup.php';
  }, 300); 
}

document.getElementById('signUp').addEventListener('click', toggleContainerAndRedirect);
  </script>
</body>
</html>
