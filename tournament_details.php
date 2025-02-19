<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}



// Handle participant/team removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove']) && $_POST['remove'] === 'yes') {
  $tournament_id = $_POST['tournament_id']; // Tournament ID from the form
  $team_name = $_POST['team_name']; // Team name from the form
  $match_type = $_POST['match_type']; // Match type from the form

  $sql_remove = '';
  if ($match_type === 'solo') {
      // Remove a solo player
      $sql_remove = "DELETE FROM solo_registration WHERE tournament_id = ? AND player_name = ?";
  } elseif ($match_type === 'duo') {
      // Remove a duo team and its players
      $sql_remove = "DELETE dr, dp 
                     FROM duo_registration dr
                     LEFT JOIN duo_players dp ON dr.duo_id = dp.duo_id
                     WHERE dr.tournament_id = ? AND dr.team_name = ?";
  } elseif ($match_type === 'squad') {
      // Remove a squad team and its players
      $sql_remove = "DELETE sr, sp 
                     FROM squad_registration sr
                     LEFT JOIN squad_players sp ON sr.squad_id = sp.squad_id
                     WHERE sr.tournament_id = ? AND sr.team_name = ?";
  }

  if ($sql_remove) {
      $stmt_remove = $conn->prepare($sql_remove);
      if ($stmt_remove) {
          // Bind parameters for tournament ID and team name
          $stmt_remove->bind_param("is", $tournament_id, $team_name);
          if ($stmt_remove->execute()) {
              // $success_message = "Team successfully removed from the tournament.";
              header("Location: mytournaments.php?success_message=Team removed successfully");
              exit();

          } else {
              $error_message = "Error executing the removal query: " . $stmt_remove->error;
              echo $error_message;
          }
          $stmt_remove->close();
      } else {
          $error_message = "Error preparing the removal query: " . $conn->error;
          echo $error_message;
      }
  }
}

$tournament_id = intval($_GET['tournament_id']);
$tournament = [];
$error_message = '';
$slots_full_message = ''; // Variable to hold the slots full message

// Fetch tournament details including creator name
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS creator_name  -- Use 'uname' for the creator's name
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        LEFT JOIN users u ON t.user_id = u.id  
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tournament = $result->fetch_assoc(); // Fetch tournament details

        // Assign variables from the $tournament array
        $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
        $tname = $tournament['tname'] ?? 'Unknown Game';
        $sdate = $tournament['sdate'] ?? '';
        $stime = $tournament['stime'] ?? '';
        $bracket_type = $tournament['bracket_type'] ?? '';
        $match_type = $tournament['match_type'] ?? '';
        $about = $tournament['about'] ?? '';
        $rules = $tournament['rules'] ?? '';
        $prizes = $tournament['prizes'] ?? '';
        $social_media_input = $tournament['social_media_input'] ?? '';
        $bannerimg = $tournament['bannerimg'] ?? '';
        $creator_name = $tournament['creator_name'] ?? 'Unknown Creator'; // Get creator's name

        // Initialize registered count variable
        $registered_count = 0;

        // Calculate registered teams/players based on match type
        if ($match_type == 'solo') {
            $sql_solo = "SELECT COUNT(*) AS registered FROM solo_registration WHERE tournament_id = ?";
            $stmt_solo = $conn->prepare($sql_solo);
            if ($stmt_solo) {
                $stmt_solo->bind_param("i", $tournament_id);
                $stmt_solo->execute();
                $result_solo = $stmt_solo->get_result();
                $row_solo = $result_solo->fetch_assoc();
                $registered_count = $row_solo['registered'];
                $total_teams = $tournament['solo_players'];
                $registration_message = "Players Registered";
                $stmt_solo->close(); // Close the statement here
            }
        } elseif ($match_type == 'duo') {
            $sql_duo = "SELECT COUNT(*) AS registered FROM duo_registration WHERE tournament_id = ?";
            $stmt_duo = $conn->prepare($sql_duo);
            if ($stmt_duo) {
                $stmt_duo->bind_param("i", $tournament_id);
                $stmt_duo->execute();
                $result_duo = $stmt_duo->get_result();
                $row_duo = $result_duo->fetch_assoc();
                $registered_count = $row_duo['registered'];
                $total_teams = $tournament['duo_teams'];
                $registration_message = "Teams Registered";
                $stmt_duo->close(); // Close the statement here
            }
        } elseif ($match_type == 'squad') {
            $sql_squad = "SELECT COUNT(*) AS registered FROM squad_registration WHERE tournament_id = ?";
            $stmt_squad = $conn->prepare($sql_squad);
            if ($stmt_squad) {
                $stmt_squad->bind_param("i", $tournament_id);
                $stmt_squad->execute();
                $result_squad = $stmt_squad->get_result();
                $row_squad = $result_squad->fetch_assoc();
                $registered_count = $row_squad['registered'];
                $total_teams = $tournament['squad_teams'];
                $registration_message = "Teams Registered";
                $stmt_squad->close(); // Close the statement here
            }
        } else {
            $registered_count = 0; // Default to 0 if the match type is invalid
            $total_teams = 0; // Default total to 0
            $registration_message = "Unknown Registration Type"; // Default message
        }

        // Check if the slots are full
        if ($registered_count >= $total_teams) {
            $slots_full_message = "Slots Full"; // Show this message if slots are full
        }

        // Display the teams/players-registered message
        $teams_registered_message = "$registered_count / $total_teams $registration_message";

        // Fetch registered participants
        $participants = []; // Initialize an array to hold participants' details
        if ($match_type == 'solo') {
            $sql_participants = "SELECT player_name AS team_name, NULL AS logo_path 
                                 FROM solo_registration 
                                 WHERE tournament_id = ?";
        } elseif ($match_type == 'duo') {
            $sql_participants = "SELECT team_name, logo_path 
                                 FROM duo_registration 
                                 WHERE tournament_id = ?";
        } elseif ($match_type == 'squad') {
            $sql_participants = "SELECT team_name, logo_path 
                                 FROM squad_registration 
                                 WHERE tournament_id = ?";
        }

        if (isset($sql_participants)) {
            $stmt_participants = $conn->prepare($sql_participants);
            if ($stmt_participants) {
                $stmt_participants->bind_param("i", $tournament_id);
                $stmt_participants->execute();
                $result_participants = $stmt_participants->get_result();
                while ($row = $result_participants->fetch_assoc()) {
                    $participants[] = $row;
                }
                $stmt_participants->close(); // Close this statement
            } else {
                $error_message = "Error preparing the participants query: " . $conn->error;
            }
        }

        

    } else {
        $error_message = "No tournament found with that ID.";
    }
    $stmt->close(); // Close the tournament detail statement
} else {
    $error_message = "Error preparing the tournament detail statement: " . $conn->error;
}

$conn->close();

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
    <link rel="stylesheet" href="./css/template-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   


    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 


    <style>
      
.banner-cont{
  background-color: #282828;
  margin-top: 0;
}
/* Center the entire profile-container */
.banner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 70vh; /* Full height of the viewport */
  position: relative;
}

/* Styling the cover photo container */
.banner-img-container {
  width: 100%;
  display: flex;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

/* Cover photo styling */
.banner-img {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

/* Cover photo image */
.banner-photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: relative;
}


.tournament-operation {
    display: flex;
    justify-content: flex-end;
    padding: 10px 20px;
}

.operation-btn {
    display: flex;
    gap: 10px;
    margin-right: 20px;
    position: relative; 
}

.operation-btn button {
    background-color: rgb(30, 196, 141);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease, color 0.3s ease;
    position: relative; 
}

.operation-btn button:hover {
    color: black;
}

.operation-btn .options {
    background-color: #a75928;
}

.operation-btn .options:hover {
    color: black; 
}

/* Dropdown menu styles */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 5px;
    list-style: none;
    padding: 0;
    margin: 0;
    width: 200px; /* Adjust as needed */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 10; 
}

.dropdown-menu li {
    padding: 10px;
    display: flex;
    align-items: center;
}

.dropdown-menu li i {
    margin-right: 8px; 
}

.dropdown-menu li:hover {
    background-color: #f1f1f1;
}

/* Show dropdown on hover */
.organizer-actions:hover .dropdown-menu {
    display: block;
}


.gameuser {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white; 
    padding: 10px 0; 
    font-size: 21px;
}

.gameuser .titlename {
    color: aquamarine; 
    margin: 0 5px; 
    font-size: 24px;
}

.tournament-details {
    display: flex;
    justify-content: space-around; /* Spreads the items evenly */
    align-items: center; /* Vertically centers the items */
    padding: 20px; /* Adds padding around the container */
    border-top: 1px solid grey;
    border-radius: 8px; /* Optional: adds rounded corners */
}

.details, .rules, .prizes, .schedule, .contact {
    width: 100%;
    border-right: 1px solid grey;
    padding: 10px 20px; /* Adds space inside each div */
    color: #ddd;
    border-radius: 5px; /* Rounded corners */
    text-align: center; /* Centers the text inside the div */
    cursor: pointer; /* Adds a pointer on hover */
    transition: background-color 0.3s ease; /* Smooth hover transition */
}

.details:hover, .rules:hover, .prizes:hover, .schedule:hover, .contact:hover {
    color: rgb(0, 255, 170); /* Changes background on hover */
}

.tour-title {
    font-size: 20px; /* Adjust font size */
    font-weight: bold;
}

/* Hide all content containers by default */
.content-container {
    display: none;
}

/* Show only the active content container */
.content-container.active {
    display: block;
}

/* Flexbox layout for containers */
.container-row {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.content-container {
    flex: 1;
    min-width: 200px;
    margin: 10px;
    padding: 10px;
    border-top: 1px solid grey;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.tournament-details div {
    cursor: pointer;
    margin: 5px;
}
.content-title{
  font-size: 18px; /* Adjust font size */
  font-weight: bold;
  color: #ffffff;
  margin: 5px;
  padding: 5px;
}

.cont-title{
  font-size: 16px; /* Adjust font size */
  color: #ffffff;
  margin: 5px;
  margin-right: 10px;
  padding:10px 5px;
  border-bottom: 0.5px solid grey;
}


.gameuser {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white; 
    padding: 10px 0; 
    font-size: 21px;
}

.gameuser .titlename {
    color: aquamarine; 
    margin: 0 5px; 
    font-size: 24px;
}


.teams-registered {
    font-size: 20px; 
    color: #fff; 
    margin-top: 15px;
    font-weight: bold;
    text-align: center; 
}
    </style>
  </head>

  <body class="size-1280 primary-color-red">  
    <div id="preloader" style="background: #000 url(./img/loader.gif) no-repeat center center; 
        background-size: 4.5%;height: 100vh;width: 100%;position: fixed;z-index: 999;">
    </div>
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
            <li><a href="tournaments.php">Tournaments</a></li>
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
    

    <!-- Popup Message -->
    <div class="popup-message" id="popup-message">

    </div><div class="banner-cont">
    <div class="banner-container">
        <div class="banner-img-container">
            <div class="banner-img">
                <?php if (!empty($bannerimg)): ?>
                    <img id="bannerimg" name="bannerimg" src="image.php?tournament_id=<?php echo urlencode($tournament_id); ?>" alt="Banner Image" class="banner-photo-img" />
                <?php else: ?>
                    <p>No banner image available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="tournament">
        <div class="tournament-operation">
            <div class="operation-btn">
                <button class="organizer-actions">
                    <i class='fa fa-gears'></i> Organizer Actions
                    <ul class="dropdown-menu">
                      <li style="margin-bottom: 10px;">
                        <i class='fa fa-edit'></i>
                        <a href="edit_tour.php?tournament_id=<?php echo urlencode($tournament_id); ?>" style="text-decoration: none; color: #007bff;">
                          Edit Tournament
                        </a>
                      </li>
                      <ul>
                          <li style="margin-bottom: 10px;">
                              <a href="javascript:void(0);" style="text-decoration: none; color: #dc3545;" 
                                onclick="confirmDelete()">
                                  <i class="fa fa-trash"></i> Delete Tournament
                              </a>
                          </li>
                      </ul>
                    </ul>
                </button>
                <button onclick="start_game(<?php echo urlencode($tournament_id); ?>)">
                    <i class='fa fa-play'></i> Start Game
                </button>
                <button class="options"><i class='fa fa-share-alt'></i> Share</button>
                <button class="options"><i class="fas fa-cog"></i> Options</button>
            </div>
        </div>

        <div class="gameuser">
            <p class="titlename"><?php echo htmlspecialchars($selected_game); ?></p>
            Tournament By:
            <p class="titlename"><?php echo htmlspecialchars($creator_name); ?></p>
        </div>
        
        <p class="teams-registered"><?php echo $teams_registered_message; ?></p>
    </div>

    <div class="tournament-details" id="tournament-details">
        <div id="details" class="details active" onclick="showContent('details')">
            <span class="tour-title">Details</span>
        </div>

        <div id="rules" class="rules" onclick="showContent('rules')">
            <span class="tour-title">Rules</span>
        </div>

        <div id="prizes" class="prizes" onclick="showContent('prizes')">
            <span class="tour-title">Prizes</span>
        </div>

        <div id="schedule" class="schedule" onclick="showContent('schedule')">
            <span class="tour-title">Participants</span>
        </div>

        <div id="contact" class="contact" onclick="showContent('contact')">
            <span class="tour-title">Contact</span>
        </div>
    </div>

    <div class="container-row">
        <div class="content-container details-container active" id="details-container">
            <p class="content-title">Game Name</p>
            <p class="cont-title"><?php echo htmlspecialchars($selected_game); ?></p>

            <p class="content-title">Start Date</p>
            <p class="cont-title"><?php echo htmlspecialchars($sdate); ?></p>

            <p class="content-title">Start Time</p>
            <p class="cont-title"><?php echo htmlspecialchars($stime); ?></p>

            <p class="content-title">Bracket Type</p>
            <p class="cont-title"><?php echo htmlspecialchars($bracket_type); ?></p>

            <p class="content-title">Match Type</p>
            <p class="cont-title"><?php echo htmlspecialchars($match_type); ?></p>

            <p class="content-title">About Game</p>
            <p class="cont-title"><?php echo htmlspecialchars($about); ?></p>
        </div>

        <div class="content-container rules-container" id="rules-container">
            <p class="content-title">Game Critical Rules</p>
            <p class="cont-title"><?php echo htmlspecialchars($rules); ?></p>
        </div>

        <div class="content-container prizes-container" id="prizes-container">
            <p class="content-title">Prize Details</p>
            <p class="cont-title"><?php echo htmlspecialchars($prizes); ?></p>
        </div>

        <div class="content-container schedule-container" id="schedule-container">
            <p class="content-title">Registered Teams</p>
            <p class="cont-title"> </p>
            <?php
if (!empty($participants)) {
    $counter = 1; // Initialize the counter
    foreach ($participants as $participant) {
        ?>
        <div style="margin-bottom: 20px; padding: 10px; border-bottom: 1px solid #ddd; border-radius: 5px; background: #282828;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <!-- Team Logo -->
                <div class="small-banner" style="width: 40px; height: 40px; overflow: hidden; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2);">
                    <?php
                    $logo_path = !empty($participant['logo_path']) && file_exists('uploads/' . htmlspecialchars($participant['logo_path']))
                                ? 'uploads/' . htmlspecialchars($participant['logo_path'])
                                : 'uploads/dash-logo.png';
                    echo '<img src="' . $logo_path . '" alt="Team Logo" style="width: 100%; height: 100%; object-fit: cover;">';
                    ?>
                </div>
                <!-- Team Name -->
                <span style="flex: 1; margin-left: 10px; font-weight: bold;"><?php echo $counter . ". " . htmlspecialchars($participant['team_name']); ?></span>
                <!-- Remove Team Button -->
                <button onclick="confirmRemove(<?php echo urlencode($tournament_id); ?>, '<?php echo $match_type; ?>', '<?php echo htmlspecialchars($participant['team_name']); ?>')">
                      <i class="fa fa-trash"></i> Remove Team
                </button>
            </div>

            <!-- Players Row -->
            <?php if (!empty($participant['players'])): ?>
                <div style="display: flex; align-items: center; margin-left: 50px; gap: 10px; flex-wrap: wrap;">
                    <?php foreach ($participant['players'] as $player): ?>
                        <span style="background: #e0e0e0; padding: 5px 10px; border-radius: 20px; font-size: 14px;"><?php echo htmlspecialchars($player); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $counter++; // Increment the counter
    }
} else {
    echo "<p>No participants registered yet.</p>";
}
?>


        </div>

        <div class="content-container contact-container" id="contact-container">
            <p class="content-title">Contact Info</p>
            <p class="cont-title"><?php echo htmlspecialchars($social_media_input); ?></p>
        </div>
    </div>
</div>

            

    <!-- FOOTER -->
    <footer>
      <!-- Social -->
      <div class="background-primary padding text-center">
        <a href="#"><i class="icon-facebook_circle text-size-30 text-white"></i></a> 
        <a href="#"><i class="icon-twitter_circle text-size-30 text-white"></i></a>
        <a href="#"><i class="icon-google_plus_circle text-size-30 text-white"></i></a>
        <a href="#"><i class="icon-instagram_circle text-size-30 text-white"></i></a> 
        <a href="#"><i class="icon-linked_in_circle text-size-30 text-white"></i></a>                                                                       
      </div>
      <!-- Animated Logos -->
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
            <button onclick="window.location.href='our-services.php'" class="animated-btn right-button">&nbsp;&nbsp;Become our Client&nbsp;&nbsp;</button>
        </div>
      </div>
      <section class="section background-dark">
        <!-- Main Footer -->
        <div class="line"> 
          <div class="margin2x">
            <div class="hide-s hide-m hide-l xl-2">
              <img src="img/logo.png" alt="">
            </div>
            <div class="s-12 m-6 l-3 xl-3">
               <h4 class="text-white text-strong">Our Mission</h4>
               <p style="text-align: justify;">
                To create a thriving esports ecosystem where players can showcase their skills, 
                teams can compete at the highest level, and fans can experience the excitement 
                of world-class gaming events.
               </p>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Useful Links</h4> 
               <a class="text-primary-hover" href="index.php">Home</a><br>
               <a class="text-primary-hover" href="news.php">News</a><br>     
               <a class="text-primary-hover" href="our-services.php">Contact Us</a><br>
               <a class="text-primary-hover" href="about-us.php">About Us</a><br>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Term of Use</h4>
               <a class="text-primary-hover" href="faq.php">FAQ</a><br>
               <a class="text-primary-hover" href="privacy-policy.php">Privacy Policy</a><br>
               <a class="text-primary-hover" href="disclaimer.php">Disclaimer</a>
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
            <p class="text-size-16 margin-bottom-0">Copyright 2024 &Sigma;Indra65 , MK38 - BCA 2K22</p>
            <p class="text-size-12">Copyright 2024 InfiKnight Esports. All Rights Reserved.</p>
          </div>
          <div class="s-12 l-6">
            <a class="right text-size-12 text-primary-hover" href="#" title="Team InfiKnight">Developed by Team <span style="font-size: 25px;">&infin;</span>
            </a>
          </div>
        </div>  
      </section>
    </footer>
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="js/template-scripts.js"></script> 
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
<script>
function showContent(section) {
    // Hide all content containers
    var containers = document.querySelectorAll('.content-container');
    containers.forEach(function(container) {
        container.classList.remove('active');
    });

    // Show the clicked section
    var activeContainer = document.getElementById(section + '-container');
    if (activeContainer) {
        activeContainer.classList.add('active');
    }
}


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



function showContent(section) {
    // Get all tabs and content containers
    var tabs = document.querySelectorAll('.tournament-details > div');
    var containers = document.querySelectorAll('.content-container');

    // Remove the 'active' class and hide all containers
    tabs.forEach(function(tab) {
        tab.classList.remove('active');
    });
    containers.forEach(function(container) {
        container.style.display = 'none';
    });

    // Add the 'active' class to the clicked tab and show the corresponding container
    document.getElementById(section).classList.add('active');
    document.getElementById(section + '-container').style.display = 'block';
}

</script>
<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
<script>
  function start_game(tournamentId) {
    // Redirect to start_game.php with tournament_id
    window.location.href = 'start_game.php?tournament_id=' + tournamentId;
  }
</script>
<script>
function confirmDelete() {
    if (confirm("Are you sure you want to delete this tournament?")) {
        // If confirmed, submit the form to delete the tournament
        var form = document.createElement("form");
        form.method = "POST";
        form.action = ""; // Submit to the same page
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = "delete_tournament";
        input.value = "yes";
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<script type="text/javascript">
   function confirmRemove(tournamentId, matchType, teamName) {
    if (confirm("Are you sure you want to remove the team from this tournament?")) {
        // Create a form dynamically
        var form = document.createElement("form");
        form.method = "POST";
        form.action = ""; // Submit to the same page

        // Hidden input fields for tournament data
        var inputTournamentId = document.createElement("input");
        inputTournamentId.type = "hidden";
        inputTournamentId.name = "tournament_id";
        inputTournamentId.value = tournamentId;
        form.appendChild(inputTournamentId);

        var inputRemove = document.createElement("input");
        inputRemove.type = "hidden";
        inputRemove.name = "remove";
        inputRemove.value = "yes"; // Custom field to indicate removal action
        form.appendChild(inputRemove);

        var inputMatchType = document.createElement("input");
        inputMatchType.type = "hidden";
        inputMatchType.name = "match_type";
        inputMatchType.value = matchType;
        form.appendChild(inputMatchType);

        var inputTeamName = document.createElement("input");
        inputTeamName.type = "hidden";
        inputTeamName.name = "team_name";
        inputTeamName.value = teamName;
        form.appendChild(inputTeamName);

        // Append form and submit
        document.body.appendChild(form);
        form.submit();
    }
}

</script>
</body>
</html>



