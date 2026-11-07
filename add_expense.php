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
$nextId = (int) ($pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM `expense-tracker`")->fetchColumn() ?: 1);
$recentEntries = $pdo->prepare("SELECT id, category, title, amount, created_at FROM `expense-tracker` WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentEntries->execute([$user_id]);
$recentEntries = $recentEntries->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $selectedDate = $_POST['date'] ?? date('Y-m-d');
    $currentPeriod = date('Y-m', strtotime($selectedDate));

    $combinedTitle = $title;
    if ($description !== '') {
        $combinedTitle = $title !== '' ? $title . ' - ' . $description : $description;
    }

    if (empty($category) || empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Category and a positive amount are required.'
        ];
        header('Location: expenses.php');
        exit();
    }

    $amount = number_format((float)$amount, 2, '.', '');

    try {
        $stmtBudget = $pdo->prepare("SELECT id FROM budgets WHERE user_id = :user_id AND period = :period LIMIT 1");
        $stmtBudget->execute([
            'user_id' => $user_id,
            'period' => $currentPeriod
        ]);
        $budget = $stmtBudget->fetch(PDO::FETCH_ASSOC);

        if (!$budget) {
            $stmtCreate = $pdo->prepare("INSERT INTO budgets (user_id, period) VALUES (:user_id, :period)");
            $stmtCreate->execute([
                'user_id' => $user_id,
                'period' => $currentPeriod
            ]);
            $budget_id = $pdo->lastInsertId();
        } else {
            $budget_id = $budget['id'];
        }

        $stmt = $pdo->prepare("INSERT INTO `expense-tracker` (user_id, category, title, amount, created_at, budget_id) VALUES (:user_id, :category, :title, :amount, :created_at, :budget_id)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':category' => $category,
            ':title' => $combinedTitle,
            ':amount' => $amount,
            ':created_at' => $selectedDate . ' 00:00:00',
            ':budget_id' => $budget_id
        ]);

        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Expense added successfully.'
        ];
    } catch (PDOException $e) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }

    header('Location: expenses.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Expense</title>
    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
  </head>
  <body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="transaction-page-shell">
      <div class="transaction-page-header">
        <a href="expenses.php" class="back-link" aria-label="Back">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
        <h1 class="transaction-page-title">New expense #<?= htmlspecialchars((string)$nextId, ENT_QUOTES, 'UTF-8'); ?></h1>
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
              <option value="Food & Drinks (Groceries)">Food & Drinks (Groceries)</option>
              <option value="Food & Drinks (Eating Out)">Food & Drinks (Eating Out)</option>
              <option value="Transport (Public)">Transport (Public)</option>
              <option value="Transport (Other)">Transport (Other)</option>
              <option value="Entertainment (Subscriptions)">Entertainment (Subscriptions)</option>
              <option value="Entertainment (Performances)">Entertainment (Performances)</option>
              <option value="Entertainment (Parties)">Entertainment (Parties)</option>
              <option value="Entertainment (Other)">Entertainment (Other)</option>
              <option value="Medication & Supplements">Medication & Supplements</option>
              <option value="Health & Wellness Facilities">Health & Wellness Facilities</option>
              <option value="Bills">Bills</option>
              <option value="Personal Shopping (Clothing & Accessories)">Personal Shopping (Clothing & Accessories)</option>
              <option value="Personal Shopping (Hair)">Personal Shopping (Hair)</option>
              <option value="Personal Shopping (Books)">Personal Shopping (Books)</option>
              <option value="Personal Shopping (Technology)">Personal Shopping (Technology)</option>
              <option value="Personal Shopping (Other)">Personal Shopping (Other)</option>
              <option value="Savings (Home)">Savings (Home)</option>
              <option value="Savings (Gifts)">Savings (Gifts)</option>
              <option value="Savings (Holiday)">Savings (Holiday)</option>
              <option value="Savings (ISA)">Savings (ISA)</option>
              <option value="Savings (Other)">Savings (Other)</option>
            </select>
          </div>

          <div class="form-field">
            <label for="title">Title</label>
            <input id="title" type="text" name="title" placeholder="e.g. Grocery shop" required>
          </div>

          <div class="form-field">
            <label for="amount">Amount</label>
            <input id="amount" type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required>
          </div>

          <div class="form-field">
            <label for="date">Date</label>
            <input id="date" type="date" name="date" value="<?= date('Y-m-d'); ?>" required>
          </div>

          <div class="form-field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Optional notes"></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="transaction-submit">Add expense</button>
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
