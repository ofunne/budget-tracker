<?php

session_start();
require 'config_budget.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please log in to update your details.'];
    /*header('Location: index.php'); 
    exit();*/
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $surname = trim($_POST['surname'] ?? '');

    if ($firstname === '' || $surname === '') {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please enter first name and surname.'];
        header('Location: edit_account.php');
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users SET firstname = ?, surname = ? WHERE user_id = ?");
    $success = $stmt->execute([$firstname, $surname, $user_id]);

    if ($success) {
        $_SESSION['firstname'] = $firstname;
        $_SESSION['surname'] = $surname;

        $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Account details updated.'];
    } else {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Failed to update your details.'];
    }

    header('Location: account.php');
    exit();
}

$stmt = $pdo->prepare("SELECT firstname, surname, email FROM `users` WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$firstname = $user['firstname'] ?? '';
$surname = $user['surname'] ?? '';
$email = $user['email'] ?? '';

?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Home </title>

    <link rel="stylesheet" href="/budget-tracker/assets/css/styles.css">
    <?php require __DIR__ . '/includes/theme_loader.php'; ?>

    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

  </head>

  <body>

    <?php include __DIR__ . '/components/navbar.php'; ?>

    <?php if (!empty($alerts)): ?>
    <div class="alert-box">
      <?php foreach($alerts as $alert): ?>
      <div class="alert <?= $alert['type']; ?>">
        <i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-x-circle'; ?>'></i>
        <span><?=$alert['message']; ?></span>
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
        <form action="/include/auth_process.php" method="POST">
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
        <form action="/include/auth_process.php" method="POST">
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

    <div class="edit-details">

      <h4>Edit Details</h4>
      <form action="" method="POST">
        <label class="first-name">First Name:
          <input type="text" name="firstname" value="<?= htmlspecialchars($firstname) ?>">
        </label><br>
        <label class="sur-name">Surname:
          <input type="text" name="surname" value="<?= htmlspecialchars($surname) ?>">
        </label><br>
        <p class="e-mail">
          Email: 
          <?php if (!empty($email)): ?>
            <?= $email; ?>
          <?php endif; ?>
        </p>
        <button type="submit" class="button" id="update-acc-button">Update Account Details</button>
        <button class="button" id="cancel-acc-update-button">
          <a href="account.php" style="text-decoration:none;color:#522B29;">Cancel</a>
        </button>
      </form>

    </div>

    <script src="/budget-tracker/assets/js/script.js"></script>
  </body>
</html>