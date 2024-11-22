<?php
require_once 'config.php';
session_start();

// Check if the user is signed in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$tournaments = [];

// Fetch username
$stmt_uname = $conn->prepare("SELECT uname FROM users WHERE id = ?");
if ($stmt_uname) {
    $stmt_uname->bind_param("i", $user_id);
    $stmt_uname->execute();
    $stmt_uname->bind_result($uname);
    if ($stmt_uname->fetch()) {
        $_SESSION['uname'] = $uname;
    } else {
        $error_message = "Error: Username not found for the user ID.";
    }
    $stmt_uname->close();
} else {
    $error_message = "Error preparing the statement: " . $conn->error;
}

// Fetch tournament data
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        WHERE t.user_id = ? 
        ORDER BY t.id";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();  // Use get_result for multiple rows

    if ($result->num_rows > 0) {
        // Fetch all tournaments into an array
        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
    } else {
        $error_message = "No tournament data found.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament statement: " . $conn->error;
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
    <link rel="stylesheet" href="./css/mytournament.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   


    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

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
    <div class="popup-message" id="popup-message"></div>

    <div class="profile-cont">
        <div class="btn-container">
            <button id="walletBtn" class="btn-cnt"><i class='fa fa-money'></i>Wallet</button>
            <button id="updateProfileBtn" class="btn-cnt"><i class='fas fa-user-edit'></i>Profile</button>
            <button id="teamProfileBtn" class="btn-cnt"><i class='fa fa-group'></i>Teams</button>
            <button id="myTournamentBtn" class="btn-cnt"><i class='fa fa-group'></i>My Tournaments</button>
            <button id="changeEmailBtn" class="btn-cnt"><i class='fa fa-envelope'></i>Change Email</button>
            <button id="changePasswordBtn" class="btn-cnt"><i class='fa fa-key'></i>Change Password</button>
            <button id="signoutBtn" class="btn-cnt"><i class='fa fa-sign-out'></i>Sign Out</button>
        </div>

            <div class="profile-container">
                <div class="cover-photo-container">
                    <div class="cover-photo">
                        <input id="coverPhotoFile" name="coverPhotoFile" type="file" onchange="loadCoverPhoto(event)" class="file-input" />
                        <label for="coverPhotoFile" class="cover-photo-label">
                            <span class="icon-wrapper">
                                <i class="fas fa-camera"></i>
                            </span>
                            <span>Change Cover</span>
                        </label>
                        <img id="coverPhoto" name="coverPhoto" src="./img/neon.png" alt="Cover Photo" class="cover-photo-img" />
                        <div class="cover-overlay"></div>
                    </div>
                </div>
                <div class="profile-pic">
                    <input id="profilePicFile" name="profilePicFile" type="file" onchange="loadProfilePic(event)" class="file-input" />
                    <label for="profilePicFile" class="profile-pic-label">
                        <span class="icon-wrapper">
                          <i class="fas fa-camera"></i>
                        </span>
                        <span>Change Profile</span>
                    </label>
                    <img src="./img/dash-logo.png" id="profilePic" name="profilePic" class="profile-pic-img" />
                </div>
            </div>

            <div id="myTournamentsSection" class="profile-section">
              <div class="unique-container">
                <h2 class="unique-header">Organized Tournaments</h2> 
                <div class="ut-container">
                    <div class="ut-header">
                        <a href="organize.php" class="ut-header__button"><i class="fas fa-plus" style="color: white;"></i> CREATE TOURNAMENTS</a>
                    </div>
                    <table class="ut-table">
                        <thead>
                            <tr>
                                <th class="ut-table__head">
                                    <i class='fa fa-trophy' style='color:#00d696'></i> TOURNAMENTS
                                </th>
                                <th class="ut-table__head ut-table__head--status">
                                    <i class='fa fa-flag-checkered' style='color:#00d696'></i> STATUS
                                </th>
                                <th class="ut-table__head ut-table__cell--date">
                                    <i class='fa fa-calendar' style='color:#00d696'></i> DATE
                                </th>
                                <th class="ut-table__head ut-table__cell--prize" style="padding-left: 20px;">
                                    <i class='fas fa-medal' style='color:#00d696'></i> PRIZE
                                </th>
                                <th class="ut-table__head"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tournaments)): ?>
                                <?php foreach ($tournaments as $tournament): ?>
                                    <div class="ut-container">
                                        <table class="ut-table">
                                            <tr class="ut-row" onclick="window.location.href='tournament_details.php?tournament_id=<?php echo $tournament['id']; ?>'" style="cursor: pointer;">
                                                <td class="ut-table__cell ut-table__cell--first">
                                                    <div class="ut-image">
                                                        <?php if (!empty($tournament['bannerimg'])): ?>
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($tournament['bannerimg']); ?>" alt="Tournament Banner">
                                                        <?php else: ?>
                                                            <img src="./img/dash-logo.png" alt="Default Tournament Banner">
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="ut-info">
                                                        <div class="ut-info__name"><?php echo htmlspecialchars($tournament['tname']); ?></div>
                                                        <div class="ut-info__host">Hosted by 
                                                            <span style="color: #00f7ff;">
                                                                <?php echo htmlspecialchars($_SESSION['uname']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="ut-table__cell ut-table__cell--status">
                                                    <div class="ut-status--new">NEW</div>
                                                </td>
                                                <td class="ut-table__cell ut-table__cell--date"><?php echo htmlspecialchars($tournament['sdate']); ?></td>
                                                <td class="ut-table__cell ut-table__cell--prize" style="padding-left: 20px;">
                                                    <?php echo htmlspecialchars($tournament['prizes']); ?>
                                                </td>
                                                <td class="ut-table__cell">
                                                    <a href="tournament_details.php?tournament_id=<?php echo $tournament['id']; ?>">
                                                        <i class='fa fa-eye ut-row__icon-eye'></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5"><?php echo htmlspecialchars($error_message); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
              </div>
          </div>
    </div>


    <!-- FOOTER -->
    <footer>
      <!-- Social -->
      <div class="background-primary padding text-center">
        <a href="/"><i class="icon-facebook_circle text-size-30 text-white"></i></a> 
        <a href="/"><i class="icon-twitter_circle text-size-30 text-white"></i></a>
        <a href="/"><i class="icon-google_plus_circle text-size-30 text-white"></i></a>
        <a href="/"><i class="icon-instagram_circle text-size-30 text-white"></i></a> 
        <a href="/"><i class="icon-linked_in_circle text-size-30 text-white"></i></a>                                                                       
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
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">FAQ</a><br>      
               <a class="text-primary-hover" href="contact-1.html">Contact Us</a><br>
               <a class="text-primary-hover" href="blog.html">Blog</a>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Term of Use</h4>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">Terms and Conditions</a><br>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">Refund Policy</a><br>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">Disclaimer</a>
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
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="js/template-scripts.js"></script> 

    <!-- Popup page Scripts -->
<script>
   document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

  document.addEventListener('DOMContentLoaded', () => {
    // Get all buttons in the button container
    const buttons = document.querySelectorAll('.btn-cnt');
  
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove 'active' class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
  
            // Add 'active' class to the clicked button
            this.classList.add('active');
  
            // Determine the URL to redirect based on button ID
            let redirectUrl = '';
            switch (this.id) {
                case 'walletBtn':
                    redirectUrl = 'wallet.php';
                    break;
                case 'updateProfileBtn':
                    redirectUrl = 'dashboard.php';
                    break;
                case 'teamProfileBtn':
                    redirectUrl = 'teams.php';
                    break;
                case 'myTournamentBtn':
                    redirectUrl = 'mytournaments.php';
                break;
                case 'changeEmailBtn':
                    redirectUrl = 'change_email.php';
                    break;
                case 'changePasswordBtn':
                    redirectUrl = 'change_password.php';
                    break;
                case 'signoutBtn':
                    redirectUrl = 'logout.php';
                    break;
                default:
                    redirectUrl = 'dashboard.php'; // Default fallback URL
            }
  
            // Redirect to the appropriate page
            window.location.href = redirectUrl;
        });
    });
  });

function loadCoverPhoto(event) {
    const coverPhoto = document.getElementById('coverPhoto');
    coverPhoto.src = URL.createObjectURL(event.target.files[0]);
}

function loadProfilePic(event) {
    const profilePic = document.getElementById('profilePic');
    profilePic.src = URL.createObjectURL(event.target.files[0]);
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
    popup.style.display = 'block';
    setTimeout(() => {
      popup.style.display = 'none';
    }, 3000); // Hide after 3 seconds
  }

  // Example usage for PHP error and success messages
  document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($success_message)): ?>
      showPopupMessage("<?php echo $success_message; ?>", 'success');
    <?php elseif (!empty($error_message)): ?>
      showPopupMessage("<?php echo $error_message; ?>", 'error');
    <?php endif; ?>
  });

    // Check if there's a success message and display it
    <?php if (!empty($success_message)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        showPopupMessage("<?php echo $success_message; ?>", 'success');
      });
    <?php endif; ?>
    
</script>
<script>
function redirectToDetails(row) {
    var tournamentId = row.getAttribute('data-id');
    window.location.href = 'tournament_details.php?tournament_id=' + tournamentId;
}
</script>

  </body>
</html>
