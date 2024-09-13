<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();


?>


<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Esports Website</title>
    <link rel="stylesheet" href="./css/components.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/responsee.css">
    <link rel="stylesheet" href="./owl-carousel/owl.carousel.css">
    <link rel="stylesheet" href="./owl-carousel/owl.theme.css">
    <!-- CUSTOM STYLE -->      
    <link rel="stylesheet" href="./css/template-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

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
            <li><a href="#"><i class="fas fa-user"></i></a>
              <ul>
                <li><a href="signin.php">Signin</a></li>
                <li><a href="signup.php">Signup</a></li>
                <li><a href="index.php">Logout</a></li>
              </ul>
            </li>
            <!-- <li>
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
          </li> -->
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
              Our Services
            </h1>
          
          </div>
  
        </header>
        
      </article>  

    </main>

        <!-- Section 1 -->
        <section class="full-width background-white">
          <div class="s-12 m-12 l-4">
            <!-- Change the background image -->  
            <div style="background-image: url(img/our_service.jpg);" class="contact-image" ></div>
          </div>
          <div class="s-12 m-12 l-4 text-center">
            <div class="padding-2x">
              <i class="icon-sli-location-pin text-primary text-size-30 center"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">Company Address</h2>                
              <p>
                 Pradarshani Marga,<br>
                 Kathmandu, Nepal
              </p> 
              
              <i class="icon-sli-envelope text-primary text-size-30 center margin-top-20"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">E-mail</h2>                
              <a class="text-primary-hover" href="mailto:contact@infiknight.com">contact@infiknight.com</a><section>
              <a class="text-primary-hover" href="mailto:office@infiknight.com">office@infiknight.com</a>
              
              <i class="icon-sli-earphones-alt text-primary text-size-30 center margin-top-20"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">Phone Numbers</h2>                
              <p>
                +977 66666666<br>
                +977 77777777<br>
                +977 88888888
              </p> 
            </div>
          </div>
          <div class="s-12 m-12 l-4">
            <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1459734.5702753505!2d16.91089086619977!3d48.577103681657675!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ssk!2ssk!4v1457640551761" width="100%" height="600" frameborder="0" style="border:0"></iframe> -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3532.4854170982326!2d85.31721907496203!3d27.70229502571519!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19a99fb5b605%3A0xe39f9cd2361902f1!2sRatna%20Rajyalaxmi%20Campus!5e0!3m2!1sen!2snp!4v1723286331549!5m2!1sen!2snp" width="100%" height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </section>
        
        <!-- Section 3 -->
        <section class="section background-image" style="background-image:url(img/contact_bg.jpg)">
          <div class="s-12 m-12 l-4 center">
            <h3 class="text-white text-size-30 margin-bottom-40 text-center">Contact Form</h3>

            <form name="contactForm" class="customform text-white" method="post" enctype="multipart/form-data">
              <div class="line">
                <div class="margin">
                  <div class="s-12 m-12 l-6">
                    <input name="email" class="required email" placeholder="Your e-mail" title="Your e-mail" type="text" />
                  </div>
                  <div class="s-12 m-12 l-6">
                    <input name="name" class="name" placeholder="Your name" title="Your name" type="text" />
                  </div>
                </div>
              </div>            
                              
              <div class="line">       
                <div class="s-12">
                  <input name="subject" class="required subject" placeholder="Subject" title="Subject" type="text" />
                  <p class="subject-error form-error">Please enter your subject.</p>
                </div>
                <div class="s-12">
                  <textarea name="message" class="required message" placeholder="Your message" rows="3"></textarea>
                  <p class="message-error form-error">Please enter your message.</p>
                </div>
                <div class="s-12"><button class="button border-radius text-white background-primary" type="submit">Submit Button</button></div>
              </div>    
            </form>
          </div>  
                
        </section>

     <!-- Section Services -->
     <section class="section">      
      <div class="line">
        <div class="margin2x">
           
           <!-- Image 1 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/team-management.jpg"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Team Management</h3>
                <p>
                  Our team management services help you build and maintain a strong, 
                  competitive team. We offer support in player recruitment, training 
                  schedules, and performance analysis. Our goal is to help your team 
                  reach its full potential by providing the resources and guidance 
                  needed to succeed in the competitive esports landscape.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 2 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/event-planning.jpg"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Event Planning</h3>
                <p>
                  From small-scale events to large-scale tournaments, our event planning
                  services ensure every detail is covered. We manage venue selection, 
                  logistics, and on-site operations to create a memorable and smooth experience
                   for participants and spectators. Our experienced team coordinates every aspect
                    of your event, so you can focus on the action.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 3 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/sponsored.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Sponsorship</h3>
                <p>
                  We connect your esports events with potential sponsors to enhance their visibility 
                  and financial support. Our sponsorship services include identifying suitable partners, 
                  negotiating terms, and managing sponsorship agreements. We aim to create mutually 
                  beneficial relationships that provide value to both sponsors and your event.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 4 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/media-coverage.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Media Coverage</h3>
                <p>
                  Effective media coverage is crucial for promoting esports events. We offer comprehensive 
                  media services, including press releases, social media management, and live streaming. 
                  goal is to maximize your event’s exposure and reach a broader audience, ensuring that
                  your event garners the attention it deserves.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>

           <!-- Image 5 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/custom-service.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Custom Services</h3>
                <p>
                  We understand that every esports event is unique, and we offer custom services tailored 
                  to your specific needs. Whether you require bespoke event features, specialized tournament 
                  formats, or unique promotional strategies, our team is here to design and deliver solutions 
                  that align with your vision and objectives.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>

           <!-- Image 6 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/tournaments.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Tournaments</h3>
                <p>
                  We specialize in organizing and executing esports tournaments that bring together players and 
                  fans from around the world. Our tournaments are meticulously planned to ensure a seamless experience, 
                  from initial registration to the final matches. We handle all aspects, including scheduling, match management, 
                  and prize distribution, ensuring a professional and exciting event for everyone involved.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
        </div>                                                                                                
      </div>     
    </section>
    <!-- Section 9 -->
    <section>
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
          <a class="right text-size-12 text-primary-hover" href="#" title="Esports Website">Design and coded by <br> Team &infin; </a>
        </div>
      </div>  
    </section>
  </footer>
  <script type="text/javascript" src="js/responsee.js"></script>
  <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
  <script type="text/javascript" src="js/template-scripts.js"></script> 
  
</body>
</html>