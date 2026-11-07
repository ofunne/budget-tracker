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
    <title>My Budgeting App | Tips</title>
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

    <main class="content-page tips-page">
      <div class="content-page-header">
        <h1>Tips</h1>
      </div>

      <section class="article-stack">
        <article class="article-section">
          <h2>1. Start with what is real, not what you wish you could do</h2>
          <p>Many people struggle with budgeting because they plan around an ideal month instead of an actual one. If your income is irregular, budget for the lower end of what you expect. If your spending is higher than you want, make your plan honest before you try to fix it.</p>
          <p>A realistic budget is not a failure. It is the foundation of stability.</p>
        </article>

        <article class="article-section">
          <h2>2. Separate “needs” from “wants” without guilt</h2>
          <p>Not every expense is a bad decision. But separate essentials from flexible spending so you can see where your money has to go first. Rent, food, transport, debt payments, energy bills, and childcare usually come before things that are nice to have.</p>
          <p>That does not mean you cannot enjoy yourself. It means your essentials should not be pushed into survival mode every month.</p>
        </article>

        <article class="article-section">
          <h2>3. Give paying yourself a place in the plan</h2>
          <p>Too many people try to save only after the rest of the month is “done.” In reality, savings should be treated like a bill. Even a small amount each month makes a difference.</p>
          <p>Even £20 or £30 a week is progress if it helps you build a cushion, pay off debt, or prepare for emergencies.</p>
        </article>

        <article class="article-section">
          <h2>4. Your brain is often the biggest spending problem</h2>
          <p>Behaviour and money are closely linked. Emotional spending, stress, and decision fatigue can quietly push spending up. If you are tired, rushed, or worried, the brain tends to choose immediate comfort instead of long-term planning.</p>
          <p>That is why a simple rule like “wait 24 hours before a non-essential purchase” can be more powerful than a complicated budget spreadsheet. It reduces impulse decisions before they become habits.</p>
        </article>

        <article class="article-section">
          <h2>5. Review your spending before it becomes expensive</h2>
          <p>Small leakages add up. Daily coffees, convenience food, delivery fees, subscriptions, and repeated impulse purchases can eat away at a budget without feeling dramatic.</p>
          <p>Set a weekly review window. It only needs to take ten minutes. Check what you spent, where you overshot, and what you can adjust next week.</p>
        </article>

        <article class="article-section">
          <h2>6. Build a buffer, not a perfect system</h2>
          <p>A good budget leaves space for life. Car repair. Broken phone. Late utility bill. A birthday gift. If your plan has no breathing room, it is likely to break under pressure.</p>
          <p>Start tiny: a small emergency buffer, a little more room in essentials, or a tiny weekly saving target. Stability usually comes from consistency, not from having everything perfectly calculated.</p>
        </article>

        <article class="article-section">
          <h2>7. Keep your goals visible</h2>
          <p>People are more likely to keep going when they can see the reason behind the budgeting. Whether the goal is debt reduction, rent stability, travel, or simply less financial stress, write it down and revisit it often.</p>
          <p>The point is not to be perfect. It is to make your money support the life you want instead of constantly catching up.</p>
        </article>

        <article class="article-section">
          <h2>8. Progress beats guilt</h2>
          <p>Money habits are not built in one dramatic reset. They are built by repeatable decisions. A rough week does not erase a good month. An imperfect budget does not mean you are failing.</p>
          <p>What matters is that you keep going, learn from the pattern, and keep adjusting toward a calmer financial life.</p>
        </article>
      </section>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>
