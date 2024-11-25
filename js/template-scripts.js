jQuery(document).ready(function($) {  
  // Owl Carousel                     
  var owl = $('.carousel-default');
  owl.owlCarousel({
    nav: true,
    dots: true,
    items: 1,
    loop: true,
    navText: ["&#xe605","&#xe606"],
    autoplay: true,
    autoplayTimeout: 5000
  });

  // Owl Carousel - Content Blocks  
  var owl = $('.carousel-blocks');
  owl.owlCarousel({
    nav: false,
    dots: true,
    items: 6,
    responsive: {
      0: {
        items: 2
      },
      481: {
        items: 3
      },
      769: {
        items: 6
      }
    },
    loop: true,
    navText: ["&#xe605","&#xe606"],
    autoplay: true,
    autoplayTimeout: 5000
  });
  
  // Owl Carousel - Content 3 Blocks
  var owl = $('.carousel-3-blocks');
  owl.owlCarousel({
    nav: true,
    dots: true,
    items: 3,
    responsive: {
      0: {
        items: 1
      },
      481: {
        items: 2
      },
      769: {
        items: 3
      }
    },
    loop: true,
    navText: ["&#xe605","&#xe606"],
    autoplay: true,
    autoplayTimeout: 5000
  });  
  
  var owl = $('.carousel-fade-transition');
  owl.owlCarousel({
    nav: true,
    dots: true,
    items: 1,
    loop: true,
    navText: ["&#xe605","&#xe606"],
    autoplay: true, 
    animateOut: 'fadeOut',
    autoplayTimeout: 5000
  });
  
  // skillbar
  $('.skillbar').bind('inview', function (event, visible) {
    if (visible) {  
      $('.skillbar').each(function(){
  	    $(this).find('.skillbar-bar').animate({
  	   	  width:$(this).attr('data-percent')
  	    },3000);
      });
       
    } 
  });
  
  
  // Sticky Nav Bar
  $(window).scroll(function() {
    if ($(this).scrollTop() > 20){  
        $('.sticky').addClass("fixed");
    }
    else{
        $('.sticky').removeClass("fixed");
    }
  });
  
  
  // Custom scripts
  $('.carousel-center').owlCarousel({
    center: true,
    items:2,
    loop:true,
    nav: true,
    dots: false,
    margin:30,
    navText: ["&#xe605","&#xe606"],
    autoplay: true,
    autoplayTimeout: 5000,
    responsive: {
      0: {
        items: 1
      },
      481: {
        items: 2
      },
      769: {
        items: 2
      }
    },
  });
  
});


/* Our Registered clients Our Numbers */
function updateCounter(element, countTo, duration) {
  let count = 0;
  let step = countTo / duration;
  let timer = setInterval(() => {
    count += step;
    element.textContent = Math.round(count);

    // Stop the timer when the counter reaches the target number
    if (Math.round(count) >= countTo) {
      clearInterval(timer);
      element.textContent = countTo;
    }
  }, 5);
}

// Get all the timer elements
const timers = document.querySelectorAll('.timer');

// Loop through each timer element and start the counter
timers.forEach(timer => {
  updateCounter(timer, parseInt(timer.textContent), 1000);
});


const profileForm = document.getElementById('profileForm');
const successMessage = document.getElementById('successMessage');
const coverPhotoInput = document.getElementById('coverPhoto');
const avatarInput = document.getElementById('avatar');
const coverPreview = document.getElementById('coverPreview');
const avatarPreview = document.getElementById('avatarPreview');

// Form validation logic
profileForm.addEventListener('submit', function(event) {
    event.preventDefault();
    let isValid = true;
    const errorMessages = document.querySelectorAll('.error-msg');
    errorMessages.forEach(error => error.style.display = 'none'); // Reset errors

    // Check required fields
    const formElements = profileForm.elements;
    for (let i = 0; i < formElements.length; i++) {
        const field = formElements[i];
        const errorMessage = field.parentElement.querySelector('.error-msg');
        
        if (field.type !== 'submit' && field.value.trim() === '') {
            errorMessage.textContent = `${field.name.charAt(0).toUpperCase() + field.name.slice(1)} is required`;
            errorMessage.style.display = 'block';
            isValid = false;
        }
    }

    // Handle form submission if valid
    if (isValid) {
        profileForm.reset(); // Clear form
        successMessage.style.display = 'block'; // Show success message
    }
});

// Preview cover photo
coverPhotoInput.addEventListener('change', function() {
    const file = coverPhotoInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            coverPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Preview avatar
avatarInput.addEventListener('change', function() {
    const file = avatarInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});



// ==========================================News Section CSS =====================================================

document.addEventListener('DOMContentLoaded', function () {
  const tabButtons = document.querySelectorAll('.tab-button');
  const tabs = document.querySelectorAll('.tab');

  function switchTab(targetTab) {
      // Remove 'active' class from all buttons and tabs
      tabButtons.forEach(btn => btn.classList.remove('active'));
      tabs.forEach(tab => tab.classList.remove('active'));

      // Add 'active' class to the clicked button and the corresponding tab
      const targetTabContent = document.querySelector(`.tab[data-tab="${targetTab}"]`);
      document.querySelector(`.tab-button[data-tab="${targetTab}"]`).classList.add('active');
      targetTabContent.classList.add('active');
  }

  // Add click event to each tab button
  tabButtons.forEach(button => {
      button.addEventListener('click', () => {
          const targetTab = button.dataset.tab; // Get the tab target from data-tab
          switchTab(targetTab);
      });
  });

  // Initialize with the first tab active
  switchTab('latest'); // Set 'latest' as the initial tab
});



