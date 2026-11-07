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
    <title>My Budgeting App | About</title>
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
        <h1>About My Budgeting App</h1>
        <p>My Budgeting App is a friendly budgeting app designed to help you see your money clearly, make smarter decisions, and build confidence with every month.</p>
      </div>

      <section class="content-grid">
        <article class="info-card">
          <h3>What My Budgeting App does</h3>
          <p>It brings your income, expenses, and monthly balance into one place so you can understand your financial habits in a simple, visual format.</p>
        </article>

        <article class="info-card">
          <h3>Why it exists</h3>
          <p>Budgeting should feel manageable, not stressful. My Budgeting App keeps the process simple, encouraging consistency rather than perfection.</p>
        </article>

        <article class="info-card">
          <h3>Where to go next</h3>
          <p>Explore <a href="how_to.php" class="inline-link">How To</a> for guidance, or <a href="contact.php" class="inline-link">Contact Us</a> if you want to get in touch.</p>
        </article>
      </section>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>