<?php

session_start();
require 'config_budget.php';

$alerts = $_SESSION['alerts'] ?? [];
$active_form = $_SESSION['active_form'] ?? '';
unset($_SESSION['alerts'], $_SESSION['active_form']);

if (!isset($_SESSION['user_id'])) {
  $user_id = null;
} else {
  $user_id = $_SESSION['user_id'];
  $firstname = $_SESSION['firstname'] ?? null;
  $surname = $_SESSION['surname'] ?? null;
  $email = $_SESSION['email'] ?? null;

  if ($firstname !== null) $_SESSION['firstname'] = $firstname;
  if ($surname !== null) $_SESSION['surname'] = $surname;
  if ($email !== null) $_SESSION['email'] = $email;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_details'])) {
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please log in to update your account.'];
    header('Location: index.php');
    exit();
  }

  $firstname = trim($_POST['firstname'] ?? '');
  $surname = trim($_POST['surname'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if ($firstname === '' || $surname === '' || $email === '') {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'First name, last name and email are required.'];
    header('Location: account.php');
    exit();
  }

  $userCheck = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ?');
  $userCheck->execute([$email, $_SESSION['user_id']]);

  if ($userCheck->fetch()) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'This email address is already in use.'];
    header('Location: account.php');
    exit();
  }

  $stmt = $pdo->prepare('UPDATE users SET firstname = ?, surname = ?, email = ? WHERE user_id = ?');
  $updated = $stmt->execute([$firstname, $surname, $email, $_SESSION['user_id']]);

  if ($updated) {
    $_SESSION['firstname'] = $firstname;
    $_SESSION['surname'] = $surname;
    $_SESSION['email'] = $email;
    $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Personal details updated successfully.'];
  } else {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Failed to update your personal details.'];
  }

  header('Location: account.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
  if (!isset($_SESSION['user_id'])) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please log in to update your password.'];
    header('Location: index.php');
    exit();
  }

  $currentPassword = $_POST['current_password'] ?? '';
  $newPassword = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  $stmt = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user || !password_verify($currentPassword, $user['password'])) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Your current password is incorrect.'];
  } elseif ($newPassword !== $confirmPassword) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'New passwords do not match.'];
  } elseif (!preg_match('/^(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/', $newPassword)) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Password must be 8-72 characters, include a number and a special character.'];
  } else {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
    $success = $update->execute([$hashedPassword, $_SESSION['user_id']]);

    if ($success) {
      $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Your password was updated successfully.'];
    } else {
      $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Failed to update your password.'];
    }
  }

  header('Location: account.php');
  exit();
}

?>

<!DOCTYPE html>

<html lang="en">

  <head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>My Account</title>

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

    <main class="content-page account-settings-page">
      <div class="content-page-header account-settings-header">
        <h1>Account settings</h1>
      </div>

      <div class="account-settings-grid">
        <section class="account-panel">
          <h2>Personal details</h2>
          <form class="account-form" method="POST" action="account.php">
            <input type="hidden" name="save_details" value="1">
            <div class="account-form-row">
              <label>
                <span>First name</span>
                <input type="text" name="firstname" value="<?php echo !empty($firstname) ? htmlspecialchars($firstname) : ''; ?>" placeholder="First name">
              </label>
              <label>
                <span>Last name</span>
                <input type="text" name="surname" value="<?php echo !empty($surname) ? htmlspecialchars($surname) : ''; ?>" placeholder="Last name">
              </label>
            </div>

            <div class="account-form-row single">
              <label>
                <span>Email address</span>
                <input type="email" name="email" value="<?php echo !empty($email) ? htmlspecialchars($email) : ''; ?>" placeholder="Email address">
              </label>
            </div>

            <div class="account-form-row">
              <label>
                <span>Language</span>
                <select name="language">
                  <option selected>English</option>
                  <option>French</option>
                  <option>Spanish</option>
                </select>
              </label>
              <label>
                <span>Address format</span>
                <select name="address_format">
                  <option selected>UK format</option>
                  <option>US format</option>
                  <option>EU format</option>
                </select>
              </label>
            </div>

            <div class="account-form-actions">
              <button type="submit" class="primary-button">Save changes</button>
            </div>
          </form>
        </section>

        <section class="account-panel">
          <h2>Profile picture</h2>
          <div class="profile-picture-layout">
            <div class="profile-picture-circle" aria-label="Current profile picture">
              <?php echo strtoupper(substr((string)($firstname ?? 'U'), 0, 1) . substr((string)($surname ?? 'A'), 0, 1)); ?>
            </div>

            <div class="profile-picture-actions">
              <button type="button" class="primary-button">Upload picture</button>
              <button type="button" class="secondary-button">Delete picture</button>
            </div>
          </div>
        </section>

        <section class="account-panel">
          <h2>Password</h2>
          <form class="account-form password-form" method="POST" action="account.php">
            <input type="hidden" name="update_password" value="1">
            <div class="account-form-row single">
              <label>
                <span>Current password</span>
                <input id="currentPassword" name="current_password" type="password" placeholder="Current password">
              </label>
            </div>

            <div class="account-form-row single password-field-wrap">
              <label>
                <span>New password</span>
                <input id="newPassword" name="new_password" type="password" placeholder="New password">
              </label>
              <div class="password-warning" id="newPasswordWarning" aria-live="polite">
                Password must be between 8 and 72 characters, include at least one number and one special character.
              </div>
            </div>

            <div class="account-form-row single password-field-wrap">
              <label>
                <span>Confirm new password</span>
                <input id="confirmPassword" name="confirm_password" type="password" placeholder="Confirm new password">
              </label>
              <div class="password-warning" id="confirmPasswordWarning" aria-live="polite">
                Passwords do not match.
              </div>
            </div>

            <div class="account-form-actions">
              <button type="submit" class="primary-button">Update password</button>
            </div>
          </form>
        </section>

        <section class="account-panel danger-panel">
          <h2>Delete account</h2>
          <div class="danger-actions">
            <button type="button" class="danger-button">Delete account</button>
            <button type="button" class="secondary-button">Log out</button>
          </div>
        </section>
      </div>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
    <script>
      const passwordField = document.getElementById('newPassword');
      const confirmPasswordField = document.getElementById('confirmPassword');
      const newPasswordWarning = document.getElementById('newPasswordWarning');
      const confirmPasswordWarning = document.getElementById('confirmPasswordWarning');

      function validatePasswordStrength(value) {
        const hasNumber = /\d/.test(value);
        const hasSpecial = /[^A-Za-z0-9]/.test(value);
        return value.length >= 8 && value.length <= 72 && hasNumber && hasSpecial;
      }

      function updatePasswordWarnings() {
        const newValue = passwordField.value;
        const confirmValue = confirmPasswordField.value;

        if (newValue.length > 0) {
          newPasswordWarning.style.display = validatePasswordStrength(newValue) ? 'none' : 'block';
        } else {
          newPasswordWarning.style.display = 'none';
        }

        if (confirmValue.length > 0) {
          confirmPasswordWarning.style.display = confirmValue === newValue && newValue.length > 0 ? 'none' : 'block';
        } else {
          confirmPasswordWarning.style.display = 'none';
        }
      }

      if (passwordField && confirmPasswordField) {
        passwordField.addEventListener('input', updatePasswordWarnings);
        confirmPasswordField.addEventListener('input', updatePasswordWarnings);
      }
    </script>
  </body>
</html>