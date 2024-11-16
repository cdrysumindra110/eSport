const html = document.documentElement;
const body = document.body;
const menuLinks = document.querySelectorAll(".admin-menu a");
const collapseBtn = document.querySelector(".admin-menu .collapse-btn");
const toggleMobileMenu = document.querySelector(".toggle-mob-menu");
const switchInput = document.querySelector(".switch input");
const switchLabel = document.querySelector(".switch label");
const switchLabelText = switchLabel.querySelector("span:last-child");
const collapsedClass = "collapsed";
const lightModeClass = "light-mode";

/*TOGGLE HEADER STATE*/
collapseBtn.addEventListener("click", function () {
  body.classList.toggle(collapsedClass);
  this.getAttribute("aria-expanded") == "true"
    ? this.setAttribute("aria-expanded", "false")
    : this.setAttribute("aria-expanded", "true");
  this.getAttribute("aria-label") == "collapse menu"
    ? this.setAttribute("aria-label", "expand menu")
    : this.setAttribute("aria-label", "collapse menu");
});

/*TOGGLE MOBILE MENU*/
toggleMobileMenu.addEventListener("click", function () {
  body.classList.toggle("mob-menu-opened");
  this.getAttribute("aria-expanded") == "true"
    ? this.setAttribute("aria-expanded", "false")
    : this.setAttribute("aria-expanded", "true");
  this.getAttribute("aria-label") == "open menu"
    ? this.setAttribute("aria-label", "close menu")
    : this.setAttribute("aria-label", "open menu");
});

/*SHOW TOOLTIP ON MENU LINK HOVER*/
for (const link of menuLinks) {
  link.addEventListener("mouseenter", function () {
    if (
      body.classList.contains(collapsedClass) &&
      window.matchMedia("(min-width: 768px)").matches
    ) {
      const tooltip = this.querySelector("span").textContent;
      this.setAttribute("title", tooltip);
    } else {
      this.removeAttribute("title");
    }
  });
}

/*TOGGLE LIGHT/DARK MODE*/
if (localStorage.getItem("dark-mode") === "false") {
  html.classList.add(lightModeClass);
  switchInput.checked = false;
  switchLabelText.textContent = "Light";
}

switchInput.addEventListener("input", function () {
  html.classList.toggle(lightModeClass);
  if (html.classList.contains(lightModeClass)) {
    switchLabelText.textContent = "Light";
    localStorage.setItem("dark-mode", "false");
  } else {
    switchLabelText.textContent = "Dark";
    localStorage.setItem("dark-mode", "true");
  }
});

// Function to handle section display
// Function to hide all sections
function hideAllSections() {
  const sections = document.querySelectorAll('.page-content section');
  sections.forEach(section => {
    section.style.display = 'none';
  });
}

// Function to show a specific section by ID
function showSection(id) {
  hideAllSections(); // Hide all sections first
  const section = document.getElementById(id);
  if (section) {
    section.style.display = 'block'; // Show the selected section
  }
}

// Add event listeners to navbar links
document.getElementById('users-link').addEventListener('click', function(event) {
  event.preventDefault();
  showSection('users-section');
});

document.getElementById('tournament-link').addEventListener('click', function(event) {
  event.preventDefault();
  showSection('tournament');
});

document.getElementById('registration-link').addEventListener('click', function(event) {
  event.preventDefault();
  showSection('registration');
});

document.getElementById('account-settings-link').addEventListener('click', function(event) {
  event.preventDefault();
  showSection('account-settings');
});

document.getElementById('analytics-link').addEventListener('click', function(event) {
  event.preventDefault();
  showSection('analytics');
});

// Function to show the popup and display user details
function showPopup(id, fullName, email, createdAt, updatedAt, uname, country, city, role, coverPhoto, profilePic, dob) {
  document.getElementById("popup-text").innerHTML = `  
      <strong>ID:</strong> ${id} <br>
      <strong>Full Name:</strong> ${fullName} <br>
      <strong>Email:</strong> ${email} <br>
      <strong>Created At:</strong> ${createdAt} <br>
      <strong>Updated At:</strong> ${updatedAt} <br>
      <strong>Username:</strong> ${uname} <br>
      <strong>Country:</strong> ${country} <br>
      <strong>City:</strong> ${city} <br>
      <strong>Role:</strong> ${role} <br>
      <strong>Cover Photo:</strong> <img src="${coverPhoto}" alt="Cover Photo" width="100px"> <br>
      <strong>Profile Picture:</strong> <img src="${profilePic}" alt="Profile Picture" width="100px"> <br>
      <strong>Date of Birth:</strong> ${dob} <br>
  `;
  // Display the popup
  document.getElementById("popup").style.display = "flex";
}

// Function to close the popup
function closePopup() {
  // Hide the popup
  document.getElementById("popup").style.display = "none";
}

// Display the first section (e.g., users-section) when the page loads
window.onload = function() {
  showSection('users-section'); // Show the first section on page load
};
