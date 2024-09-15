<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();

$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $selected_game = $_POST['selected_game'];
    $tname = $_POST['tname'];
    $sdate = $_POST['sdate'];
    $stime = $_POST['stime'];

    // Handle file upload
    if (isset($_FILES['bannerimg']) && $_FILES['bannerimg']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['bannerimg']['tmp_name'];
        $file_name = basename($_FILES['bannerimg']['name']);
        $upload_dir = 'uploads/'; // Ensure this directory exists and is writable
        $bannerimg = $upload_dir . $file_name;
        move_uploaded_file($file_tmp, $bannerimg);
    } else {
        $bannerimg = ''; // Handle the case where no file is uploaded
    }

    $about = $_POST['about'];
    $bracket_type = $_POST['bracket-type'];
    $match_type = $_POST['match-type'];
    $solo_players = $_POST['solo-players'];
    $duo_teams = $_POST['duo-teams'];
    $squad_teams = $_POST['squad-teams'];
    $solo_adv = $_POST['solo-adv'];
    $duo_adv = $_POST['duo-adv'];
    $squad_adv = $_POST['squad-adv'];
    $solo_rounds = $_POST['solo-rounds'];
    $duo_rounds = $_POST['duo-rounds'];
    $squad_rounds = $_POST['squad-rounds'];
    $placement_points = isset($_POST['placement_points']) ? $_POST['placement_points'] : NULL;
    $contact = $_POST['contact'];
    $rules = $_POST['rules'];
    $prizes = $_POST['prizes'];

    // Process streams
    $streams = array();
    if (isset($_POST['select-provider']) && is_array($_POST['select-provider'])) {
        foreach ($_POST['select-provider'] as $key => $provider) {
            $streams[] = array(
                'provider' => $provider,
                'channel_name' => $_POST['channel-name'][$key]
            );
        }
    }

    // Insert tournament data
    $query = "INSERT INTO tournaments (selected_game, tname, sdate, stime, bannerimg, about) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssss", $selected_game, $tname, $sdate, $stime, $bannerimg, $about);
    if (!$stmt->execute()) {
        die("Error inserting tournament data: " . $stmt->error);
    }
    $tournament_id = $stmt->insert_id;

    // Insert bracket data
    $query = "INSERT INTO brackets (tournament_id, bracket_type, match_type, solo_players, duo_teams, squad_teams, solo_adv, duo_adv, squad_adv, solo_rounds, duo_rounds, squad_rounds, placement_points, contact, rules, prizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iissiiiiiiiiisss", $tournament_id, $bracket_type, $match_type, $solo_players, $duo_teams, $squad_teams, $solo_adv, $duo_adv, $squad_adv, $solo_rounds, $duo_rounds, $squad_rounds, $placement_points, $contact, $rules, $prizes);
    if (!$stmt->execute()) {
        die("Error inserting bracket data: " . $stmt->error);
    }

    // Insert stream data
    foreach ($streams as $stream) {
        $query = "INSERT INTO streams (tournament_id, provider, channel_name) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iss", $tournament_id, $stream['provider'], $stream['channel_name']);
        if (!$stmt->execute()) {
            error_log("Error inserting stream data: " . $stmt->error);
            die("Error inserting stream data: " . $stmt->error);
        }
    }

    // Close connection
    $stmt->close();
    $conn->close();
    header("Location: tournament-details.php");
    exit;
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

    <!-- Include Quill's CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
           <li><a href="#">Tournaments</a>
              <ul>
                <li><a href="#">Upcoming Tournaments</a>
                  <ul class="game_container">
                    <a href="#"><li class="ga_me"> <img src="img/logo/pubg_logo.png" alt="Pubg Logo" class="ga_me-icon">Pubg Mobile</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/ff_logo.png" alt="FF Logo" class="ga_me-icon">Free Fire</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/cs_logo.png" alt="COD Logo" class="ga_me-icon">COD Mobile</li></a>
                    <a href="tour_reg.php" class="all-games"><li class="all-games-text">All Tournaments<i class="fas fa-arrow-right"></i></li></a>
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
        
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
            <div class="line">
              <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
                Organize Tournament
              </h1>
            </div>
          </header>
    </main>
<!-- ++++++++++++++++++++++++++++++++++++++++++++++Form containrerer+++++++++++++++++++++++++++++++++++ -->
    <div id="tournament-form" class="tournament_form">
      <div id="popup-alert" class="popup hidden">
        <i class="fa fa-exclamation-triangle fa-2x" aria-hidden="true"></i>
        <span id="popup-message">Please select a provider.</span>
        <span id="close-popup" class="close-btn">&times;</span>
      </div>
      
          <!-- partial:index.partial.php -->
          <div class="container-fluid" style="width: 100%;">
            <div class="row justify-content-center" style="width: 100%;">
                <div class="col-11 col-sm-10 col-md-10 col-lg-6 col-xl-5 text-center p-0 mt-3 mb-2" style="width: 100%;">
                    <div class="card px-0 pt-4 pb-0 mt-3 mb-3" >
                        <div class="back-arrow-container">
                          <button id="back-arrow" class="btn btn-light">
                              <i class="fa fa-arrow-left"></i> Back
                          </button>
                        </div>
                        <h2 id="heading">Create Tournament</h2>
                        <p>Fill all form field to go to next step</p>
                        <form id="msform" action="create_tour.php" method="post" enctype="multipart/form-data">
                            <!-- progressbar -->
                            <ul id="progressbar">
                                <li class="active" id="setup"><strong>Setup</strong></li>
                                <li id="brackets"><strong>Brackets</strong></li>
                                <li id="stream"><strong>Stream</strong></li>
                                <li id="publish"><strong>Publish</strong></li>
                            </ul>
                            <div class="progress" >
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                            </div> <br> <!-- fieldsets -->
                            <fieldset>
                                <div class="form-card">
                                    <div class="row">
                                        <div class="col-7">
                                            <h2 class="fs-title">Setup Tournament</h2>
                                        </div>
                                        <div class="col-5">
                                            <h2 class="steps">Step 1 - 4</h2>
                                        </div>
                                    </div> 
                                      <label class="fieldlabels">Selected Game</label>
                                      <input type="text" id="selected_game" name="selected_game" readonly />
                                      <label class="fieldlabels">Tournament Name</label> 
                                      <input type="text" name="tname" id="tname" placeholder="Tournament Name" required /> 
                                      <label class="fieldlabels">Start Date</label> 
                                      <input type="date" name="sdate" id="sdate" placeholder="Start Date(DD/MM/YYYY)" required /> 
                                      <label class="fieldlabels">Start Time</label> 
                                      <input type="time" name="stime" id="stime" placeholder="Time displayed in Time displayed in +0545" required /> 
                                      <label class="fieldlabels">Game Banner</label>
                                      <input type="file" id="bannerimg" name="bannerimg" accept="image/*" onchange="showPreview(event);" required />
                                      <div class="preview">
                                        <img id="bannerimg-preview">
                                      </div>
                                      <label class="fieldlabels">About</label>
                                      <div id="editor-container-about" style="height: 200px; display: block !important; height: 200px !important;"></div>
                                      <input type="hidden" name="about" id="about">                           
                                    </div> 
                                      <input type="button" name="next" class="next action-button" value="Next" />
                            </fieldset>
                            <!-- Bracket & Rules -->
                            <fieldset>
                                <div class="form-card">
                                    <div class="row">
                                        <div class="col-7">
                                            <h2 class="fs-title">Bracket & Rules</h2>
                                        </div>
                                        <div class="col-5">
                                            <h2 class="steps">Step 2 - 4</h2>
                                        </div>
                                    </div>
                                    <div class="match-details-container">
                                      <h3 class="fs-titleh3">Bracket Details</h3>
                                    
                                      <div class="bracket-selection">
                                        <label class="brac-label" for="bracket-type">Bracket Type</label>
                                        <select id="bracket-type" name="bracket-type" class="brac-input">
                                          <option value="battle_royal">Battle Royal</option>
                                          <option value="round_robin">Round Robin</option>
                                          <option value="double_elimination" disabled>Double Elimination</option>
                                          <option value="single_elimination" disabled>Single Elimination</option>
                                        </select>
                                      </div>
                                    
                                      <div class="match-selection">
                                        <label class="brac-label" for="match-type">Match Type</label>
                                        <select id="match-type" name="match-type" class="brac-input">
                                          <option value="solo">Solo</option>
                                          <option value="duo">Duo</option>
                                          <option value="squad">Squad</option>
                                        </select>
                                      </div>
                                    
                                      <!-- Solo Container -->
                                      <div id="solo-container" class="match-container" style="display: none;">
                                        <label for="solo-players" class="brac-label">Number of Players</label>
                                        <input type="number" id="solo-players" name="solo-players" class="brac-input">
                                    
                                        <label for="solo-group-advancement" class="brac-label">Group Advancement Style</label>
                                        <select id="solo-adv" name="solo-adv" class="brac-input">
                                          <option value="random">Random</option>
                                          <option value="elimination">Elimination</option>
                                        </select>
                                    
                                        <label for="solo-rounds" class="brac-label">Number of Rounds</label>
                                        <select id="solo-rounds" name="solo-rounds" class="brac-input">
                                          <option value="1">1</option>
                                          <option value="2">2</option>
                                          <option value="3">3</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                        </select>
                                    
                                        <h3 class="fs-titleh3">Placement Point System</h3>
                                        <label for="solo-placement" class="brac-label">Placement</label>
                                        <textarea id="solo-place" name="solo-place" class="brac-input" rows="7" placeholder="#1 = 10pts\n#2 = 8pts\n#3 = 6pts\n#Kill = 1pt"></textarea>
                                      </div>
                                    
                                      <!-- Duo Container -->
                                      <div id="duo-container" class="match-container" style="display: none;">
                                        <label for="duo-teams" class="brac-label">Number of Teams</label>
                                        <input type="number" id="duo-teams" name="duo-teams" class="brac-input">
                                    
                                        <label for="duo-players-per-team" class="brac-label">Players per Team</label>
                                        <input type="number" id="duo-players" name="duo-players" class="brac-input">
                                    
                                        <label for="duo-group-advancement" class="brac-label">Group Advancement Style</label>
                                        <select id="duo-adv " name="duo-adv" class="brac-input">
                                          <option value="random">Random</option>
                                          <option value="elimination">Elimination</option>
                                        </select>
                                    
                                        <label for="duo-rounds" class="brac-label">Number of Rounds</label>
                                        <select id="duo-rounds" name="duo-rounds" class="brac-input">
                                          <option value="1">1</option>
                                          <option value="2">2</option>
                                          <option value="3">3</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                        </select>
                                    
                                        <h3 class="fs-titleh3">Placement Point System</h3>
                                        <label for="duo-placement" class="brac-label">Placement</label>
                                        <textarea id="duo-place " name="duo-place" class="brac-input" rows="7" placeholder="#1 = 10pts\n#2 = 8pts\n#3 = 6pts\n#Kill = 1pt"></textarea>
                                      </div>
                                    
                                      <!-- Squad Container -->
                                      <div id="squad-container" class="match-container" style="display: none;">
                                        <label for="squad-teams" class="brac-label">Number of Teams</label>
                                        <select id="squad-teams" name="squad-teams" class="brac-input">
                                          <option value="1">1</option>
                                          <option value="2">2</option>
                                          <option value="3">3</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                          <option value="7">7</option>
                                          <option value="8">8</option>
                                          <option value="9">9</option>
                                          <option value="10">10</option>
                                          <option value="11">11</option>
                                          <option value="12">12</option>
                                          <option value="13">13</option>
                                          <option value="14">14</option>
                                          <option value="15">15</option>
                                          <option value="16">16</option>
                                          <option value="17">17</option>
                                          <option value="18">18</option>
                                          <option value="19">19</option>
                                          <option value="20">20</option>
                                        </select>
                                    
                                        <label for="squad-players-per-team" class="brac-label">Players per Team</label>
                                        <input type="number" id="squad-players" name="squad-players" class="brac-input">
                                    
                                        <label for="squad-group-advancement" class="brac-label">Group Advancement Style</label>
                                        <select id="squad-adv " name="squad-adv" class="brac-input">
                                          <option value="random">Random</option>
                                          <option value="elimination">Elimination</option>
                                        </select>
                                    
                                        <label for="squad-rounds" class="brac-label">Number of Rounds</label>
                                        <select id="squad-rounds" name="squad-rounds" class="brac-input">
                                          <option value="1">1</option>
                                          <option value="2">2</option>
                                          <option value="3">3</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                        </select>
                                    
                                        <h3 class="fs-titleh3">Placement Point System</h3>
                                        <label for="squad-placement" class="brac-label">Placement</label>
                                        <textarea id="squad-place " name="squad-place" class="brac-input" rows="7" placeholder="#1 = 10pts\n#2 = 8pts\n#3 = 6pts\n#Kill = 1pt"></textarea>
                                      </div>
                                    </div>
                                    
                                    <main class="main-container section-padding">
                                        <div class="unique-input-field">
                                            <label for="social-media" class="unique-label">Social Media</label>
                                            <div class="social-media-row">
                                                <select id="social-media" class="unique-select">
                                                    <option value="">Select a social media</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="twitter">Twitter</option>
                                                    <option value="discord">Discord</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                    <!-- Add more social media options here -->
                                                </select>
                                                <input id="social-media-input" class="dynamic-input" type="text" placeholder="Enter your username">
                                            </div>
                                        </div>
                                        
                                       <dl class="accordion">
                                           <dt class="expand">Contact Details</dt>
                                           <dd> 
                                           <div id="editor-container-contact" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="contact" id="contact">
                                           </dd>
                                           <dt>Critical Rules</dt>
                                           <dd>
                                           <div id="editor-container-rules" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="rules" id="rules">
                                           </dd>
                                           <dt>Prizes</dt>
                                           <dd>
                                           <div id="editor-container-prizes" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="prizes" id="prizes">
                                           </dd>
                                       </dl>
                                   </main>
                                </div> 
                                <input type="button" name="next" class="next action-button" value="Next" /> 
                                <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                            </fieldset>

                              <!-- Streams -->
                            <fieldset>
                                <div class="form-card">
                                    <div class="row">
                                        <div class="col-7">
                                            <h2 class="fs-title">Streams</h2>
                                        </div>
                                        <div class="col-5">
                                            <h2 class="steps">Step 3 - 4</h2>
                                        </div>
                                    </div> 
                                    <div class="new-stream-container">
                                      <div id="new-stream-form" class="new-stream-form hidden">
                                        <div class="form-group">
                                          <div class="stream-input">
                                            <select id="select-provider" name="select-provider" aria-placeholder="Select Provider">
                                              <option value="">Select Provider</option>
                                              <option value="twitch">Twitch</option>
                                              <option value="youtube">YouTube</option>
                                              <option value="facebook">Facebook</option>
                                            </select>
                                          </div>
                                          <div class="stream-input">
                                            <div class="input-wrapper">
                                              <input type="text" id="channel-name" name="channel-name" placeholder=" " />
                                              <label for="channel-name">Enter channel name</label>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="form-buttons">
                                          <button id="save-button" class="save-btn">Save</button>
                                          <button id="remove-button" class="remove-btn">Remove</button>
                                        </div>
                                      </div>
                                      <button id="add-new-stream" class="add-btn">+ Add New Stream</button>
                                    </div>
                                    
                                </div> 
                                  <input type="submit" name="next" class="next action-button" id="create_tour" value="Submit" /> 
                                  <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                            </fieldset>
                            
                            <fieldset>
                                <div class="form-card">
                                  <div class="row">
                                      <div class="col-7">
                                          <h2 class="fs-title">Finish:</h2>
                                      </div>
                                      <div class="col-5">
                                          <h2 class="steps">Step 4 - 4</h2>
                                      </div>
                                  </div> 
                                  <br><br>
                                  <h2 class="purple-text text-center"><strong>Tournament Created</strong></h2> 
                                  <br>
                                  <div class="row justify-content-center">
                                      <div class="col-3">
                                          <!-- <img src="img.png" class="fit-image"> -->
                                      </div>
                                  </div> 
                                  <br><br>
                                  <div class="row justify-content-center">
                                      <div class="col-7 text-center">
                                          <h5 class="purple-text text-center">You Have Successfully Created Tournament</h5>
                                      </div>
                                  </div>
                                  <br>
                                  <div class="row justify-content-center">
                                      <div class="col-7 text-center">
                                        <a href="tournament-details.php" class="button-custom">View Tournament</a>
                                      </div>
                                  </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
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
    <script src="./js/tour_org.js"></script>

    <!-- Popup page Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        myModal.show();
    }, 1000); // 1-second delay before modal appears
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.editor-container').forEach((container) => {
        container.style.display = 'block';
    });

    var quillAbout = new Quill('#editor-container-about', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });
    console.log('Quill About initialized:', quillAbout);

    var quillContact = new Quill('#editor-container-contact', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });
    console.log('Quill Contact initialized:', quillContact);

    var quillRules = new Quill('#editor-container-rules', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });
    console.log('Quill Rules initialized:', quillRules);

    var quillPrizes = new Quill('#editor-container-prizes', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });
    console.log('Quill Prizes initialized:', quillPrizes);
});

</script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</body>
</html>