<?php

session_start();

include_once('config.php');

$error_message = '';
$success_signup = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $uname = $_POST['uname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($uname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        if ($conn->connect_error) {
            $error_message = "Connection failed: " . $conn->connect_error;
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
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user
                $stmt = $conn->prepare("INSERT INTO users (uname, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $uname, $email, $hashed_password);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $_SESSION['username'] = $uname;

                    $success_signup = "Account created successfully!";
                    header("Location: signin.php?success_signup=" . urlencode($success_signup));
                    exit();
                } else {
                    $error_message = "Error: " . $conn->error;
                }
            }

            // Close the statement and connection
            $stmt->close();
            $conn->close();
        }
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
    .password-container {
      position: relative;
      width: 100%;
    }

    #toggle-password {
      position: absolute;
      right: -2rem;
      top: 25%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
    }
    #toggle-confirm-password {
      position: absolute;
      right: -2rem;
      top: 75%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
    }
    #toggle-icon {
      font-size: 1rem;  /* Adjust the size as needed */
    }
  </style>
</head>
<body>
<div id="preloader" style="background: #000 url(./img/loader.gif) no-repeat center center; 
    background-size: 4.5%;height: 100vh;width: 100%;position: fixed;z-index: 999;">
    </div>
  <div class="popup-message" id="popup-message"></div>

  <div class="container" id="container">
  <div class="form-container sign-up-container">
    <form id="signup-form" action="signup.php" method="post" onsubmit="return validateForm()">
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
      <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Enter your Password" required />
          <button type="button" id="toggle-password">
            <span id="toggle-icon">👁️</span>
          </button>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
          <button type="button" id="toggle-confirm-password">
            <span id="toggle-icon">👁️</span>
          </button>
      </div>
      
      <button type="submit">Sign Up</button>
    </form>
  </div>
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

  // Function to toggle password visibility
  function togglePasswordVisibility(passwordFieldId, toggleButtonId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleButton = document.getElementById(toggleButtonId);
    
    if (passwordField.type === "password") {
      passwordField.type = "text";
      toggleButton.textContent = "🙈"; // Change to 'Hide' icon when visible
    } else {
      passwordField.type = "password";
      toggleButton.textContent = "👁️"; // Change to 'Show' icon when hidden
    }
  }

  // Add event listeners to toggle buttons
  document.getElementById('toggle-password').addEventListener('click', function() {
    togglePasswordVisibility('password', 'toggle-password');
  });
  document.getElementById('toggle-confirm-password').addEventListener('click', function() {
    togglePasswordVisibility('confirm_password', 'toggle-confirm-password');
  });

  // Your other existing JavaScript code here...
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
    document.addEventListener('DOMContentLoaded', function () {
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
    setTimeout(function () {
      window.location.href = 'signin.php';
    }, 300);
  }

  document.getElementById('signIn').addEventListener('click', toggleContainerAndRedirect);

  document.getElementById('signup-form').addEventListener('submit', function (event) {
    const uname = document.getElementById('uname').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (uname === '' || email === '' || password === '' || confirmPassword === '') {
      event.preventDefault();
      showPopupMessage('All fields are required.', 'error');
      return;
    }

    if (!/\S+@\S+\.\S+/.test(email)) {
      event.preventDefault();
      showPopupMessage('Invalid email format.', 'error');
      return;
    }

    if (password !== confirmPassword) {
      event.preventDefault();
      showPopupMessage('Passwords do not match.', 'error');
      return;
    }

    const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&./])[A-Za-z\d@$!%*?&./]{8,}$/;

if (!passwordRegex.test(password)) {
  event.preventDefault();
  showPopupMessage('Password must be at least 8 characters long, and include one uppercase letter, one number, and one special character.', 'error');
  return;
}


  });
</script>
<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
</body>
</html>
