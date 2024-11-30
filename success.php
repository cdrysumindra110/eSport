<?php
// Include the config file for database connection
require_once 'config.php';
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

// Check if the user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

$tournament_id = isset($_GET['tournament_id']) ? $_GET['tournament_id'] : null;
if (!$tournament_id) {
    die("Error: Missing tournament ID or match type.");
    exit();
}

// SQL Query to fetch tournament and game data
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, u.uname AS creator_name,
            g.game_id, g.password, g.expire_time
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN game g ON t.id = g.tournament_id
        WHERE t.id = ?";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tournament = $result->fetch_assoc();

        // Assign variables from the $tournament array
        $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
        $tname = $tournament['tname'] ?? 'Unknown Tournament';
        $sdate = $tournament['sdate'] ?? '';
        $stime = $tournament['stime'] ?? '';
        $about = $tournament['about'] ?? '';
        $bannerimg = $tournament['bannerimg'] ?? '';
        $creator_name = $tournament['creator_name'] ?? 'Unknown Creator';

        // Game-related data
        $game_id = $tournament['game_id'] ?? 'N/A';
        $password = $tournament['password'] ?? 'N/A';
        $expire_time = $tournament['expire_time'] ?? 'N/A';

        // Format the date to show month and day in words (keep year as a number)
        if ($sdate) {
            $date = new DateTime($sdate);
            $sdate = $date->format('F j, Y');  // Month name, day, year
        }

        // Check if the expiration date has passed and handle 'N/A' properly
        $current_time = new DateTime();
        
        // Check if expire_time is 'N/A' before using it

        if ($expire_time !== 'N/A') {
          $expire_time_obj = new DateTime($expire_time);
          // Check if the tournament has expired
          $is_expired = $current_time > $expire_time_obj;
        } else {
          $expire_time_obj = null;
          $is_expired = false;
        }

        $game_id_value = $is_expired ? "Expired" : $game_id;
        $password_value = $is_expired ? "Expired" : $password;
        
    } else {
        $error_message = "No tournament found with that ID.";
        echo $error_message;
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament detail statement: " . $conn->error;
    echo $error_message;
}

$conn->close();
?>


<!-- HTML form goes here, including success and error messages -->

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Esports Website</title>
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/responsee.css">
    <link rel="stylesheet" href="owl-carousel/owl.carousel.css">
    <link rel="stylesheet" href="owl-carousel/owl.theme.css">
    <!-- CUSTOM STYLE -->      
    <link rel="stylesheet" href="css/template-style.css">
    <link rel="stylesheet" href="css/tour_org.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

    <!-- popup -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        /* Overall Container Styling */
        .tournament-reg_container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        /* Left Container (Countdown and Text) */
        .left-container {
            flex: 1;
            padding: 40px;
            color: white;
            font-family: 'Arial', sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Adding a decorative element in the left container */
        .left-container::before {
            content: '';
            position: absolute;
            top: -100px;
            left: 0;
            width: 100%;
            background: url('https://www.example.com/path-to-image.svg') no-repeat center center;
            background-size: cover;
            opacity: 0.1;
            pointer-events: none;
        }

        /* Heading styles */
        .left-container h1, 
        .left-container h2, 
        .left-container h3 {
            margin: 10px 0;
            font-family: 'Helvetica', sans-serif;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Main Title */
        .left-container h1 {
            font-size: 2rem;
            color: #ecf0f1;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Subtitle */
        .left-container h2 {
            font-size: 1.5rem;
            color: #f39c12;
            text-align: center;
            margin-bottom: 10px;
        }


        /* Tournament Date and Time */
        .left-container h1 + h1 {
            font-size: 1.8rem;
            text-align: center;
            color: #ecf0f1;
            margin-top: 20px;
        }

        .ctn_btn:hover {
            background-color: #2ecc71;
            border-color: #2ecc71;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        /* Countdown Timer */
        .countdown {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .time-section {
            padding: 10px 20px;
            margin: 0 10px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        .separator {
            font-size: 1.8rem;
            margin: 0 15px;
            color: #f39c12;
        }

        /* Time numbers */
        .countdown #days,
        .countdown #hours,
        .countdown #minutes,
        .countdown #seconds {
            font-size: 2rem;
            color: #ecf0f1;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.4);
        }


        /* Right Container (Form) */
        .right-container {
            flex: 1;
            padding: 20px;
        }

        .right-container h2 {
            font-size: 3.12rem;
            color: #f39c12;
            text-align: center;
            margin-bottom: 10px;
        }
        .right-container p {
            font-size: 1.8rem;
            color: #f39c12;
            text-align: center;
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            color: #fff;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #27ae60;
            outline: none;
        }

        .start-btn, .cancel-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            width: 48%;
            margin-top: 10px;
        }

        .start-btn {
            background-color: #27ae60;
            color: white;
        }

        .start-btn:hover {
            background-color: #2ecc71;
        }

        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .tournament-reg_container {
                flex-direction: column;
                align-items: center;
            }

            .left-container, .right-container {
                width: 100%;
                margin-bottom: 20px;
            }
        }


    </style>
  </head>

  <body class="size-1280 primary-color-red">
    <!-- HEADER -->
    <header role="banner" class="position-absolute">
      <!-- Top Bar -->
      <div class="top-bar full-width hide-s hide-m">
        <div class="right">
            <a href="tel:080055544444444" class="text-white text-primary-hover">Phone : +977 8888888888 </a> 
            <span class="sep text-white">|</span> <a href="mailto:infiknightesports@gmail.com" class="text-white text-primary-hover"><i ></i>Email : infiknightesports@gmail.com</a>
        </div>  
      </div>    
      <!-- Top Navigation -->
      <nav class="background-transparent background-transparent-hightlight full-width sticky">
        <div class="s-12 l-2">
          <a href="index.php" class="logo">
            <!-- Logo White Version -->
            <img class="logo-white" src="img/logo.png" alt="">
            <!-- Logo Dark Version -->
            <img class="logo-dark" src="img/logo.png" alt="">
          </a>
        </div>
        <div class="top-nav s-12 l-10">
          <ul class="right chevron">
            <li><a href="index.php">Home</a></li>
           <li><a href="tournaments.php">Tournaments</a>
              <ul>
                <li><a href="#">Upcoming Tournaments</a>
                  <ul class="game_container">
                    <a href="#"><li class="ga_me"> <img src="img/logo/pubg_logo.png" alt="Pubg Logo" class="ga_me-icon">Pubg Mobile</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/ff_logo.png" alt="FF Logo" class="ga_me-icon">Free Fire</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/cs_logo.png" alt="COD Logo" class="ga_me-icon">COD Mobile</li></a>
                    <a href="tournaments.php" class="all-games"><li class="all-games-text">All Tournaments<i class="fas fa-arrow-right"></i></li></a>
                  </ul>
              </li>
                <li><a>Ongoing Tournaments</a></li>
                </ul>
            </li>
            <li><a href="news.php">News</a></li>
            <li><a href="our-services.php">Our Services</a></li>
             
            <li><a href="organize.php">Organize</a></li>
            <li><a href="about-us.php">About</a></li>
            <li><a href="#"><i class="fas fa-user"></i><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></a>
              <ul>
                <?php if (isset($_SESSION['isSignin']) && $_SESSION['isSignin']): ?>
                  <li><a href="dashboard.php">Profile</a></li>
                  <li><a href="logout.php"><i class='fa fa-sign-out'></i>Signout</a></li>
                <?php else: ?>
                  <li><a href="signin.php">Signin</a></li>
                  <li><a href="signup.php">Signup</a></li>
                <?php endif; ?>
              </ul>
            </li>
          </li>
        </div>
      </nav>
    </header>

    <div id="preloader" style="background: #000 url(./img/loader.gif) no-repeat center center; 
    background-size: 4.5%;height: 100vh;width: 100%;position: fixed;z-index: 999;">
    </div>

   <!-- MAIN -->
    <main role="main"> 
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
            <div class="line">
              <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
                Register Tournament
              </h1>
            </div>
        </header>
    </main>



        <!-- Popup Message -->
        <div class="popup-message" id="popup-message"></div>
<!-- ++++++++++++++++++++++++++++++++++++++++++++++Form containrerer+++++++++++++++++++++++++++++++++++ -->
<div class="tournament-reg_container">
    <div class="left-container">
        <h1>WELCOME TO THE EXCLUSIVE eSPORTS TOURNAMENT</h1>
        <h2><?php echo htmlspecialchars($tname); ?></h2>
        <h1>Expiry Date and Time: <?php 
            if ($expire_time_obj) {
                echo htmlspecialchars($expire_time_obj->format('F j, Y g:i A'));
            } else {
                echo 'N/A'; // or a custom message like "Not available yet"
            }
        ?></h1>
        <h2>THE TOURNAMENT EXPIRES IN:</h2>
        <div class="countdown">
            <div class="time-section">
                <div id="days">0</div>
                <span>Days</span>
            </div>
            <div class="separator">:</div>
            <div class="time-section">
                <div id="hours">0</div>
                <span>Hours</span>
            </div>
            <div class="separator">:</div>
            <div class="time-section">
                <div id="minutes">0</div>
                <span>Minutes</span>
            </div>
            <div class="separator">:</div>
            <div class="time-section">
                <div id="seconds">0</div>
                <span>Seconds</span>
            </div>
        </div>
    </div>
    <div class="right-container">
        <h2>Game Id and Password</h2>
        <?php if ($game_id == 'N/A' || $password == 'N/A'): ?>
            <p>Game ID and password will be available soon</p>
        <?php elseif ($is_expired): ?>
            <p>The Game ID and password have expired</p>
        <?php else: ?>
            <div class="form-group">
                <label for="game_id">Game ID</label>
                <input type="text" id="game_id" name="game_id" value="<?php echo htmlspecialchars($game_id_value); ?>" readonly onclick="copyText(this)">
            </div>
            <div class="form-group">
                <label for="password">Game Password</label>
                <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($password_value); ?>" readonly onclick="copyText(this)">
            </div>
        <?php endif; ?>
    </div>
</div>

    <!-- FOOTER -->
    <footer>
      <div class="container-animated sticky" id="logo-container">
        <div class="scrollable-container">
          <button class="animated-btn left-button">&nbsp;&nbsp;&nbsp;&nbsp;We are Trusted By:&nbsp;&nbsp;&nbsp;&nbsp;</button>
              <div class="logos">
                <img src="img/logo/ESports.jpg" alt="Esports" class="image">
                <img src="img/logo/amd.jpg" alt="AMD" class="image">
                <img src="img/logo/redbull.jpg" alt="Red Bull" class="image">
                <img src="img/logo/unicef.jpg" alt="UNICEF" class="image">
                <img src="img/logo/tencent.jpg" alt="Tencent" class="image">
                <img src="img/logo/KoHire.png" alt="KoHire" class="image">
                <img src="img/logo/masterportfolio-banner-dark.png" alt="masterportfolio-banner-dark" class="image">
                <img src="img/logo/Empyre.png" alt="Empyre" class="image">
              </div>
            <button class="animated-btn right-button">&nbsp;&nbsp;Become our Client&nbsp;&nbsp;</button>
        </div>
    </div>
      <section class="section background-dark">
      <!-- Main Footer -->
        <div class="line"> 
          <div class="margin2x">
            <div class="hide-s hide-m hide-l xl-2">
               <img src="img/logo_red.png" alt="">
            </div>
            <div class="s-12 m-6 l-3 xl-3">
               <h4 class="text-white text-strong">Our Mission</h4>
               <p>
                To create a thriving esports ecosystem where players can showcase their skills, 
                teams can compete at the highest level, and fans can experience the excitement 
                of world-class gaming events.
               </p>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Useful Links</h4>
               <a class="text-primary-hover" href="sample-post-without-sidebar.php">FAQ</a><br>      
               <a class="text-primary-hover" href="contact-1.php">Contact Us</a><br>
               <a class="text-primary-hover" href="blog.php">Blog</a>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Term of Use</h4>
               <a class="text-primary-hover" href="sample-post-without-sidebar.php">Terms and Conditions</a><br>
               <a class="text-primary-hover" href="sample-post-without-sidebar.php">Refund Policy</a><br>
               <a class="text-primary-hover" href="sample-post-without-sidebar.php">Disclaimer</a>
            </div>
            <div class="s-12 m-6 l-3 xl-3">
               <h4 class="text-white text-strong margin-m-top-30">Contact Us</h4>
                <a class="text-primary-hover" href="tel:+977 8888888888"><i class="icon-sli-screen-smartphone text-primary"></i> +977 8888888888</a><br>
                <a class="text-primary-hover" href="mailto:contact@InfiKnight.com"><i class="fa-solid fa-envelope text-primary"></i> contact@InfiKnight.com</a><br>
                <a class="text-primary-hover" href="https://maps.app.goo.gl/QGesNa3t51KtP1Vt7"><i class="fa-solid fa-map-marker-alt text-primary"></i> Pradarshani Marg, Kathmandu 44600</a>
            </div>
          </div>  
        </div>    
      </section>
      <div class="background-dark">
        <hr class="break margin-top-bottom-0" style="border-color: #777;">
      </div>
      <!-- Bottom Footer -->
      <section class="padding-2x background-dark full-width">
        <div class="full-width">
          <div class="s-12 l-6">
            <p class="text-size-12 margin-bottom-0">Copyright 2024, &Sigma;Indra65 - BCA 2k22</p>
            <p class="text-size-12 margin-bottom-0">Copyright 2024, MK38 - BCA 2k22</p>
            <p class="text-size-12">© 2024 InfiKnight Esports. All Rights Reserved.</p>
          </div>
          <div class="s-12 l-6">
            <a class="right text-size-12 text-primary-hover" href="#" title="Esports Website">Developed by Team <span style="font-size: 25px;">&infin;</span>
            </a>
          </div>
        </div>  
      </section>
    </footer>
    <script type="text/javascript" src="./js/responsee.js"></script>
    <script type="text/javascript" src="./owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="./js/template-scripts.js"></script>
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
    <!-- Popup page Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        myModal.show();
    }, 1000); // 1-second delay before modal appears
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
  popup.style.display = 'block'; // Show the popup
  setTimeout(() => {
    popup.style.display = 'none'; // Hide after 3 seconds
  }, 3000);
}

// Example usage for PHP error and success messages
document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($success_message)): ?>
    showPopupMessage("<?php echo $success_message; ?>", 'success');
  <?php elseif (!empty($error_message)): ?>
    showPopupMessage("<?php echo $error_message; ?>", 'error');
  <?php endif; ?>
});
</script>
<script>
// Get the expiry time from PHP and convert it into JavaScript Date object
var expireTime = new Date("<?php echo $expire_time_obj->format('Y-m-d H:i:s'); ?>").getTime();

// Update the countdown every second
var x = setInterval(function() {
    var now = new Date().getTime();
    var distance = expireTime - now;

    // Calculate days, hours, minutes, and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

    // Display the results
    document.getElementById("days").innerHTML = days;
    document.getElementById("hours").innerHTML = hours;
    document.getElementById("minutes").innerHTML = minutes;
    document.getElementById("seconds").innerHTML = seconds;

    // If the countdown is finished, disable the game fields and change their values
    if (distance < 0) {
        clearInterval(x);
        document.getElementById("days").innerHTML = 0;
        document.getElementById("hours").innerHTML = 0;
        document.getElementById("minutes").innerHTML = 0;
        document.getElementById("seconds").innerHTML = 0;

        // Set game_id and password to "Expired" in case of expiration
        document.getElementById("game_id").value = "Expired";
        document.getElementById("password").value = "Expired";
        document.getElementById("game_id").disabled = true;
        document.getElementById("password").disabled = true;
    }
}, 1000);

        // Show image preview
        function showPreview(event) {
            var input = event.target;
            var preview = input.nextElementSibling.querySelector('img');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
</script>

<script>

function copyText(element) {
        element.select();
        document.execCommand('copy');
        alert('Text copied to clipboard: ' + element.value);
    }
</script>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

</body>
</html>