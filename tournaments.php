<?php
require_once 'config.php';
session_start();

// Check if user is signed in
$isSignin = isset($_SESSION['isSignin']) && $_SESSION['isSignin'];

if (!$isSignin) {
    header('Location: signin.php');
    exit();
}

// Retrieve user_id from session
if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}
$user_id = $_SESSION['user_id']; // Initialize user_id here

$stmt_uname = $conn->prepare("SELECT uname FROM users WHERE id = ?");
if ($stmt_uname) {
    $stmt_uname->bind_param("i", $user_id);
    $stmt_uname->execute();
    $stmt_uname->bind_result($uname);
    if ($stmt_uname->fetch()) {
        $_SESSION['uname'] = $uname;
    } else {
        die("Error: Username not found for the user ID."); // Handle error properly
    }
    $stmt_uname->close();
} else {
    die("Error preparing the statement: " . $conn->error); // Handle error properly
}

// Fetch all organized tournaments
$sql = "SELECT t.tname, t.selected_game, t.sdate, b.bracket_type, b.prizes, t.bannerimg, u.uname AS host_username 
        FROM tournaments t 
        LEFT JOIN brackets b ON t.id = b.tournament_id 
        LEFT JOIN users u ON t.user_id = u.id";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $tournaments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Error fetching tournaments: " . $conn->error); // Handle error properly
}

// Close connection
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
    <link rel="stylesheet" href="./css/tour_org.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
                  <?php if ($isSignin): ?>
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
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              Tournaments
            </h1>
          
          </div>
  
        </header>
        
      </article>  

    </main>

    <!-- Popup Message -->
    <div class="popup-message" id="popup-message"></div>

    <div id="myTournamentsSection" class="profile-section">
        
        <div class="ut-container">
        <h2 class="unique-header">Available Tournaments</h2>
            <div class="ut-header">
                <a href="#" class="ut-header__button">EXPLORE TOURNAMENTS</a>
            </div>
            <table class="ut-table">
                <thead>
                    <tr>
                        <th class="ut-table__head">
                            <i class='fa fa-trophy' style='color:#00d696'></i> TOURNAMENTS
                        </th>
                        <th class="ut-table__head ut-table__head--game">
                            <i class='fa fa-flag-checkered' style='color:#00d696'></i> GAME
                        </th>
                        <th class="ut-table__head ut-table__cell--brackets">
                            <i class='fa fa-calendar' style='color:#00d696'></i> Brackets
                        </th>
                        <th class="ut-table__head ut-table__cell--date">
                            <i class='fa fa-calendar' style='color:#00d696'></i> DATE
                        </th>
                        <th class="ut-table__head ut-table__cell--prize" style="padding-left: 20px;">
                            <i class='fas fa-medal' style='color:#00d696'></i> PRIZE
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tournaments)): ?>
                        <?php foreach ($tournaments as $tournament): ?>
                        <tr class="ut-row">
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
                                        <span style="color: #00d696;">
                                            <?php echo htmlspecialchars($tournament['host_username']); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="ut-table__cell ut-table__cell--game">
                                <?php echo htmlspecialchars($tournament['selected_game']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--brackets">
                                <?php echo htmlspecialchars($tournament['bracket_type']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--date">
                                <?php echo htmlspecialchars($tournament['sdate']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--prize">
                                <?php echo htmlspecialchars($tournament['prizes']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No tournaments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
               <img src="img/logo.png" alt="">
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
    <script type="text/javascript" src="./js/template-scripts.js"></script> 
    <script src="./js/tour_org.js"></script>

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


<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>