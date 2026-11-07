<?php
session_start();

$firstname = $_SESSION['firstname'] ?? null;
$active_form = $_SESSION['active_form'] ?? '';
unset($_SESSION['active_form']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Budgeting App | How To</title>
    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
  </head>
  <body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="auth-modal <?= $active_form === 'register' ? 'show slide' : ($active_form === 'login' ? 'show' : ''); ?>">
      <button type="button" class="close-btn-modal">
        <i class='bx bxs-x-circle'></i>
      </button>
      <div class="form-box login">
        <h2>Log In</h2>
        <form action="auth_process.php" method="POST">
          <div class="input-box">
            <input type="email" name="email" placeholder="Enter Email Address" required>
            <i class='bx bxs-envelope-alt'></i>
          </div>
          <div class="input-box">
            <input type="password" name="password" placeholder="********" required>
            <i class='bx bxs-lock'></i>
          </div>
          <button type="submit" name="login_btn" class="btn">Log In</button>
          <p>Don't have an account? <a href="#" class="register-link">Register Here</a></p>
        </form>
      </div>

      <div class="form-box register">
        <h2>Register</h2>
        <form action="auth_process.php" method="POST">
          <div class="input-box">
            <input type="text" name="firstname" placeholder="Enter First Name" required>
            <i class='bx bxs-user'></i>
          </div>
          <div class="input-box">
            <input type="text" name="surname" placeholder="Enter Surname" required>
            <i class='bx bxs-user'></i>
          </div>
          <div class="input-box">
            <input type="email" name="email" placeholder="Enter Email Address" required>
            <i class='bx bxs-envelope-alt'></i>
          </div>
          <div class="input-box">
            <input type="password" name="password" placeholder="********" required>
            <i class='bx bxs-lock'></i>
          </div>
          <button type="submit" name="register_btn" class="btn">Register</button>
          <p>Already have an account? <a href="#" class="login-link">Log In Here</a></p>
        </form>
      </div>
    </div>

    <main class="content-page">
      <div class="content-page-header">
        <h1>How To</h1>
      </div>

      <section class="how-to">

        <div class="instruction">
          <h3>Review your balance</h3>
          <img src="/budget-tracker/assets/images/entrance-ss.png" alt="Entrance Page">
          <p>Once you are logged in, you can see your balance and access your income and spending cards from the home page.</p>
        </div>

        <div class="instruction">
          <h3>Add income or expenses</h3>
          <img src="/budget-tracker/assets/images/income-pg-ss.png" alt="Income Page">
          <img src="/budget-tracker/assets/images/new-entry-ss.png" alt="New Expenses Page">
          <p>Use the income and expense pages to add new entries, update details, or delete anything you no longer need.</p>
        </div>

        <div class="instruction">
          <h3>Check your recent transactions</h3>
          <img src="/budget-tracker/assets/images/filter-ss.png" alt="Budget Page">
          <p>View recent transactions and filter for efficiency.</p>
        </div>

        <div class="instruction">
          <h3>Change the theme</h3>
          <img src="/budget-tracker/assets/images/theme-ss.png" alt="Theme Page">
          <p>Customise your website experience by changing the theme.</p>
        </div>

        <div class="instruction">
          <h3>Get support</h3>
          <img src="/budget-tracker/assets/images/tips-ss.png" alt="Tips Page">
          <p>Need help saving? We've got you. On our website, you can also find some tips and support for decreasing your spending or increasing income.</p>
        </div>
      </section>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>
