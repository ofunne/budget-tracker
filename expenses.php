<?php
session_start();
require 'config_budget.php';

if (!isset($_SESSION['user_id'])) {
  $_SESSION['alerts'][] = [
    'type' => 'error',
    'message' => 'Please log in.'
  ];
  header('Location: index.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$firstname = $_SESSION['firstname'] ?? null;
$surname = $_SESSION['surname'] ?? null;
$email = $_SESSION['email'] ?? null;
$alerts = $_SESSION['alerts'] ?? [];
$active_form = $_SESSION['active_form'] ?? '';

unset($_SESSION['alerts'], $_SESSION['active_form']);

if ($firstname !== null) $_SESSION['firstname'] = $firstname;
if ($surname !== null) $_SESSION['surname'] = $surname;
if ($email !== null) $_SESSION['email'] = $email;

$sort = $_GET['sort'] ?? 'date_desc';
$categoryFilter = $_GET['category'] ?? 'all';
$contains = trim((string)($_GET['contains'] ?? ''));
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$minDate = $_GET['min_date'] ?? '';
$maxDate = $_GET['max_date'] ?? '';

$sql = "SELECT id, category, title, amount, created_at FROM `expense-tracker` WHERE user_id = :user_id";
$params = ['user_id' => $user_id];

if ($categoryFilter !== 'all') {
  $sql .= " AND category = :category";
  $params['category'] = $categoryFilter;
}

if ($contains !== '') {
  $sql .= " AND LOWER(title) LIKE :contains";
  $params['contains'] = '%' . strtolower($contains) . '%';
}

if ($minPrice !== '') {
  $sql .= " AND amount >= :min_price";
  $params['min_price'] = (float)$minPrice;
}

if ($maxPrice !== '') {
  $sql .= " AND amount <= :max_price";
  $params['max_price'] = (float)$maxPrice;
}

if ($minDate !== '') {
  $sql .= " AND DATE(created_at) >= :min_date";
  $params['min_date'] = $minDate;
}

if ($maxDate !== '') {
  $sql .= " AND DATE(created_at) <= :max_date";
  $params['max_date'] = $maxDate;
}

switch ($sort) {
  case 'date_asc':
    $sql .= " ORDER BY created_at ASC, id ASC";
    break;
  case 'amount_desc':
    $sql .= " ORDER BY amount DESC, created_at DESC";
    break;
  case 'amount_asc':
    $sql .= " ORDER BY amount ASC, created_at DESC";
    break;
  case 'date_desc':
  default:
    $sql .= " ORDER BY created_at DESC, id DESC";
    break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chartSql = "SELECT category, SUM(amount) AS total FROM `expense-tracker` WHERE user_id = :user_id";
$chartParams = ['user_id' => $user_id];

if ($categoryFilter !== 'all') {
  $chartSql .= " AND category = :category";
  $chartParams['category'] = $categoryFilter;
}

if ($contains !== '') {
  $chartSql .= " AND LOWER(title) LIKE :contains";
  $chartParams['contains'] = '%' . strtolower($contains) . '%';
}

if ($minPrice !== '') {
  $chartSql .= " AND amount >= :min_price";
  $chartParams['min_price'] = (float)$minPrice;
}

if ($maxPrice !== '') {
  $chartSql .= " AND amount <= :max_price";
  $chartParams['max_price'] = (float)$maxPrice;
}

if ($minDate !== '') {
  $chartSql .= " AND DATE(created_at) >= :min_date";
  $chartParams['min_date'] = $minDate;
}

if ($maxDate !== '') {
  $chartSql .= " AND DATE(created_at) <= :max_date";
  $chartParams['max_date'] = $maxDate;
}

$chartSql .= " GROUP BY category ORDER BY total DESC";
$stmtChart = $pdo->prepare($chartSql);
$stmtChart->execute($chartParams);
$chartRows = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$amounts = [];
foreach ($chartRows as $row) {
  $categories[] = $row['category'];
  $amounts[] = (float)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Expenses | Ofi</title>
    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <main class="page-shell">
      <h2 class="page-title">My Expenses</h2>

      <form class="filter-panel" method="GET">
        <div class="filter-row">
          <label class="field">
            <select name="sort">
              <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Newest first</option>
              <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Oldest first</option>
              <option value="amount_desc" <?= $sort === 'amount_desc' ? 'selected' : '' ?>>Highest amount</option>
              <option value="amount_asc" <?= $sort === 'amount_asc' ? 'selected' : '' ?>>Lowest amount</option>
            </select>
          </label>

          <label class="field">
            <select name="category">
              <option value="all" <?= $categoryFilter === 'all' ? 'selected' : '' ?>>All categories</option>
              <?php foreach (array_unique(array_column($entries, 'category')) as $category): ?>
                <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label class="field field-wide">
            <input type="text" name="contains" value="<?= htmlspecialchars($contains, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search keyword">
          </label>

          <label class="field small-field">
            <input type="number" step="0.01" name="min_price" value="<?= htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Min £">
          </label>

          <label class="field small-field">
            <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Max £">
          </label>

          <label class="field small-field">
            <input type="date" name="min_date" value="<?= htmlspecialchars($minDate, ENT_QUOTES, 'UTF-8'); ?>" placeholder="From date">
          </label>

          <label class="field small-field">
            <input type="date" name="max_date" value="<?= htmlspecialchars($maxDate, ENT_QUOTES, 'UTF-8'); ?>" placeholder="To date">
          </label>
        </div>
        <div class="filter-actions">
          <button type="submit" class="btn ghost-btn">Apply</button>
          <a href="expenses.php" class="reset-link">Reset</a>
        </div>
      </form>

      <section class="records-layout">
        <div class="chart-card">
          <canvas id="expenseChart"></canvas>
        </div>

        <div class="record-panel">
          <div class="record-panel-header">
            <h3>Expense Details</h3>
          </div>

          <div class="record-list">
            <?php if (!empty($entries)): ?>
              <?php foreach ($entries as $expense): ?>
                <a href="edit_expenses.php?id=<?= (int)$expense['id']; ?>" class="record-item-link">
                  <div class="record-item">
                    <div class="record-main">
                      <strong><?= htmlspecialchars($expense['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <div class="record-meta">
                      <span class="date-text"><?= htmlspecialchars(date('d/m/Y', strtotime($expense['created_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="record-amount expense">-£<?= number_format((float)$expense['amount'], 2); ?></div>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="empty-state">No expenses match your filters yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <div class="bottom-actions">
        <a href="add_expense.php" class="btn ghost-btn">Add expense</a>
      </div>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>

    <script>
      const expenseCategories = <?= json_encode($categories); ?>;
      const expenseAmounts = <?= json_encode($amounts); ?>;

      const expenseCtx = document.getElementById('expenseChart');
      if (expenseCtx) {
        const expensePalette = ['#B8336A', '#F9C8E0', '#945C4C', '#73956F', '#132A13', '#A3145E', '#31572C', '#BCA0BC', '#DD6E42', '#C1DFF0', '#88CCF1', '#F0D2D1', '#823200'];

        new Chart(expenseCtx, {
          type: 'pie',
          data: {
            labels: expenseCategories.length ? expenseCategories : ['No data'],
            datasets: [{
              data: expenseAmounts.length ? expenseAmounts : [1],
              backgroundColor: expensePalette
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: (context) => `${context.label}: £${context.parsed.toFixed(2)}`
                }
              }
            }
          }
        });
      }
    </script>
  </body>
</html>
