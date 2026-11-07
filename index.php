<?php

session_start();
require 'config_budget.php';

$alerts = $_SESSION['alerts']??[];
$active_form=$_SESSION['active_form']??'';
unset($_SESSION['alerts'], $_SESSION['active_form']);

$showLogin = isset($_GET['login']);

if (!isset($_SESSION['user_id'])) {
  $showLogin = isset($_GET['login']);
}

else {

  $user_id = $_SESSION['user_id'];
  

  try {
    $currentPeriod = date('Y-m');
    $selectedPeriod = $_GET['period'] ?? date('Y-m');
    $displayMonth = date('F Y', strtotime($selectedPeriod));


    $stmtBudget = $pdo->prepare("SELECT * FROM budgets WHERE user_id = :user_id AND period = :period LIMIT 1");
    $stmtBudget->execute([
      'user_id' => $user_id, 
      'period' => $selectedPeriod
    ]);

    $budget = $stmtBudget->fetch(PDO::FETCH_ASSOC);

    if (!$budget) {
      $balance = 0;
      $totalIncome = 0;
      $totalExpenses = 0;
      $_SESSION['alerts'][]=[
        'type'=>'info',
        'message'=>"No budget created for ($currentPeriod)."
      ];
    } else {

      $budget_id=$budget['id'];

      // Get total income
      $stmtIncome = $pdo->prepare("SELECT SUM(amount) FROM `income` WHERE user_id = :user_id AND budget_id = :budget_id");
      $stmtIncome->execute(['user_id' => $user_id, 'budget_id' => $budget_id]);
      $totalIncome = $stmtIncome->fetchColumn();
      $totalIncome = $totalIncome ? (float)$totalIncome : 0.00;

      // Get total expenses
      $stmtExpenses = $pdo->prepare("SELECT SUM(amount) FROM `expense-tracker` WHERE user_id = :user_id AND budget_id = :budget_id");
      $stmtExpenses->execute(['user_id' => $user_id, 'budget_id' => $budget_id]);
      $totalExpenses = $stmtExpenses->fetchColumn();
      $totalExpenses = $totalExpenses ? (float)$totalExpenses : 0.00;

      // Calculate balance
      $balance = $totalIncome - $totalExpenses;
    } 
      
  } catch (PDOException $e) {
    $alerts[] = [
      'type' => 'error',
      'message' => 'Database error: ' . $e->getMessage()
    ];
  }

  foreach (['firstname', 'surname', 'email'] as $key) {
    if (!empty($_SESSION[$key])) {
      $_SESSION[$key] = $_SESSION[$key];
    }
  }

  $firstname=$_SESSION['firstname'] ?? null;
  $surname=$_SESSION['surname'] ?? null;
  $email=$_SESSION['email'] ?? null;

  if ($firstname !== null) $_SESSION['firstname'] = $firstname;
  if ($surname !== null) $_SESSION['surname'] = $surname;
  if ($email !== null) $_SESSION['email'] = $email;

}

?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Home | Ofi</title>

    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>

    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

  </head>

  <body>

    <?php include __DIR__ . '/components/navbar.php'; ?>

    <section>
      <h1 class="welcome">Welcome, <?= $firstname ?? 'budgeter'?>!</h1>
    </section>

    <div class="intro">
      <?php if (!empty($budget_id)): ?>
        <p>Your balance for 
          <?php
            $dateObj = DateTime::createFromFormat('Y-m', $displayMonth);
            echo $dateObj ? $dateObj->format('F Y') : htmlspecialchars($displayMonth);
          ?> is: </p>
      <?php else: ?>
        <p>Your balance is: </p>
      <?php endif; ?>
    </div>

    <?php if (!empty($alerts)): ?>
      <div class="alert-box">
        <?php foreach($alerts as $alert): ?>
          <div class="alert <?= $alert['type']; ?>">
            <i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-x-circle'; ?>'></i>
            <span><?= htmlspecialchars($alert['message']) ?></span>
          </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="auth-modal <?= $active_form === 'register' ? 'show slide' : ($active_form === 'login' ? 'show' : ''); ?>">
      <button type="button" class="close-btn-modal">
        <i class='bx bxs-x-circle'></i> 
      </button>
      <div class="form-box login">
        <h2>
          Log In
        </h2>
        <form action="auth_process.php" method="POST">
          <div class="input-box">
            <input type="email" name="email" placeholder="Enter Email Address" required>
            <i class='bx bxs-envelope-alt'></i> 
          </div>
          <div class="input-box">
            <input type="password" name="password" placeholder="********" required>
            <i class='bx bxs-lock'></i> 
          </div>
          <button type="submit" name="login_btn" class="btn">
            Log In
          </button>
          <p>Don't have an account? <a href="#" class="register-link">Register Here</a></p>
        </form>
      </div>

      <div class="form-box register">
        <h2>
          Register
        </h2>
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
          <button type="submit" name="register_btn" class="btn">
            Register
          </button>
          <p>Already have an account? <a href="#" class="login-link">Log In Here</a></p>
        </form>
      </div>
    </div>

    <div class="container">
      <ul class="cards" id="expansion">
        <li class="income-card">
          <?php if (!empty($user_id)): ?>
            <a href="income.php" class="card-link" style="text-decoration:none;">
              <i class='bx bxs-currency-notes'></i>
            </a>
          <?php else: ?>
            <i class='bx bxs-currency-notes' style="pointer-events:none;"></i>
          <?php endif; ?>
          <span class="info">
            <h3><span>£</span>
              <span>
                <?php if (!empty($totalIncome)): ?>
                  <?= $totalIncome; ?>
                <?php else: ?>
                  0
                <?php endif; ?>
              </span></h3>
            <p>Total Income</p>
          </span>
        </li>
        <li class="balance-card" data-href="budget.php" tabindex="0" role="button" aria-label="Open recent transactions">
          <i class='bx bxs-piggy-bank'></i> 
          <span class="info">
            <h3><span>£</span><span>
              <?php if (!empty($balance)): ?>
                <?= $balance; ?>
              <?php else: ?>
                0
              <?php endif; ?>
            </span></h3>
            <p class="balance-text">Balance</p>
          </span>
        </li>
        <li class="expense-card">
          <?php if (!empty($user_id)): ?>
            <a href="expenses.php" class="card-link" style="text-decoration:none;">
              <i class='bx bxs-credit-card-alt'></i>
            </a>
          <?php else: ?>
            <i class='bx bxs-credit-card-alt' style="pointer-events:none;"></i>
          <?php endif; ?>
          <span class="info">
            <h3><span>£</span>
              <span>
                <?php if (!empty($totalExpenses)): ?>
                  <?= $totalExpenses; ?>
                <?php else: ?>
                  0
                <?php endif; ?>
              </span>
            </h3>
            <p>Total Spending</p>
          </span>
        </li>
      </ul>
    </div>


    <?php if (!empty($user_id)): ?>
      <div class="click-info">
        <p>Click your balance to see more information.</p>
      </div>
      <div class="clicked-info">
        <p>Click the income or spending icon to see more information or to start tracking.</p>
      </div>
    <?php else: ?>
      <div class="not-logged-info">
        <p style="font-size: 20px;color: var(--dark-green);font-weight: 600;margin-top: 2.5rem;text-align: center;opacity: 1;">
          Log in to start budgeting!
        </p>
      </div>
    <?php endif; ?>

    <script src="/budget-tracker/assets/js/script.js"></script>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const showLogin = <?= json_encode($showLogin) ?>;

        if (showLogin) {
          const loginModal = document.querySelector('.auth-modal');
          if (loginModal) {
            loginModal.classList.add('active');
          }
        }
      });
    </script>


  </body>
</html>