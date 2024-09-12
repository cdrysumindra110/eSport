<?php 
// Database configuration
include_once('config.php');

// Initialize messages
$error_message = '';
$success_message = '';

// Check if a success message is set in the URL
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Sanitize inputs
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    // Query to check if user exists
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '".md5($password)."'"; // Use appropriate hashing method for passwords
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // User exists, set success message and redirect
        $success_message = "Successfully logged in!";
        header("Location: dashboard.php?success_message=" . urlencode($success_message));
        exit();
    } else {
        // User does not exist, set error message
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
          <a class="social-icon" id="google-signin" title="Sign with Google" class="social"><i class="fab fa-google"></i></a>
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
    <?php if (!empty($error_message) || !empty($success_message)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($success_message)): ?>
          showPopupMessage("<?php echo $success_message; ?>", 'success');
        <?php elseif (!empty($error_message)): ?>
          showPopupMessage("<?php echo $error_message; ?>", 'error');
        <?php endif; ?>
      });
    <?php endif; ?>

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
