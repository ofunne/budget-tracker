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

if (!isset($_GET['id'])) {
    header('Location: income.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$id = (int) $_GET['id'];

$recentEntries = $pdo->prepare("SELECT id, category, title, amount, created_at FROM income WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentEntries->execute([$user_id]);
$recentEntries = $recentEntries->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT et.*, b.period FROM income et JOIN budgets b ON et.budget_id = b.id WHERE et.id = ? AND et.user_id = ?");
$stmt->execute([$id, $user_id]);
$income = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$income) {
    echo 'Income record not found.';
    exit();
}

$splitTitle = explode(' - ', $income['title'], 2);
$titleValue = $splitTitle[0] ?? $income['title'];
$descriptionValue = $splitTitle[1] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $category = trim($_POST['category'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $amount = trim($_POST['amount'] ?? '');
  $selectedDate = $_POST['date'] ?? date('Y-m-d');
  $period = date('Y-m', strtotime($selectedDate));

  $combinedTitle = $title;
  if ($description !== '') {
      $combinedTitle = $title !== '' ? $title . ' - ' . $description : $description;
  }

  if (empty($category) || empty($amount) || !is_numeric($amount) || $amount <= 0) {
      $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Category and a positive amount are required.'];
      header('Location: income.php');
      exit();
  }

  $amount = number_format((float)$amount, 2, '.', '');

  $stmtBudget = $pdo->prepare("SELECT id FROM budgets WHERE user_id = ? AND period = ? LIMIT 1");
  $stmtBudget->execute([$user_id, $period]);
  $budget = $stmtBudget->fetch(PDO::FETCH_ASSOC);

  if (!$budget) {
      $stmtInsertBudget = $pdo->prepare("INSERT INTO budgets (user_id, period) VALUES (?, ?)");
      $stmtInsertBudget->execute([$user_id, $period]);
      $budget_id = $pdo->lastInsertId();
  } else {
      $budget_id = $budget['id'];
  }

  $updateStmt = $pdo->prepare("UPDATE income SET category = ?, title = ?, amount = ?, created_at = ?, budget_id = ? WHERE id = ? AND user_id = ?");
  $updateStmt->execute([$category, $combinedTitle, $amount, $selectedDate . ' 00:00:00', $budget_id, $id, $user_id]);

  $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Income updated.'];
  header('Location: income.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Income</title>
    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
  </head>
  <body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="transaction-page-shell">
      <div class="transaction-page-header">
        <a href="income.php" class="back-link" aria-label="Back">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 class="transaction-page-title">Income #<?= htmlspecialchars((string)$income['id'], ENT_QUOTES, 'UTF-8'); ?></h1>
      </div>

      <div class="transaction-form-card">
        <form class="transaction-form" method="POST">
          <div class="form-field">
            <label for="recent-entry">Recent entries</label>
            <select id="recent-entry" name="recent_entry">
              <option value="">Select a recent entry</option>
              <?php foreach ($recentEntries as $entry):
                $recentParts = explode(' - ', $entry['title'], 2);
                $recentTitle = $recentParts[0] ?? $entry['title'];
                $recentDescription = $recentParts[1] ?? '';
              ?>
                <option
                  value="<?= (int)$entry['id']; ?>"
                  data-category="<?= htmlspecialchars($entry['category'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-title="<?= htmlspecialchars($recentTitle, ENT_QUOTES, 'UTF-8'); ?>"
                  data-description="<?= htmlspecialchars($recentDescription, ENT_QUOTES, 'UTF-8'); ?>"
                  data-amount="<?= htmlspecialchars((string)$entry['amount'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-date="<?= htmlspecialchars(date('Y-m-d', strtotime($entry['created_at'])), ENT_QUOTES, 'UTF-8'); ?>"
                >
                  <?= htmlspecialchars($recentTitle . ' • £' . $entry['amount'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-field">
            <label for="category">Category</label>
            <select id="category" name="category" required>
              <option value="">Choose category</option>
              <option value="Salary" <?= $income['category'] === 'Salary' ? 'selected' : '' ?>>Salary</option>
              <option value="Freelancing" <?= $income['category'] === 'Freelancing' ? 'selected' : '' ?>>Freelancing</option>
              <option value="Socials" <?= $income['category'] === 'Socials' ? 'selected' : '' ?>>Socials</option>
              <option value="Investment" <?= $income['category'] === 'Investment' ? 'selected' : '' ?>>Investment</option>
              <option value="Other" <?= $income['category'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <div class="form-field">
            <label for="title">Title</label>
            <input id="title" type="text" name="title" value="<?= htmlspecialchars($titleValue, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="form-field">
            <label for="amount">Amount</label>
            <input id="amount" type="number" step="0.01" min="0.01" name="amount" value="<?= htmlspecialchars((string)$income['amount'], ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="form-field">
            <label for="date">Date</label>
            <input id="date" type="date" name="date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($income['created_at'])), ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="form-field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Optional notes"><?= htmlspecialchars($descriptionValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="transaction-submit">Save changes</button>
            <a href="delete_income.php?id=<?= (int)$income['id']; ?>" class="transaction-delete">Delete income</a>
          </div>
        </form>
      </div>
    </main>
    <script src="/budget-tracker/assets/js/script.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const recentEntry = document.getElementById('recent-entry');
        if (!recentEntry) return;

        recentEntry.addEventListener('change', function () {
          const selected = this.options[this.selectedIndex];
          if (!selected || !selected.value) return;

          const category = selected.dataset.category || '';
          const title = selected.dataset.title || '';
          const description = selected.dataset.description || '';
          const amount = selected.dataset.amount || '';
          const date = selected.dataset.date || '';

          const categoryField = document.getElementById('category');
          const titleField = document.getElementById('title');
          const descriptionField = document.getElementById('description');
          const amountField = document.getElementById('amount');
          const dateField = document.getElementById('date');

          if (categoryField) categoryField.value = category;
          if (titleField) titleField.value = title;
          if (descriptionField) descriptionField.value = description;
          if (amountField) amountField.value = amount;
          if (dateField) dateField.value = date;
        });
      });
    </script>
  </body>
</html>
