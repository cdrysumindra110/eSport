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

// Get tournament ID and match type from the request
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : null;
$match_type = isset($_GET['match_type']) ? $_GET['match_type'] : null;

if (!$tournament_id || !$match_type) {
    die("Error: Missing tournament ID or match type.");
}

// Only process form submission if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch the logged-in user ID
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        die("Error: User ID not set in session.");
    }

    // Initialize prepared statements and variables
    $player_stmt = null;

    try {
        // Start a transaction
        $conn->begin_transaction();

        // Define logo file path (if uploaded)
        $logo_path = '';
        if (!empty($_FILES['logo']['name'])) {
            $target_dir = "uploads/logos/";
            $file_type = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.");
            }

            $logo_path = $target_dir . uniqid() . '.' . $file_type; // Generate a unique file name

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                throw new Exception("File upload error: Unable to save logo.");
            }
        }

        // Insert into registration table
        $stmt = $conn->prepare("INSERT INTO registration (tournament_id, user_id, match_type, team_name, mentor_name, email, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Set fields based on match type
        if ($match_type === 'solo') {
            $mentor_name = null;
            $stmt->bind_param("iisssss", $tournament_id, $user_id, $match_type, $_POST['solo_name'], $mentor_name, $_POST['solo_email'], $logo_path);
        } elseif ($match_type === 'duo') {
            $stmt->bind_param("iisssss", $tournament_id, $user_id, $match_type, $_POST['duo_name'], $_POST['duo_mentor'], $_POST['duo_email'], $logo_path);
        } elseif ($match_type === 'squad') {
            $stmt->bind_param("iisssss", $tournament_id, $user_id, $match_type, $_POST['sqd_name'], $_POST['sqd_captain'], $_POST['sqd_email'], $logo_path);
        }

        $stmt->execute();
        $registration_id = $stmt->insert_id;

        // Insert players based on match type
        $player_stmt = $conn->prepare("INSERT INTO players (registration_id, name, email, role, ign) VALUES (?, ?, ?, ?, ?)");

        if ($match_type === 'solo') {
            $role = null;
            $ign = null;
            $player_stmt->bind_param("issss", $registration_id, $_POST['solo_name'], $_POST['solo_email'], $role, $ign);
            $player_stmt->execute();
        } elseif ($match_type === 'duo') {
            // Duo players
            $duo_players = [
                ['name' => $_POST['duop1_name'], 'email' => $_POST['duop1_email'], 'role' => $_POST['duop1_role'], 'ign' => $_POST['duop1_ign']],
                ['name' => $_POST['duop2_name'], 'email' => $_POST['duop2_email'], 'role' => $_POST['duop2_role'], 'ign' => $_POST['duop2_ign']],
            ];
            foreach ($duo_players as $player) {
                $player_stmt->bind_param("issss", $registration_id, $player['name'], $player['email'], $player['role'], $player['ign']);
                $player_stmt->execute();
            }
        } elseif ($match_type === 'squad') {
            // Squad players
            $squad_players = [
                ['name' => $_POST['sqdp1_name'], 'email' => $_POST['sqdp1_email'], 'role' => $_POST['sqdp1_role'], 'ign' => $_POST['sqdp1_ign']],
                ['name' => $_POST['sqdp2_name'], 'email' => $_POST['sqdp2_email'], 'role' => $_POST['sqdp2_role'], 'ign' => $_POST['sqdp2_ign']],
                ['name' => $_POST['sqdp3_name'], 'email' => $_POST['sqdp3_email'], 'role' => $_POST['sqdp3_role'], 'ign' => $_POST['sqdp3_ign']],
                ['name' => $_POST['sqdp4_name'], 'email' => $_POST['sqdp4_email'], 'role' => $_POST['sqdp4_role'], 'ign' => $_POST['sqdp4_ign']],
                // Substitute Player
                ['name' => $_POST['sqdsb_name'], 'email' => $_POST['sqdsb_email'], 'role' => $_POST['sqdsb_role'], 'ign' => $_POST['sqdsb_ign']],
            ];
            foreach ($squad_players as $player) {
                $player_stmt->bind_param("issss", $registration_id, $player['name'], $player['email'], $player['role'], $player['ign']);
                $player_stmt->execute();
            }
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "Registration successful!";
        header("Location: tournaments.php"); // Redirect to a success page
        exit();
    } catch (Exception $e) {
        // Rollback transaction if there was an error
        $conn->rollback();
        error_log("Registration failed: " . $e->getMessage()); // Log the error for debugging
        $_SESSION['error_message'] = "Registration failed: " . $e->getMessage();
        header("Location: register.php"); // Redirect back to the registration page
        exit();
    } finally {
        // Close statements and connection
        if (isset($stmt)) {
            $stmt->close();
        }
        if ($player_stmt) {
            $player_stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Fetch tournament details (run this outside of form submission check to display on page load)
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, u.uname AS creator_name 
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN users u ON t.user_id = u.id  
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tournament = $result->fetch_assoc();

        // Assign variables from the $tournament array
        $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
        $tname = $tournament['tname'] ?? 'Unknown Game';
        $sdate = $tournament['sdate'] ?? '';
        $stime = $tournament['stime'] ?? '';
        $match_type = $tournament['match_type'] ?? '';
        $about = $tournament['about'] ?? '';
        $bannerimg = $tournament['bannerimg'] ?? '';
        $creator_name = $tournament['creator_name'] ?? 'Unknown Creator';
    } else {
        $error_message = "No tournament found with that ID.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament detail statement: " . $conn->error;
}
?> 




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
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 0;
}

/* Tournament Registration Container */
.tournament-reg_container {
    background: linear-gradient(135deg, #3a1c71, #d76d77, #ffaf7b);
    color: #fff;
    text-align: center;
    padding: 50px 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    margin-bottom: 30px;
}

/* Content Styling */
.tournament-reg_container .content {
    max-width: 800px;
    margin: 0 auto;
}

/* Heading Styles */
.tournament-reg_container h1 {
    font-size: 36px;
    margin: 15px 0;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: color 0.3s ease;
}
.tournament-reg_container h2 {
    font-size: 24px;
    margin: 10px 0;
    font-weight: semi-bold;
    letter-spacing: 1.5px;
    color: #ffeb3b;
}
.tournament-reg_container h3 {
    font-size: 20px;
    margin: 8px 0;
    font-weight: normal;
}

/* Button Styles */
.ctn_btn {
    display: inline-block;
    background-color: #ff4081;
    color: #fff;
    padding: 12px 25px;
    font-size: 18px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
    margin-top: 20px;
}
.ctn_btn:hover {
    background-color: #f50057;
    transform: scale(1.05);
}

/* Countdown Styling */
.countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
    font-size: 24px;
}
.countdown div {
    background-color: rgba(255, 255, 255, 0.2);
    padding: 20px;
    border-radius: 5px;
    min-width: 60px;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .tournament-reg_container {
        padding: 30px 15px;
    }
    .tournament-reg_container h1 {
        font-size: 28px;
    }
    .tournament-reg_container h2 {
        font-size: 20px;
    }
    .tournament-reg_container h3 {
        font-size: 16px;
    }
    .ctn_btn {
        font-size: 16px;
        padding: 10px 20px;
    }
    .countdown div {
        padding: 15px;
        font-size: 20px;
    }
}

/* Container Styles */
.container-registration {
    background-color: #fff;
    width: 90%;
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}
.container-registration:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

/* Header */
.container-registration h2 {
    text-align: center;
    font-size: 28px;
    color: #333;
}

/* Form Styles */
#registration_form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Section Styles */
#solo_reg,
#duo_reg,
#sqd_reg {
    background-color: #fafafa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}

/* Unique Class Names */
.form-group-solo,
.form-group-duo,
.form-group-squad {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}
/* Player Container Styling */
#players {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

/* Player Section Styling */
.player-section {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Responsive Design for Smaller Screens */
@media (max-width: 768px) {
    #players {
        grid-template-columns: 1fr;
    }
}

/* Input Styles */
input[type="text"],
input[type="email"],
input[type="tel"],
input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="tel"]:focus,
input[type="file"]:focus {
    border-color: #6200ea;
    box-shadow: 0 0 5px rgba(98, 0, 234, 0.4);
}

/* Button Styles */
button[type="submit"] {
    background-color: #6200ea;
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}
button[type="submit"]:hover {
    background-color: #4b00b3;
}

/* Preview Image */
.preview img {
    max-width: 100%;
    max-height: 150px;
    border-radius: 5px;
}

/* Player Info */
.player-section h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 8px;
}
.player-section label {
    font-size: 14px;
    color: #666;
}
.player-section input {
    margin-bottom: 8px;
}

/* Section-specific Styles */
#solo_reg .form-group-solo label,
#duo_reg .form-group-duo label,
#sqd_reg .form-group-squad label {
    font-weight: bold;
    color: #444;
}

/* Transitions for Effects */
.container-registration,
#registration_form div,
input,
button {
    transition: all 0.3s ease;
}

.hidden {
    display: none;
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
            <span class="sep text-white">|</span> <a href="mailto:info@InfiKnight.com" class="text-white text-primary-hover"><i ></i>Email : info@InfiKnight.com</a>
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
                  <li><a href="logout.php">Signout</a></li>
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
    <div class="content">
        <h1>GET READY FOR THE ULTIMATE eSPORTS TOURNAMENT</h1>
        <h2><?php echo htmlspecialchars($tname); ?></h2>
        <h3>THE WORLD OF COMPETITIVE GAMING</h3>
        <br>
        <h2>JOIN US FOR THIS EXCLUSIVE LIVE EVENT</h2>
        <h1><?php echo htmlspecialchars($sdate); ?> at <?php echo htmlspecialchars($stime); ?> Eastern</h1>
        <a href="#" class="ctn_btn">CLAIM MY SPOT!</a>
        <br><br>
        <h3>THE TOURNAMENT STARTS IN:</h3>
        <div class="countdown">
            <div id="days">0</div>
            <div id="hours">0</div>
            <div id="minutes">0</div>
            <div id="seconds">0</div>
        </div>
    </div>
</div>

<div class="container">
    <h2>Tournament Registration</h2>
    <form id="registration_form" action="register.php" method="post" enctype="multipart/form-data">

        <!-- Solo Registration  -->
        <?php if ($match_type === 'solo'): ?>
            <div id="solo_reg">
                <h3>Solo Registration</h3>
                <div class="form-group">
                    <label for="solo_name">Player Name:</label>
                    <input type="text" id="solo_name" name="solo_name" required>
                </div>
                <div class="form-group">
                    <label for="solo_email">Email:</label>
                    <input type="email" id="solo_email" name="solo_email" required>
                </div>
                <div class="form-group">
                    <label for="solo_logo">Upload Logo:</label>
                    <input type="file" id="solo_logo" name="logo">
                </div>
            </div>

        <!-- Duo Registration  -->
        <?php elseif ($match_type === 'duo'): ?>
            <div id="duo_reg">
                <h3>Duo Registration</h3>
                <div class="form-group">
                    <label for="duo_name">Team Name:</label>
                    <input type="text" id="duo_name" name="duo_name" required>
                </div>
                <div class="form-group">
                    <label for="duo_mentor">Mentor Name:</label>
                    <input type="text" id="duo_mentor" name="duo_mentor" required>
                </div>
                <div class="form-group">
                    <label for="duo_email">Email:</label>
                    <input type="email" id="duo_email" name="duo_email" required>
                </div>
                
                <div id="players">
                    <div class="player">
                        <div class="form-group">
                            <label for="duop1_name">Player Name:</label>
                            <input type="text" id="duop1_name" name="duop1_name" required>
                        </div>
                        <div class="form-group">
                            <label for="duop1_email">Player Email:</label>
                            <input type="email" id="duop1_email" name="duop1_email" required>
                        </div>
                        <div class="form-group">
                            <label for="duop1_role">Player Role:</label>
                            <input type="text" id="duop1_role" name="duop1_role">
                        </div>
                        <div class="form-group">
                            <label for="duop1_ign">Player IGN:</label>
                            <input type="text" id="duop1_ign" name="duop1_ign">
                        </div>
                    </div>
                    <div class="player">
                        <div class="form-group">
                            <label for="duop2_name">Player Name:</label>
                            <input type="text" id="duop2_name" name="duop2_name" required>
                        </div>
                        <div class="form-group">
                            <label for="duop2_email">Player Email:</label>
                            <input type="email" id="duop2_email" name="duop2_email" required>
                        </div>
                        <div class="form-group">
                            <label for="duop2_role">Player Role:</label>
                            <input type="text" id="duop2_role" name="duop2_role">
                        </div>
                        <div class="form-group">
                            <label for="duop2_ign">Player IGN:</label>
                            <input type="text" id="duop2_ign" name="duop2_ign">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="duo_logo">Upload Logo:</label>
                    <input type="file" id="duo_logo" name="logo">
                </div>
            </div>

        <!-- Squad Registration  -->
        <?php elseif ($match_type === 'squad'): ?>
            <div id="squad_reg">
                <h3>Squad Registration</h3>
                <div class="form-group">
                    <label for="sqd_name">Team Name:</label>
                    <input type="text" id="sqd_name" name="sqd_name" required>
                </div>
                <div class="form-group">
                    <label for="sqd_captain">Captain Name:</label>
                    <input type="text" id="sqd_captain" name="sqd_captain" required>
                </div>
                <div class="form-group">
                    <label for="sqd_email">Email:</label>
                    <input type="email" id="sqd_email" name="sqd_email" required>
                </div>

                <div id="players">
                <div class="player">
                    <h3>Player</h3>
                    <label for="sqdp1_name">Name:</label>
                    <input type="text" id="sqdp1_name" name="sqdp1_name" required>

                    <label for="sqdp1_email">Email:</label>
                    <input type="email" id="sqdp1_email" name="sqdp1_email" required>

                    <label for="sqdp1_role">Role:</label>
                    <input type="text" id="sqdp1_role" name="sqdp1_role" required>

                    <label for="sqdp1_ign">Player IGN:</label>
                    <input type="text" id="sqdp1_ign" name="sqdp1_ign" required>
                </div>
                <div class="player">
                    <h3>Player</h3>
                    <label for="sqdp2_name">Name:</label>
                    <input type="text" id="sqdp2_name" name="sqdp2_name" required>

                    <label for="sqdp2_email">Email:</label>
                    <input type="email" id="sqdp2_email" name="sqdp2_email" required>

                    <label for="sqdp2_role">Role:</label>
                    <input type="text" id="sqdp2_role" name="sqdp2_role" required>

                    <label for="sqdp2_ign">Player IGN:</label>
                    <input type="text" id="sqdp2_ign" name="sqdp2_ign" required>
                </div>
                <div class="player">
                    <h3>Player 3</h3>
                    <label for="sqdp3_name">Name:</label>
                    <input type="text" id="sqdp3_name" name="sqdp3_name" required>

                    <label for="sqdp3_email">Email:</label>
                    <input type="email" id="sqdp3_email" name="sqdp3_email" required>

                    <label for="sqdp3_role">Role:</label>
                    <input type="text" id="sqdp3_role" name="sqdp3_role" required>

                    <label for="sqdp3_ign">Player IGN:</label>
                    <input type="text" id="sqdp3_ign" name="sqdp3_ign" required>
                </div>
                <div class="player">
                    <h3>Player 4</h3>
                    <label for="sqdp4_name">Name:</label>
                    <input type="text" id="sqdp4_name" name="sqdp4_name" required>

                    <label for="sqdp4_email">Email:</label>
                    <input type="email" id="sqdp4_email" name="sqdp4_email" required>

                    <label for="sqdp4_role">Role:</label>
                    <input type="text" id="sqdp4_role" name="sqdp4_role" required>

                    <label for="sqdp4_ign">Player IGN:</label>
                    <input type="text" id="sqdp4_ign" name="sqdp4_ign" required>
                </div>
                <div class="player">
                    <h3>Substitute</h3>
                    <label for="sqdsb_name">Name:</label>
                    <input type="text" id="sqdsb_name" name="sqdsb_name">

                    <label for="sqdsb_email">Email:</label>
                    <input type="email" id="sqdsb_email" name="sqdsb_email">

                    <label for="sqdsb_role">Role:</label>
                    <input type="text" id="sqdsb_role" name="sqdsb_role">

                    <label for="sqdsb_ign">Substitute IGN:</label>
                    <input type="text" id="sqdsb_ign" name="sqdsb_ign">
                </div>
              </div>
        <?php endif; ?>

        <input type="hidden" name="match_type" value="<?= htmlspecialchars($match_type) ?>">
        <input type="hidden" name="tournament_id" value="<?= htmlspecialchars($tournament_id) ?>">
        <button type="submit">Register</button>
    </form>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
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
        document.getElementById('registration_form').addEventListener('submit', function(event) {
    event.preventDefault();

    // Validation or form submission logic here
    alert('Registration form submitted!');
});



</script>

<script>
        // Countdown function
        function updateCountdown() {
            var eventDate = new Date('<?php echo $sdate; ?> ' + '<?php echo $stime; ?>');
            var now = new Date().getTime();
            var timeLeft = eventDate - now;

            var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            document.getElementById('days').innerText = days;
            document.getElementById('hours').innerText = hours;
            document.getElementById('minutes').innerText = minutes;
            document.getElementById('seconds').innerText = seconds;
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);


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

    function toggleRegistrationForms(matchType) {
    // Convert matchType to lowercase for case-insensitive comparison
    const normalizedMatchType = matchType.toLowerCase();
    document.getElementById('solo_reg').style.display = normalizedMatchType === 'solo' ? 'block' : 'none';
    document.getElementById('duo_reg').style.display = normalizedMatchType === 'duo' ? 'block' : 'none';
    document.getElementById('sqd_reg').style.display = normalizedMatchType === 'squad' ? 'block' : 'none';
}

// Call the function after the page has fully loaded
window.addEventListener('DOMContentLoaded', function() {
    const matchType = '<?php echo htmlspecialchars($match_type); ?>';
    console.log("Match Type:", matchType); // Debugging: Check if matchType is set correctly
    toggleRegistrationForms(matchType);
});

</script>

<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</body>
</html>