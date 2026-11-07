<?php
session_start();
require 'config_budget.php';

$alerts = $_SESSION['alerts'] ?? [];
$active_form = $_SESSION['active_form'] ?? '';
unset($_SESSION['alerts'], $_SESSION['active_form']);

$pdo->exec("CREATE TABLE IF NOT EXISTS `budget_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `freelance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `benefits` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rent` decimal(10,2) NOT NULL DEFAULT 0.00,
  `groceries` decimal(10,2) NOT NULL DEFAULT 0.00,
  `transport` decimal(10,2) NOT NULL DEFAULT 0.00,
  `utilities` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insurance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `emergency` decimal(10,2) NOT NULL DEFAULT 0.00,
  `holiday` decimal(10,2) NOT NULL DEFAULT 0.00,
  `retirement` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_budget_plan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$defaultPlan = [
  'salary' => 2200.00,
  'freelance' => 320.00,
  'benefits' => 150.00,
  'rent' => 980.00,
  'groceries' => 380.00,
  'transport' => 180.00,
  'utilities' => 210.00,
  'insurance' => 120.00,
  'emergency' => 220.00,
  'holiday' => 180.00,
  'retirement' => 160.00,
];

$userPlan = $defaultPlan;

if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare('SELECT * FROM budget_plan WHERE user_id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $storedPlan = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($storedPlan) {
    foreach ($defaultPlan as $key => $value) {
      $userPlan[$key] = (float)($storedPlan[$key] ?? $value);
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_budget_plan'])) {
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please log in to update your budget plan.'];
    header('Location: index.php');
    exit();
  }

  $incoming = [
    'salary' => (float)($_POST['salary'] ?? 0),
    'freelance' => (float)($_POST['freelance'] ?? 0),
    'benefits' => (float)($_POST['benefits'] ?? 0),
    'rent' => (float)($_POST['rent'] ?? 0),
    'groceries' => (float)($_POST['groceries'] ?? 0),
    'transport' => (float)($_POST['transport'] ?? 0),
    'utilities' => (float)($_POST['utilities'] ?? 0),
    'insurance' => (float)($_POST['insurance'] ?? 0),
    'emergency' => (float)($_POST['emergency'] ?? 0),
    'holiday' => (float)($_POST['holiday'] ?? 0),
    'retirement' => (float)($_POST['retirement'] ?? 0),
  ];

  $upsert = $pdo->prepare('INSERT INTO budget_plan (user_id, salary, freelance, benefits, rent, groceries, transport, utilities, insurance, emergency, holiday, retirement)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      salary = VALUES(salary),
      freelance = VALUES(freelance),
      benefits = VALUES(benefits),
      rent = VALUES(rent),
      groceries = VALUES(groceries),
      transport = VALUES(transport),
      utilities = VALUES(utilities),
      insurance = VALUES(insurance),
      emergency = VALUES(emergency),
      holiday = VALUES(holiday),
      retirement = VALUES(retirement)');

  $upsert->execute([
    $_SESSION['user_id'],
    $incoming['salary'],
    $incoming['freelance'],
    $incoming['benefits'],
    $incoming['rent'],
    $incoming['groceries'],
    $incoming['transport'],
    $incoming['utilities'],
    $incoming['insurance'],
    $incoming['emergency'],
    $incoming['holiday'],
    $incoming['retirement'],
  ]);

  $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Budget plan updated successfully.'];
  header('Location: budget_plan.php');
  exit();
}

$editing = isset($_GET['edit']) && isset($_SESSION['user_id']);

$incomeTotal = $userPlan['salary'] + $userPlan['freelance'] + $userPlan['benefits'];
$expenseTotal = $userPlan['rent'] + $userPlan['groceries'] + $userPlan['transport'] + $userPlan['utilities'] + $userPlan['insurance'];
$savingsTotal = $userPlan['emergency'] + $userPlan['holiday'] + $userPlan['retirement'];

$makeChartGradient = function (array $segments, array $colors): string {
  $total = array_sum(array_map(fn ($segment) => (float)($segment['value'] ?? 0), $segments));

  if ($total <= 0) {
    return 'conic-gradient(var(--light-primary) 0 100%)';
  }

  $parts = [];
  $cursor = 0;

  foreach ($segments as $index => $segment) {
    $segmentValue = (float)($segment['value'] ?? 0);
    $start = $cursor;
    $end = $cursor + (($segmentValue / $total) * 100);
    $parts[] = sprintf('%s %s%% %s%%', $colors[$index % count($colors)], $start, $end);
    $cursor = $end;
  }

  return 'conic-gradient(' . implode(', ', $parts) . ')';
};

$incomeStreams = [
  ['label' => 'Salary', 'value' => $userPlan['salary'], 'color' => 'var(--primary)'],
  ['label' => 'Freelance', 'value' => $userPlan['freelance'], 'color' => 'var(--secondary)'],
  ['label' => 'Benefits', 'value' => $userPlan['benefits'], 'color' => 'var(--light-primary)'],
];

$expenseStreams = [
  ['label' => 'Rent', 'value' => $userPlan['rent'], 'color' => 'var(--light-dark)'],
  ['label' => 'Groceries', 'value' => $userPlan['groceries'], 'color' => 'var(--primary)'],
  ['label' => 'Transport', 'value' => $userPlan['transport'], 'color' => 'var(--secondary)'],
  ['label' => 'Utilities', 'value' => $userPlan['utilities'], 'color' => 'var(--light-primary)'],
  ['label' => 'Insurance', 'value' => $userPlan['insurance'], 'color' => 'var(--light-secondary)'],
];

$savingStreams = [
  ['label' => 'Emergency', 'value' => $userPlan['emergency'], 'color' => 'var(--primary)'],
  ['label' => 'Holiday', 'value' => $userPlan['holiday'], 'color' => 'var(--secondary)'],
  ['label' => 'Retirement', 'value' => $userPlan['retirement'], 'color' => 'var(--light-primary)'],
];

$incomeChartGradient = $makeChartGradient($incomeStreams, ['#B8336A', '#F9C8E0', '#945C4C']);
$expenseChartGradient = $makeChartGradient($expenseStreams, ['#B8336A', '#F9C8E0', '#945C4C', '#73956F', '#132A13']);
$savingsChartGradient = $makeChartGradient($savingStreams, ['#B8336A', '#F9C8E0', '#945C4C']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Budgeting App | My Budget Plan</title>
    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
  </head>
  <body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <?php if (!empty($alerts)): ?>
      <div class="alert-box">
        <?php foreach ($alerts as $alert): ?>
          <div class="alert <?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8'); ?>">
            <i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-x-circle'; ?>'></i>
            <span><?= htmlspecialchars($alert['message'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

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

    <main class="content-page budget-page">
      <div class="content-page-header budget-plan-header">
        <h1>My Budget Plan</h1>
      </div>

      <?php if ($editing && isset($_SESSION['user_id'])): ?>
        <section class="budget-plan-form-panel">
          <h2>Edit budget</h2>
          <form method="POST" class="budget-plan-form">
            <div class="budget-form-grid">
              <label>
                <span>Salary</span>
                <input type="number" name="salary" step="0.01" value="<?= htmlspecialchars((string)$userPlan['salary'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Freelance</span>
                <input type="number" name="freelance" step="0.01" value="<?= htmlspecialchars((string)$userPlan['freelance'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Benefits</span>
                <input type="number" name="benefits" step="0.01" value="<?= htmlspecialchars((string)$userPlan['benefits'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Rent</span>
                <input type="number" name="rent" step="0.01" value="<?= htmlspecialchars((string)$userPlan['rent'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Groceries</span>
                <input type="number" name="groceries" step="0.01" value="<?= htmlspecialchars((string)$userPlan['groceries'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Transport</span>
                <input type="number" name="transport" step="0.01" value="<?= htmlspecialchars((string)$userPlan['transport'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Utilities</span>
                <input type="number" name="utilities" step="0.01" value="<?= htmlspecialchars((string)$userPlan['utilities'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Insurance</span>
                <input type="number" name="insurance" step="0.01" value="<?= htmlspecialchars((string)$userPlan['insurance'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Emergency</span>
                <input type="number" name="emergency" step="0.01" value="<?= htmlspecialchars((string)$userPlan['emergency'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Holiday</span>
                <input type="number" name="holiday" step="0.01" value="<?= htmlspecialchars((string)$userPlan['holiday'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
              <label>
                <span>Retirement</span>
                <input type="number" name="retirement" step="0.01" value="<?= htmlspecialchars((string)$userPlan['retirement'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
            </div>

            <div class="budget-plan-actions">
              <button type="submit" name="save_budget_plan" value="1" class="primary-button">Save budget</button>
              <a href="budget_plan.php" class="secondary-button text-button">Cancel</a>
            </div>
          </form>
        </section>
      <?php else: ?>
        <section class="budget-plan-grid">
          <article class="budget-plan-card">
            <div class="budget-plan-chart income-chart" style="background: <?= htmlspecialchars($incomeChartGradient, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Income budget chart"></div>
            <div class="budget-plan-card-content">
              <h3>Income</h3>
              <ul>
                <?php foreach ($incomeStreams as $stream): ?>
                  <li><span><?= htmlspecialchars($stream['label'], ENT_QUOTES, 'UTF-8'); ?></span><strong>£<?= number_format((float)$stream['value'], 2); ?></strong></li>
                <?php endforeach; ?>
              </ul>
              <div class="budget-card-total">
                <span>Total</span>
                <strong>£<?= number_format((float)$incomeTotal, 2); ?></strong>
              </div>
            </div>
          </article>

          <article class="budget-plan-card">
            <div class="budget-plan-chart expense-chart" style="background: <?= htmlspecialchars($expenseChartGradient, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Expenses budget chart"></div>
            <div class="budget-plan-card-content">
              <h3>Expenses</h3>
              <ul>
                <?php foreach ($expenseStreams as $stream): ?>
                  <li><span><?= htmlspecialchars($stream['label'], ENT_QUOTES, 'UTF-8'); ?></span><strong>£<?= number_format((float)$stream['value'], 2); ?></strong></li>
                <?php endforeach; ?>
              </ul>
              <div class="budget-card-total">
                <span>Total</span>
                <strong>£<?= number_format((float)$expenseTotal, 2); ?></strong>
              </div>
            </div>
          </article>

          <article class="budget-plan-card">
            <div class="budget-plan-chart savings-chart" style="background: <?= htmlspecialchars($savingsChartGradient, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Savings budget chart"></div>
            <div class="budget-plan-card-content">
              <h3>Savings</h3>
              <ul>
                <?php foreach ($savingStreams as $stream): ?>
                  <li><span><?= htmlspecialchars($stream['label'], ENT_QUOTES, 'UTF-8'); ?></span><strong>£<?= number_format((float)$stream['value'], 2); ?></strong></li>
                <?php endforeach; ?>
              </ul>
              <div class="budget-card-total">
                <span>Total</span>
                <strong>£<?= number_format((float)$savingsTotal, 2); ?></strong>
              </div>
            </div>
          </article>
        </section>

        <div class="budget-plan-actions">
          <a href="budget_plan.php?edit=1" class="primary-button">Edit budget</a>
        </div>
      <?php endif; ?>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>
