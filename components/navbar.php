<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$selectedPeriod = $selectedPeriod ?? null;
$logoHref = $logoHref ?? (
    $selectedPeriod ? 'index.php?period=' . urlencode($selectedPeriod) : 'index.php'
);
$homeHref = $homeHref ?? (
    $selectedPeriod ? 'index.php?period=' . urlencode($selectedPeriod) : 'index.php'
);
$firstname = $firstname ?? ($_SESSION['firstname'] ?? null);
?>

<header>
  <a href="<?= htmlspecialchars($logoHref, ENT_QUOTES, 'UTF-8'); ?>" class="logo">
    <img src="/budget-tracker/assets/images/ego-no-bg.png" alt="My Budgeting App Logo">
  </a>

  <nav>
    <?php if (!empty($firstname)): ?>
      <div class="nav-item">
        <button type="button" class="nav-item-toggle">My Budget <i class='bx bx-chevron-down chevron'></i></button>
        <div class="dropdown">
          <a href="income.php">My Income</a>
          <a href="expenses.php">My Expenses</a>
          <a href="budget.php">My Budget</a>
          <a href="budget_plan.php">My Budget Plan</a>
        </div>
      </div>
    <?php endif; ?>

    <div class="nav-item">
      <button type="button" class="nav-item-toggle">Advice <i class='bx bx-chevron-down chevron'></i></button>
      <div class="dropdown">
        <a href="tips.php">Tips</a>
        <a href="talk_to_advisor.php">Talk to an Advisor</a>
        <a href="more_support.php">More Support</a>
      </div>
    </div>

    <div class="nav-item">
      <button type="button" class="nav-item-toggle">Help <i class='bx bx-chevron-down chevron'></i></button>
      <div class="dropdown">
        <a href="how_to.php">How To</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact Us</a>
      </div>
    </div>
  </nav>

  <div class="user-auth">
    <div class="profile-box">
      <?php if (!empty($firstname)): ?>
        <div class="profile-circle">
          <?= strtoupper($firstname[0]); ?>
        </div>
        <div class="dropdown">
          <a href="account.php">My Account</a>
          <a href="themes.php">Themes</a>
          <a href="logout.php">Log Out</a>
        </div>
      <?php else: ?>
        <button type="button" class="login-btn-modal">Log In</button>
      <?php endif; ?>
    </div>
  </div>
</header>
