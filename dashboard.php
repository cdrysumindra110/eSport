<?php
// Start session
session_start();

// Include the database connection file
include('config.php');
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
// Handle file uploads
if (isset($_FILES['coverPhotoFile']) && $_FILES['coverPhotoFile']['error'] === UPLOAD_ERR_OK) {
    $coverPhotoTmpName = $_FILES['coverPhotoFile']['tmp_name'];
    $coverPhotoName = basename($_FILES['coverPhotoFile']['name']);
    $coverPhotoPath = 'uploads/' . $coverPhotoName;
    move_uploaded_file($coverPhotoTmpName, $coverPhotoPath);
}

if (isset($_FILES['profilePicFile']) && $_FILES['profilePicFile']['error'] === UPLOAD_ERR_OK) {
    $profilePicTmpName = $_FILES['profilePicFile']['tmp_name'];
    $profilePicName = basename($_FILES['profilePicFile']['name']);
    $profilePicPath = 'uploads/' . $profilePicName;
    move_uploaded_file($profilePicTmpName, $profilePicPath);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // You should hash the password in a real-world application
    $role = $_POST['role'];
    $uname = filter_var($_POST['uname'], FILTER_SANITIZE_STRING);
    $dobMonth = filter_var($_POST['dob-month'], FILTER_SANITIZE_NUMBER_INT);
    $dobDay = filter_var($_POST['dob-day'], FILTER_SANITIZE_NUMBER_INT);
    $dobYear = filter_var($_POST['dob-year'], FILTER_SANITIZE_NUMBER_INT);
    $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);

    // Example query to update user profile
    $sql = "UPDATE users SET email=?, password=?, role=?, uname=?, dob_month=?, dob_day=?, dob_year=?, country=?, city=?, cover_photo=?, profile_pic=? WHERE id=?";

    $stmt = $conn->prepare($sql);

    // Handle cover photo and profile picture if uploaded
    $coverPhotoPath = isset($coverPhotoPath) ? $coverPhotoPath : NULL;
    $profilePicPath = isset($profilePicPath) ? $profilePicPath : NULL;

    // Assuming you have user ID available as a session variable
    $userId = $_SESSION['user_id'];

    $stmt->bind_param(
        'ssssiiiiisii',
        $email,
        $password,
        $role,
        $uname,
        $dobMonth,
        $dobDay,
        $dobYear,
        $country,
        $city,
        $coverPhotoPath,
        $profilePicPath,
        $userId
    );

    if ($stmt->execute()) {
        echo "Profile updated successfully!";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
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
    <link rel="stylesheet" href="css/template-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   
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
          <a href="index.html" class="logo">
            <!-- Logo White Version -->
            <img class="logo-white" src="img/logo.png" alt="">
            <!-- Logo Dark Version -->
            <img class="logo-dark" src="img/logo.png" alt="">
          </a>
        </div>
        <div class="top-nav s-12 l-10">
          <ul class="right chevron">
            <li><a href="index.html">Home</a></li>
           <li><a href="#">Tournaments</a>
              <ul>
                <li><a href="#">Upcoming Tournaments</a>
                  <ul class="game_container">
                    <a href="#"><li class="ga_me"> <img src="img/logo/pubg_logo.png" alt="Pubg Logo" class="ga_me-icon">Pubg Mobile</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/ff_logo.png" alt="FF Logo" class="ga_me-icon">Free Fire</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/cs_logo.png" alt="COD Logo" class="ga_me-icon">COD Mobile</li></a>
                    <a href="tour_reg.html" class="all-games"><li class="all-games-text">All Tournaments<i class="fas fa-arrow-right"></i></li></a>
                  </ul>
              </li>
                <li><a>Ongoing Tournaments</a></li>
                </ul>
            </li>
            <li><a href="games.html">Games</a></li>
            <li><a href="our-services.html">Our Services</a></li>
            <!-- <li><a href="our-history.html">Our History</a></li> -->
            <!-- <li><a href="contact.html">Contact</a></li> -->
             
            <li><a href="organize.html">Organize</a></li>
            <li><a href="about-us.html">About</a></li>
            <li><a href="#"><i class="fas fa-user"></i></a>
              <ul>
                <li><a href="signin.html">Signin</a></li>
                <li><a href="signup.html">Signup</a></li>
                <li><a href="index.html">Logout</a></li>
              </ul>
            </li>
            <li>
              <a href="#">
              <label class="plane-switch">
              <input type="checkbox">
              <div>
                  <div>
                      <svg viewBox="0 0 13 13">
                          <path d="M1.55989957,5.41666667 L5.51582215,5.41666667 L4.47015462,0.108333333 L4.47015462,0.108333333 C4.47015462,0.0634601974 4.49708054,0.0249592654 4.5354546,0.00851337035 L4.57707145,0 L5.36229752,0 C5.43359776,0 5.50087375,0.028779451 5.55026392,0.0782711996 L5.59317877,0.134368264 L7.13659662,2.81558333 L8.29565964,2.81666667 C8.53185377,2.81666667 8.72332694,3.01067661 8.72332694,3.25 C8.72332694,3.48932339 8.53185377,3.68333333 8.29565964,3.68333333 L7.63589819,3.68225 L8.63450135,5.41666667 L11.9308317,5.41666667 C12.5213171,5.41666667 13,5.90169152 13,6.5 C13,7.09830848 12.5213171,7.58333333 11.9308317,7.58333333 L8.63450135,7.58333333 L7.63589819,9.31666667 L8.29565964,9.31666667 C8.53185377,9.31666667 8.72332694,9.51067661 8.72332694,9.75 C8.72332694,9.98932339 8.53185377,10.1833333 8.29565964,10.1833333 L7.13659662,10.1833333 L5.59317877,12.8656317 C5.55725264,12.9280353 5.49882018,12.9724157 5.43174295,12.9907056 L5.36229752,13 L4.57707145,13 L4.55610333,12.9978962 C4.51267695,12.9890959 4.48069792,12.9547924 4.47230803,12.9134397 L4.47223088,12.8704208 L5.51582215,7.58333333 L1.55989957,7.58333333 L0.891288881,8.55114605 C0.853775374,8.60544678 0.798421006,8.64327676 0.73629202,8.65879796 L0.672314689,8.66666667 L0.106844414,8.66666667 L0.0715243949,8.66058466 L0.0715243949,8.66058466 C0.0297243066,8.6457608 0.00275502199,8.60729104 0,8.5651586 L0.00593007386,8.52254537 L0.580855011,6.85813984 C0.64492547,6.67265611 0.6577034,6.47392717 0.619193545,6.28316421 L0.580694768,6.14191703 L0.00601851064,4.48064746 C0.00203480725,4.4691314 0,4.45701613 0,4.44481314 C0,4.39994001 0.0269259152,4.36143908 0.0652999725,4.34499318 L0.106916826,4.33647981 L0.672546853,4.33647981 C0.737865848,4.33647981 0.80011301,4.36066329 0.848265401,4.40322477 L0.89131128,4.45169723 L1.55989957,5.41666667 Z" fill="currentColor"></path>
                      </svg>
                  </div>
                  <span class="street-middle"></span>
                  <span class="cloud"></span>
                  <span class="cloud two"></span>
              </div>
              </label>
             </a>
          </li>
          </li>
        </div>
      </nav>
    </header>
    

    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              User Profile
            </h1>
          
          </div>
   
        </header>
  
     
      <div class="popup-message" id="popup-message"></div>
            <!-- Dashboard Container  -->
            <form id="profile-form" action="update_profile.php" method="post" enctype="multipart/form-data">
    <div class="profile-container">
        <!-- Cover Photo and Profile Picture Section -->
        <div class="cover-photo-container">
            <div class="cover-photo">
                <input id="coverPhotoFile" name="coverPhotoFile" type="file" onchange="loadCoverPhoto(event)" class="file-input" />
                <label for="coverPhotoFile" class="cover-photo-label">
                    <span class="icon-wrapper">
                        <i class="fas fa-camera"></i>
                    </span>
                    <span>Change Cover</span>
                </label>
                <img id="coverPhoto" name="coverPhoto" src="img/neon.png" alt="Cover Photo" class="cover-photo-img" />
                <div class="cover-overlay"></div>
            </div>
        </div>
        <div class="profile-pic">
            <input id="profilePicFile" name="profilePicFile" type="file" onchange="loadProfilePic(event)" class="file-input" />
            <label class="-label" for="profilePicFile">
                <span class="glyphicon glyphicon-camera"></span>
                <span>Change Profile</span>
            </label>
            <img src="img/team-02.jpg" id="profilePic" name="profilePic" class="profile-pic-img" />
        </div>
    </div>

    <!-- Account Settings Container -->
    <div class="unique-container">
        <div class="acc-setting">
            <h2 class="unique-header">Account Settings</h2>

            <div class="unique-input-field">
                <label for="email" class="unique-label">Change Email:</label>
                <div class="unique-input-button-container">
                    <input type="email" id="email" name="email" class="unique-input" placeholder="user@gmail.com">
                    <button id="change-email" class="unique-button">Change email</button>
                </div>
            </div>

            <div class="unique-input-field">
                <label for="password" class="unique-label">Change Password:</label>
                <div class="unique-input-button-container">
                    <input type="password" id="password" name="password" class="unique-input" placeholder="********">
                    <button id="change-password" class="unique-button">Change password</button>
                </div>
            </div>
        </div>

        <!-- Role Selection Container -->
        <h2 class="unique-header">Role Selection</h2>

        <div class="unique-input-field">
            <label class="unique-label">Select Role:</label>
            <div class="radio-group">
                <input type="radio" id="role-player" name="role" value="player" class="unique-radio">
                <label for="role-player" class="unique-radio-label">Player</label>
                <input type="radio" id="role-organizer" name="role" value="organizer" class="unique-radio">
                <label for="role-organizer" class="unique-radio-label">Organizer</label>
            </div>
        </div>

        <!-- Personal Information Container -->
        <h2 class="unique-header">Personal Information</h2>

        <div class="unique-input-field">
            <label for="uname" class="unique-label">User Name</label>
            <input type="text" id="uname" name="uname" class="unique-input" placeholder="@BloodeHancxy">
        </div>

        <div class="unique-input-field">
            <label for="dob" class="unique-label">Date Of Birth</label>
            <div class="dob-row">
                <select id="dob-month" name="dob-month" class="unique-select"></select>
                <select id="dob-day" name="dob-day" class="unique-select"></select>
                <select id="dob-year" name="dob-year" class="unique-select"></select>
            </div>
        </div>

        <h2 class="unique-header">Location</h2>

        <div class="unique-input-field">
            <label for="country" class="unique-label">Country</label>
            <select id="country" name="country" class="unique-select"></select>
        </div>

        <div class="unique-info">
            <i class="unique-info-icon"></i> You can change your country once every 6 months.
        </div>

        <div class="unique-input-field">
            <label for="city" class="unique-label">City</label>
            <input type="text" id="city" name="city" class="unique-input" placeholder="City">
        </div>

        <button type="button" class="unique-button">CANCEL</button>
        <button type="submit" class="unique-button">SAVE CHANGES</button>
    </div>
</form>
            
        
    <!-- Section 9 -->
    <section class="section background-white">
      <!-- red full width arrow object -->
      <img class="arrow-object" src="img/object-red.svg" alt="">
    </section>
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
                <img src="../assets/img/ESports.jpg" alt="Esports" class="image">
                <img src="../assets/img/amd.jpg" alt="AMD" class="image">
                <img src="../assets/img/redbull.jpg" alt="Red Bull" class="image">
                <img src="../assets/img/unicef.jpg" alt="UNICEF" class="image">
                <img src="../assets/img/tencent.jpg" alt="Tencent" class="image">
                <img src="../assets/img/KoHire.png" alt="KoHire" class="image">
                <img src="../assets/img/masterportfolio-banner-dark.png" alt="masterportfolio-banner-dark" class="image">
                <img src="../assets/img/Empyre.png" alt="Empyre" class="image">
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
            <a class="right text-size-12 text-primary-hover" href="#" title="Esports Website">Design and coded by <br> Team &infin; </a>
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

  function loadCoverPhoto(event) {
  const coverPhoto = document.getElementById('coverPhoto');
  coverPhoto.src = URL.createObjectURL(event.target.files[0]);
}

function loadProfilePic(event) {
  const profilePic = document.getElementById('profilePic');
  profilePic.src = URL.createObjectURL(event.target.files[0]);
}

// ----------------------Dob Javascript ----------------------------
document.addEventListener('DOMContentLoaded', function() {
    const months = [
        "January", "February", "March", "April", "May", "June", "July",
        "August", "September", "October", "November", "December"
    ];
    const days = Array.from({ length: 31 }, (_, i) => i + 1);
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 100 }, (_, i) => currentYear - i);

    const monthSelect = document.getElementById('dob-month');
    const daySelect = document.getElementById('dob-day');
    const yearSelect = document.getElementById('dob-year');

    // Populate months
    months.forEach(month => {
        const option = document.createElement('option');
        option.value = month;
        option.textContent = month;
        monthSelect.appendChild(option);
    });

    // Populate days
    days.forEach(day => {
        const option = document.createElement('option');
        option.value = day;
        option.textContent = day;
        daySelect.appendChild(option);
    });

    // Populate years
    years.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    });
});

// ----------------------------------JS for Countries ----------------------------
document.addEventListener('DOMContentLoaded', function() {
    const countries = [
        "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda",
        "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain",
        "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia",
        "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso",
        "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic",
        "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic of the",
        "Congo, Republic of the", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czechia", "Denmark",
        "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador",
        "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland",
        "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada",
        "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary",
        "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica",
        "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South",
        "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia",
        "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia",
        "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico",
        "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique",
        "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua",
        "Niger", "Nigeria", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Panama",
        "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar",
        "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia",
        "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe",
        "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore",
        "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Sudan",
        "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan",
        "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago",
        "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates",
        "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City",
        "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
    ];

    const countrySelect = document.getElementById('country');

    // Populate countries
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country;
        option.textContent = country;
        countrySelect.appendChild(option);
    });
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

</script>

  </body>
</html>
