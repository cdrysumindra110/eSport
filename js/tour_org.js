
document.addEventListener('DOMContentLoaded', () => {
  // Game cards
  const gameCards = document.querySelectorAll('.game-card.available');

  gameCards.forEach(card => {
      card.addEventListener('click', (event) => {
          // Get the selected game name
          const selectedGame = event.currentTarget.querySelector('h3').textContent;

          // Store the selected game in localStorage
          localStorage.setItem('selectedGame', selectedGame);

          // Redirect to the create_tour.php page
          window.location.href = 'create_tour.php';
      });
  });
});

document.getElementById('back-arrow').addEventListener('click', function() {
  window.location.href = 'organize.php';
});


document.addEventListener('DOMContentLoaded', function() {
  // Select all available game cards
  document.querySelectorAll('.game-card.available').forEach(card => {
    card.addEventListener('click', function() {
      // Get the game name from the clicked card
      const gameName = this.querySelector('h3').textContent;
      
      // Store the game name in localStorage
      localStorage.setItem('selectedGame', gameName);
      
      // Redirect to the create_tour.php page
      window.location.href = 'create_tour.php';
    });
  });
});


document.addEventListener('DOMContentLoaded', function() {
  // Retrieve the game name from localStorage
  const selectedGame = localStorage.getItem('selectedGame');
  
  // Populate the input field if the game name exists
  if (selectedGame) {
    document.getElementById('selected_game').value = selectedGame;
    
    // Optionally, clear the item from localStorage after use
    localStorage.removeItem('selectedGame');
  }
});



$(document).ready(function(){

var current_fs, next_fs, previous_fs; //fieldsets
var opacity;
var current = 1;
var steps = $("fieldset").length;

setProgressBar(current);

$(".next").click(function(){

current_fs = $(this).parent();
next_fs = $(this).parent().next();

//Add Class Active
$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

//show the next fieldset
next_fs.show();
//hide the current fieldset with style
current_fs.animate({opacity: 0}, {
step: function(now) {
// for making fielset appear animation
opacity = 1 - now;

current_fs.css({
'display': 'none',
'position': 'relative'
});
next_fs.css({'opacity': opacity});
},
duration: 500
});
setProgressBar(++current);
});

$(".previous").click(function(){

current_fs = $(this).parent();
previous_fs = $(this).parent().prev();

//Remove class active
$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

//show the previous fieldset
previous_fs.show();

//hide the current fieldset with style
current_fs.animate({opacity: 0}, {
step: function(now) {
// for making fielset appear animation
opacity = 1 - now;

current_fs.css({
'display': 'none',
'position': 'relative'
});
previous_fs.css({'opacity': opacity});
},
duration: 500
});
setProgressBar(--current);
});

function setProgressBar(curStep){
var percent = parseFloat(100 / steps) * curStep;
percent = percent.toFixed();
$(".progress-bar")
.css("width",percent+"%")
}

$(".submit").click(function(){
return false;
})

});

// Image preview
function showPreview(event) {
      if (event.target.files.length > 0) {
          var src = URL.createObjectURL(event.target.files[0]);
          var preview = document.getElementById("bannerimg-preview");
          preview.src = src;
          preview.style.display = "block";
      }
  }
  function formatText(command) {
    document.execCommand(command, false, null);
}

function copyContentToInput() {
        const editorContent = document.getElementById('editor').innerHTML;
        document.getElementById('hiddenInput').value = editorContent;
    }
// Accordian js
let accordDT = jQuery(".accordion dt");
accordDT.on("click", function () {
  $(this).toggleClass("expand");
  // jQuery(this).next('dd').slideDown(300).siblings('dd').slideUp(500);// only single toggle
  $(this).next("dd").slideToggle(300); //best for responsive toggle
});


document.getElementById('social-media').addEventListener('change', function() {
    var selectedValue = this.value;
    var inputField = document.getElementById('social-media-input');
    
    if (selectedValue) {
        inputField.style.display = 'block'; // Show the input field
    } else {
        inputField.style.display = 'none'; // Hide the input field
    }
});

// --------------------------------------------------Add stream Js+++++++++++++++++++++++++++++++++++++++++++++++++
document.addEventListener('DOMContentLoaded', function() {
  // Function to show the popup alert
  function showPopup(message) {
    const popup = document.getElementById('popup-alert');
    const popupMessage = document.getElementById('popup-message');
    popupMessage.textContent = message;
    popup.classList.remove('hidden');

     // Automatically hide the popup after 3 seconds
     setTimeout(function() {
      popup.classList.add('hidden');
    }, 3000);

    document.getElementById('close-popup').addEventListener('click', function() {
      popup.classList.add('hidden');
    });
  }

  document.getElementById('add-new-stream').addEventListener('click', function(event) {
    event.preventDefault();

    const newStreamForm = document.querySelector('.new-stream-form').cloneNode(true);
    newStreamForm.classList.remove('hidden');

    // Clear input values and update names
    newStreamForm.querySelectorAll('input').forEach(input => {
      input.value = '';
      input.name = input.name + '_clone_' + Date.now();
    });

    newStreamForm.querySelectorAll('select').forEach(select => {
      select.selectedIndex = 0;
      select.name = select.name + '_clone_' + Date.now();
    });

    // Add event listener to the remove button for this cloned form
    newStreamForm.querySelector('.remove-btn').addEventListener('click', function(event) {
      event.preventDefault();
      newStreamForm.remove();

      if (document.querySelectorAll('.new-stream-form').length === 0) {
        document.getElementById('add-new-stream').disabled = false;
      }
    });

    // Add event listener for save button
    newStreamForm.querySelector('.save-btn').addEventListener('click', function(event) {
      event.preventDefault();
      const providerSelect = newStreamForm.querySelector('select');
      if (!providerSelect || !providerSelect.value) {
        showPopup('Please select a provider.');
        return;
      }
      console.log('Stream saved');
    });

    document.querySelector('.new-stream-container').insertBefore(newStreamForm, document.getElementById('add-new-stream'));
  });

  // Initialize remove and save functionality for existing forms
  function initFormControls() {
    document.querySelectorAll('.remove-btn').forEach(button => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        this.closest('.new-stream-form').remove();

        if (document.querySelectorAll('.new-stream-form').length === 0) {
          document.getElementById('add-new-stream').disabled = false;
        }
      });
    });

    document.querySelectorAll('.save-btn').forEach(button => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        const providerSelect = this.closest('.new-stream-form').querySelector('select');
        if (!providerSelect || !providerSelect.value) {
          showPopup('Please select a provider.');
          return;
        }
        console.log('Stream saved');
      });
    });
  }

  initFormControls();
});


// -----------------------------Bracket Setting---------------------------------------

document.addEventListener('DOMContentLoaded', function() {
  const matchTypeSelect = document.getElementById('match-type');
  const soloContainer = document.getElementById('solo-container');
  const duoContainer = document.getElementById('duo-container');
  const squadContainer = document.getElementById('squad-container');

  // Function to show the correct container based on the selected match type
  function updateMatchContainer() {
    // Hide all containers initially
    soloContainer.style.display = 'none';
    duoContainer.style.display = 'none';
    squadContainer.style.display = 'none';

    // Show the selected container
    switch (matchTypeSelect.value) {
      case 'solo':
        soloContainer.style.display = 'block';
        break;
      case 'duo':
        duoContainer.style.display = 'block';
        break;
      case 'squad':
        squadContainer.style.display = 'block';
        break;
      default:
        // Optionally handle the case where no valid option is selected
        break;
    }
  }

  // Initial call to set the default container based on the current selection
  updateMatchContainer();

  // Add event listener to update the container when the selection changes
  matchTypeSelect.addEventListener('change', updateMatchContainer);
});

