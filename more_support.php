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
    <title>My Budgeting App | More Support</title>
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

    <main class="content-page support-page">
      <div class="content-page-header">
        <h1>More support</h1>
      </div>

      <section class="article-stack">
        <article class="article-section">
          <h2>Benefits and income support</h2>
          <p>Many people miss out on benefits simply because they do not know they may qualify. In the UK, some of the most common support includes Universal Credit, Pension Credit, disability-related support, and help with childcare costs.</p>
          <p>This is often worth checking before making big spending decisions. A small amount of support can make a huge difference to rent, food, and day-to-day bills.</p>
          <p><a href="https://www.gov.uk/benefits-calculators" class="inline-link" target="_blank" rel="noopener noreferrer">Use the UK Government benefits calculator</a> to estimate what you may be entitled to.</p>
        </article>

        <article class="article-section">
          <h2>Cheaper rent and housing help</h2>
          <p>If rent is putting pressure on your budget, it can help to check whether you qualify for local housing support, council tax reductions, or more affordable housing options. Some people also save money by moving to a lower-cost area, sharing costs, or switching to a cheaper tenancy arrangement.</p>
          <ul>
            <li>Check your local council website for housing support.</li>
            <li>Ask whether you qualify for council tax reduction.</li>
            <li>Look at whether a cheaper property or room share would reduce pressure.</li>
          </ul>
        </article>

        <article class="article-section">
          <h2>Food and shopping discounts</h2>
          <p>Food costs can be reduced by combining supermarket loyalty schemes, weekly offers, and discount apps. It is often the smaller savings that add up across a month.</p>
          <ul>
            <li>Use supermarket points and loyalty cards.</li>
            <li>Shop around for the cheapest weekly basket rather than always buying the same brands.</li>
            <li>Look for reduced-price food near closing time and bulk buy only what you know you will use.</li>
          </ul>
        </article>

        <article class="article-section">
          <h2>Energy, phone, and regular bills</h2>
          <p>Utility bills can be reduced by switching providers, reviewing your direct debits, and checking if you qualify for support. Some households also get help with winter fuel, energy grants, or emergency support schemes.</p>
          <ul>
            <li>Compare gas and electricity suppliers.</li>
            <li>Check if your provider has a cheaper social tariff or hardship support.</li>
            <li>Review your mobile and broadband plan for cost-cutting options.</li>
          </ul>
        </article>

        <article class="article-section">
          <h2>Debt and payment support</h2>
          <p>If you are struggling with debt, contact your lender or provider early. Many organisations will work with you to arrange a more manageable plan before the situation gets worse.</p>
          <p>It is okay to ask for help. Waiting often makes the problem more stressful and more expensive.</p>
        </article>
      </section>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>
