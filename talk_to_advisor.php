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
    <title>My Budgeting App | Talk to an Advisor</title>
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
        <h1>Talk to an advisor</h1>
        <p>Need practical, non-judgemental money support? A quick conversation with a trained advisor can help you focus, plan, and feel less stuck.</p>
      </div>

      <div class="advisor-layout">
        <aside class="advisor-info">
          <h3>How an advisor can help</h3>
          <p>Advisors can support you with practical money decisions, clear budgeting priorities, and short-term planning when life feels overwhelming.</p>
          <ul>
            <li>Break down monthly spending and income.</li>
            <li>Help you prioritise essentials before debts or extras.</li>
            <li>Suggest realistic next steps for debt, savings or rent pressure.</li>
            <li>Point you towards relevant local or government support.</li>
          </ul>
          <p style="margin-top: 18px;">You do not need to wait until things are “bad enough.” If you want a clearer plan, it is okay to ask for support early.</p>
        </aside>

        <section class="advisor-form-card">
          <h3>Request support</h3>
          <form class="advisor-form" action="contact.php" method="POST">
            <div class="advisor-form-row">
              <label>
                Full name
                <input type="text" name="name" placeholder="Your name" required>
              </label>

              <label>
                Email address
                <input type="email" name="email" placeholder="Your email" required>
              </label>
            </div>

            <div class="advisor-form-row">
              <label>
                Postcode
                <input type="text" name="postcode" placeholder="Your postcode">
              </label>

              <label>
                Support needed
                <select name="support_type">
                  <option value="">Select an option</option>
                  <option>Budgeting help</option>
                  <option>Debt advice</option>
                  <option>Rent or housing pressure</option>
                  <option>Benefits check</option>
                  <option>General advice</option>
                </select>
              </label>
            </div>

            <label>
              What would you like help with?
              <textarea name="message" placeholder="Tell us a little about your situation" required></textarea>
            </label>

            <button type="submit">Send request</button>
          </form>
        </section>
      </div>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>
