<?php 
// Database configuration
include_once('config.php');

// Initialize messages
$error_message = '';
$success_message = '';  // For redirect success messages

// Check if a success message is set in the URL
if (isset($_GET['success_signup'])) {
    $success_message = htmlspecialchars($_GET['success_signup']);  // Sanitize output
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($success_message)."', 'success'); }</script>";
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email and password from the form
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Sanitize inputs
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // Query to check if user exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // User exists and password is correct, set success message and redirect
            $success_message = "Successfully logged in!";
            header("Location: update_profile.php?success_signin=" . urlencode($success_message));
            exit();
        } else {
            // Invalid password
            $error_message = "Invalid email or password. Please try again.";
        }
    } else {
        // User does not exist
        $error_message = "Invalid email or password. Please try again.";
    }
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
  <link rel="stylesheet" href="css/signin.css">
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
            <a href="../index.html"><img src="img/logo.png" alt="Logo"></a>
          </div>
          <h1>Welcome !</h1>
          <p>Don't have an account?</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>
  <script>
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
