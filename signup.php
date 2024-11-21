<?php
session_start();

include_once('config.php');  // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initialize the error and success messages
$error_message = '';
$success_signup = '';

// Generate OTP and activation code for account verification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gather form data
    $uname = $_POST['uname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check for empty fields
    if (empty($uname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR uname = ?");
        $stmt->bind_param("ss", $email, $uname);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['email'] === $email) {
                $error_message = "Email already exists.";
            } elseif ($row['uname'] === $uname) {
                $error_message = "Username already exists.";
            }
        } else {
            // Generate OTP for email verification
            $otp_str = str_shuffle("0123456789");
            $otp = substr($otp_str, 0, 6);
            $_SESSION['otp'] = $otp;  // Store OTP in session
            $_SESSION['email'] = $email;  // Store email in session

            // Create the activation code
            $act_str = rand(100000, 10000000);
            $activation_code = str_shuffle("abcdefghijklmnopqrstuvwxyz" . $act_str);

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (uname, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $uname, $email, $hashed_password);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Send OTP to the user's email using PHPMailer
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.example.com';  // Your SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'user@example.com';  // Your SMTP username
                    $mail->Password = 'secret';  // Your SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;

                    $mail->setFrom('no-reply@yourdomain.com', 'Your Website');
                    $mail->addAddress($email);  // Send OTP to the user's email
                    $mail->Subject = 'Your OTP for Account Verification';
                    $mail->Body = "Hello, your OTP for account verification is: $otp";
                    $mail->send();

                    // Set success message and redirect to the verification page
                    $success_signup = "Account created successfully! OTP sent to your email for verification.";
                    header("Location: verify_otp.php?success_signup=" . urlencode($success_signup));
                    exit();
                } catch (Exception $e) {
                    $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error_message = "Error: " . $conn->error;
            }

            // Close the statement
            $stmt->close();
        }
    }

    // Close the connection
    $conn->close();
}

// If OTP is submitted for verification
if (isset($_POST['otp_verification'])) {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        // OTP is correct, proceed to activate the account
        $activation_code = $_SESSION['activation_code'];
        // Activate the account (e.g., update the status in the database)
        // Your activation logic goes here

        echo "Account successfully activated!";
    } else {
        // OTP is incorrect
        echo "Invalid OTP. Please try again.";
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Signup Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,800">
  <link rel="stylesheet" href="./css/signup.css">
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
      background-color: #4CAF50;
    }
    .popup-message.error {
      background-color: #f44336;
    }
  </style>
</head>
<body>
  <div class="popup-message" id="popup-message"></div>

  <div class="container" id="container">
    <div class="form-container sign-up-container">
        <form id="signup-form" action="signup.php" method="post">
            <h1>Sign Up</h1>
            <div class="social-container">
                <a class="social-icon" id="google-signup" title="Sign Up with Google"><i class="fab fa-google"></i></a>
                <a class="social-icon" id="facebook-signup" title="Sign Up with Facebook"><i class="fab fa-facebook-f"></i></a>
                <a class="social-icon" id="twitch-signup" title="Sign Up with Twitch"><i class="fab fa-twitch"></i></a>
                <a class="social-icon" id="discord-signup" title="Sign Up with Discord"><i class="fab fa-discord"></i></a>
            </div>
            <span>| or |</span>
            <input type="text" id="uname" name="uname" placeholder="Enter a valid Username" required />
            <input type="email" id="email" name="email" placeholder="Enter your Email" required />
            <input type="password" id="password" name="password" placeholder="Enter your Password" required />
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
            <button type="submit" name="signup">Sign Up</button>
        </form>
    </div>

    <!-- OTP Form Section (only shown after sign-up is successful) -->
    <?php if (isset($_SESSION['show_otp_form']) && $_SESSION['show_otp_form'] === true): ?>
    <div class="form-container otp-container">
        <form id="otp-form" action="signup.php" method="post">
            <h1>Enter OTP</h1>
            <input type="text" name="otp" placeholder="Enter OTP" required />
            <button type="submit" name="otp_verification">Verify OTP</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <div class="logo-container">
                    <a href="./index.php"><img src="./img/logo.png" alt="Logo"></a>
                </div>
                <h1>Welcome!</h1>
                <p>Already have an account?</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
        </div>
    </div>
</div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

    function showPopupMessage(message, type) {
      const popup = document.getElementById('popup-message');
      popup.textContent = message;
      popup.className = 'popup-message'; 
      if (type === 'success') {
        popup.classList.add('success');
      } else if (type === 'error') {
        popup.classList.add('error');
      }
      popup.style.display = 'block';
      setTimeout(() => {
        popup.style.display = 'none';
      }, 3000); 
    }

    
    <?php if (!empty($error_message) || !empty($success_signup)): ?>  
      document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($success_signup)): ?>  
          showPopupMessage("<?php echo $success_signup; ?>", 'success');  
        <?php elseif (!empty($error_message)): ?>
          showPopupMessage("<?php echo $error_message; ?>", 'error');
        <?php endif; ?>
      });
    <?php endif; ?>


    function toggleContainerAndRedirect() {
      const container = document.getElementById('container');
      container.classList.add('hidden'); 

      setTimeout(function() {
        window.location.href = 'signin.php';
      }, 300); 
    }

    document.getElementById('signIn').addEventListener('click', toggleContainerAndRedirect);

    
    document.getElementById('signup-form').addEventListener('submit', function(event) {
      const fullName = document.getElementById('uname').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      if (fullName === '' || email === '' || password === '' || confirmPassword === '') {
        event.preventDefault();
        showPopupMessage('All fields are required.', 'error');
      } else if (!/\S+@\S+\.\S+/.test(email)) {
        event.preventDefault();
        showPopupMessage('Invalid email format.', 'error');
      } else if (password !== confirmPassword) {
        event.preventDefault();
        showPopupMessage('Passwords do not match.', 'error');
      }
    });
  </script>
</body>
</html>
