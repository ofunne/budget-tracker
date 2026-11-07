const authModal = document.querySelector(".auth-modal");
const loginLink = document.querySelector(".login-link");
const registerLink = document.querySelector(".register-link");

if (registerLink && authModal) registerLink.addEventListener("click", () => authModal.classList.add("slide"));
if (loginLink && authModal) loginLink.addEventListener("click", () => authModal.classList.remove("slide"));

const loginBtnModal = document.querySelector(".login-btn-modal");
const closeBtnModal = document.querySelector(".close-btn-modal");

if (loginBtnModal && authModal) loginBtnModal.addEventListener("click", () => {
  authModal.classList.toggle("show")
});

if (closeBtnModal && authModal) closeBtnModal.addEventListener("click", () => authModal.classList.remove("show", "slide"));

const profileBox = document.querySelector(".profile-box");
const profileCircle = document.querySelector(".profile-circle");
const navItems = document.querySelectorAll(".nav-item");

const closeAllMenus = () => {
  navItems.forEach((item) => item.classList.remove("show"));
  if (profileBox) profileBox.classList.remove("show");
};

if (profileCircle && profileBox) profileCircle.addEventListener("click", (event) => {
  event.preventDefault();
  event.stopPropagation();

  const shouldOpen = !profileBox.classList.contains("show");
  closeAllMenus();

  if (shouldOpen) {
    profileBox.classList.add("show");
  }
});

navItems.forEach((navItem) => {
  const toggle = navItem.querySelector(".nav-item-toggle");
  if (!toggle) return;

  toggle.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    const shouldOpen = !navItem.classList.contains("show");
    closeAllMenus();

    if (shouldOpen) navItem.classList.add("show");
  });
});

document.addEventListener("click", (event) => {
  if (!event.target.closest(".nav-item") && !event.target.closest(".profile-box")) {
    closeAllMenus();
  }
});

const alertBox = document.querySelector(".alert-box");

if (alertBox) {
  setTimeout(() => alertBox.classList.add("show"), 50);

  setTimeout(() => {
    alertBox.classList.remove("show");
    setTimeout(() => alertBox.remove(), 1000);
  }, 3000)
}

const balanceText = document.querySelector(".balance-text");

const cards = document.querySelector(".cards");
const cardContainer = document.querySelector(".container");
const balanceCard = document.querySelector(".balance-card");
const clickInfo = document.querySelector(".click-info");
const clickedInfo = document.querySelector(".clicked-info");
const notLoggedInfo = document.querySelector(".not-logged-info");

//const expansion = document.querySelector("#expansion");

if (balanceCard) {
  const toggleCards = () => {
    if (!cards) return;

    const isExpanded = cards.classList.contains("expanded");

    if (isExpanded) {
      window.location.href = "budget.php";
      return;
    }

    cards.classList.add("expanded");
    if (clickInfo) clickInfo.classList.add("expanded");
    if (clickedInfo) clickedInfo.classList.add("expanded");
  };

  balanceCard.addEventListener("click", toggleCards);
  balanceCard.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      toggleCards();
    }
  });
}
