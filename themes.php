<?php
session_start();
require __DIR__ . '/config_budget.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please log in to manage your theme.'];
    header('Location: index.php');
    exit();
}

$alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);

$defaultTheme = [
    'theme_name' => 'Default skin',
    'main_background' => '#FFFEF9',
    'secondary_background' => '#F9C8E0',
    'accent_color' => '#B8336A',
    'secondary_accent' => '#972957',
    'light_secondary' => '#eeaacb',
    'dark_color' => '#513229',
    'light_dark_color' => '#945C4C',
];

$currentTheme = $defaultTheme;
$savedThemes = [];

$stmt = $pdo->prepare('SELECT * FROM user_theme_settings WHERE user_id = ? ORDER BY is_active DESC, updated_at DESC, id DESC');
$stmt->execute([$_SESSION['user_id']]);
$savedThemes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($savedThemes)) {
    $activeTheme = null;
    foreach ($savedThemes as $theme) {
        if ((int) $theme['is_active'] === 1) {
            $activeTheme = $theme;
            break;
        }
    }

    if ($activeTheme) {
        foreach ($defaultTheme as $key => $value) {
            if (!empty($activeTheme[$key])) {
                if ($key === 'secondary_accent' && in_array($activeTheme[$key], ['#972957', ''], true)) {
                    $currentTheme[$key] = $defaultTheme[$key];
                } else {
                    $currentTheme[$key] = $activeTheme[$key];
                }
            }
        }
    } else {
        $activeTheme = $savedThemes[0];
        foreach ($defaultTheme as $key => $value) {
            if (!empty($activeTheme[$key])) {
                if ($key === 'secondary_accent' && in_array($activeTheme[$key], ['#972957', ''], true)) {
                    $currentTheme[$key] = $defaultTheme[$key];
                } else {
                    $currentTheme[$key] = $activeTheme[$key];
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_theme'])) {
    $selectedThemeId = (int) ($_POST['selected_theme_id'] ?? 0);
    if ($selectedThemeId > 0) {
        $pdo->prepare('UPDATE user_theme_settings SET is_active = 0 WHERE user_id = ?')->execute([$_SESSION['user_id']]);
        $stmt = $pdo->prepare('UPDATE user_theme_settings SET is_active = 1 WHERE id = ? AND user_id = ?');
        $stmt->execute([$selectedThemeId, $_SESSION['user_id']]);
        $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Active skin updated.'];
        header('Location: themes.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_theme'])) {
    $themeName = trim($_POST['theme_name'] ?? 'Default skin');
    $selectedThemeId = (int) ($_POST['selected_theme_id'] ?? 0);
    $themeData = [
        'main_background' => $_POST['main_background'] ?? $defaultTheme['main_background'],
        'secondary_background' => $_POST['secondary_background'] ?? $defaultTheme['secondary_background'],
        'accent_color' => $_POST['accent_color'] ?? $defaultTheme['accent_color'],
        'secondary_accent' => $_POST['secondary_accent'] ?? $defaultTheme['secondary_accent'],
        'light_secondary' => $_POST['light_secondary'] ?? $defaultTheme['light_secondary'],
        'dark_color' => $_POST['dark_color'] ?? $defaultTheme['dark_color'],
        'light_dark_color' => $_POST['light_dark_color'] ?? $defaultTheme['light_dark_color'],
    ];

    foreach ($themeData as $key => $value) {
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            $themeData[$key] = $defaultTheme[$key];
        }
    }

    $themeName = $themeName === '' ? 'Default skin' : $themeName;

    if ($selectedThemeId > 0) {
        $stmt = $pdo->prepare('UPDATE user_theme_settings SET theme_name = ?, main_background = ?, secondary_background = ?, accent_color = ?, secondary_accent = ?, light_secondary = ?, dark_color = ?, light_dark_color = ?, is_active = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $success = $stmt->execute([
            $themeName,
            $themeData['main_background'],
            $themeData['secondary_background'],
            $themeData['accent_color'],
            $themeData['secondary_accent'],
            $themeData['light_secondary'],
            $themeData['dark_color'],
            $themeData['light_dark_color'],
            $selectedThemeId,
            $_SESSION['user_id'],
        ]);
        $pdo->prepare('UPDATE user_theme_settings SET is_active = 0 WHERE user_id = ? AND id != ?')->execute([$_SESSION['user_id'], $selectedThemeId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO user_theme_settings (user_id, theme_name, main_background, secondary_background, accent_color, secondary_accent, light_secondary, dark_color, light_dark_color, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
        $success = $stmt->execute([
            $_SESSION['user_id'],
            $themeName,
            $themeData['main_background'],
            $themeData['secondary_background'],
            $themeData['accent_color'],
            $themeData['secondary_accent'],
            $themeData['light_secondary'],
            $themeData['dark_color'],
            $themeData['light_dark_color'],
        ]);
        $pdo->prepare('UPDATE user_theme_settings SET is_active = 0 WHERE user_id = ? AND id != LAST_INSERT_ID()')->execute([$_SESSION['user_id']]);
    }

    if ($success) {
        $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Your skin was saved successfully.'];
        $currentTheme = array_merge($defaultTheme, $themeData);
        $currentTheme['theme_name'] = $themeName;
    } else {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Your skin could not be saved.'];
    }

    header('Location: themes.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_theme'])) {
    $pdo->prepare('UPDATE user_theme_settings SET is_active = 0 WHERE user_id = ?')->execute([$_SESSION['user_id']]);
    $stmt = $pdo->prepare('INSERT INTO user_theme_settings (user_id, theme_name, main_background, secondary_background, accent_color, secondary_accent, light_secondary, dark_color, light_dark_color, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
    $stmt->execute([
        $_SESSION['user_id'],
        $defaultTheme['theme_name'],
        $defaultTheme['main_background'],
        $defaultTheme['secondary_background'],
        $defaultTheme['accent_color'],
        $defaultTheme['secondary_accent'],
        $defaultTheme['light_secondary'],
        $defaultTheme['dark_color'],
        $defaultTheme['light_dark_color'],
    ]);

    $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Your theme has been reset to the default palette.'];
    header('Location: themes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Settings</title>
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

    <main class="content-page theme-page">
      <div class="content-page-header account-settings-header">
        <h1>Theme &amp; skin settings</h1>
      </div>

      <div class="theme-page-grid">
        <section class="theme-panel">
          <h2>Customise your palette</h2>
          <form method="POST" action="themes.php" class="theme-form" id="themeForm">
            <div class="theme-form-row">
              <label>
                <span>Saved skins</span>
                <select name="selected_theme_id" id="saved_theme_id">
                  <option value="">Create a new skin</option>
                  <?php foreach ($savedThemes as $skin): ?>
                    <option value="<?= (int) $skin['id']; ?>" <?= ((int) $skin['is_active'] === 1) ? 'selected' : ''; ?>><?= htmlspecialchars($skin['theme_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>

            <div class="theme-form-row">
              <label>
                <span>Skin name</span>
                <input type="text" name="theme_name" id="theme_name" value="<?= htmlspecialchars($currentTheme['theme_name'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="My pastel skin">
              </label>
            </div>

            <div class="theme-form-grid">
              <label>
                <span>Main background</span>
                <input type="color" name="main_background" id="main_background" value="<?= htmlspecialchars($currentTheme['main_background'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                <span>Light primary background</span>
                <input type="color" name="secondary_background" id="secondary_background" value="<?= htmlspecialchars($currentTheme['secondary_background'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                <span>Accent / text</span>
                <input type="color" name="accent_color" id="accent_color" value="<?= htmlspecialchars($currentTheme['accent_color'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                <span>Secondary / hover for primary</span>
                <input type="color" name="secondary_accent" id="secondary_accent" value="<?= htmlspecialchars($currentTheme['secondary_accent'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                <span>Light secondary / hover for secondary</span>
                <input type="color" name="light_secondary" id="light_secondary" value="<?= htmlspecialchars($currentTheme['light_secondary'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                <span>Secondary text tone</span>
                <input type="color" name="dark_color" id="dark_color" value="<?= htmlspecialchars($currentTheme['dark_color'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>

              <label>
                <span>Light dark tone</span>
                <input type="color" name="light_dark_color" id="light_dark_color" value="<?= htmlspecialchars($currentTheme['light_dark_color'], ENT_QUOTES, 'UTF-8'); ?>">
              </label>
            </div>

            <div class="theme-form-actions">
              <button type="submit" name="save_theme" class="primary-button">Save skin</button>
              <button type="submit" name="select_theme" class="secondary-button">Use selected skin</button>
              <button type="submit" name="reset_theme" class="secondary-button" formnovalidate>Reset defaults</button>
            </div>
          </form>
        </section>

        <section class="theme-panel preview-panel">
          <h2>Preview</h2>
          <div class="theme-preview-card" id="themePreviewCard">
            <div class="theme-preview-header" id="themePreviewHeader" style="background: <?= htmlspecialchars($currentTheme['main_background'], ENT_QUOTES, 'UTF-8'); ?>; color: <?= htmlspecialchars($currentTheme['accent_color'], ENT_QUOTES, 'UTF-8'); ?>;">
              <span id="themePreviewLabel"><?= htmlspecialchars($currentTheme['theme_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="theme-preview-body" id="themePreviewBody" style="background: <?= htmlspecialchars($currentTheme['secondary_background'], ENT_QUOTES, 'UTF-8'); ?>; color: <?= htmlspecialchars($currentTheme['dark_color'], ENT_QUOTES, 'UTF-8'); ?>;">
              <div class="theme-preview-box" id="themePreviewBoxPrimary" style="background: <?= htmlspecialchars($currentTheme['accent_color'], ENT_QUOTES, 'UTF-8'); ?>; color: #fff;">Budget</div>
              <div class="theme-preview-box alt" id="themePreviewBoxSecondary" style="background: <?= htmlspecialchars($currentTheme['light_dark_color'], ENT_QUOTES, 'UTF-8'); ?>; color: #fff;">Insights</div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <script src="/budget-tracker/assets/js/script.js"></script>
    <script>
      (function () {
        const form = document.getElementById('themeForm');
        if (!form) return;

        const previewLabel = document.getElementById('themePreviewLabel');
        const previewHeader = document.getElementById('themePreviewHeader');
        const previewBody = document.getElementById('themePreviewBody');
        const previewPrimary = document.getElementById('themePreviewBoxPrimary');
        const previewSecondary = document.getElementById('themePreviewBoxSecondary');

        function updatePreview() {
          const values = {
            mainBackground: document.getElementById('main_background').value,
            secondaryBackground: document.getElementById('secondary_background').value,
            accentColor: document.getElementById('accent_color').value,
            secondaryAccent: document.getElementById('secondary_accent').value,
            lightSecondary: document.getElementById('light_secondary').value,
            darkColor: document.getElementById('dark_color').value,
            lightDarkColor: document.getElementById('light_dark_color').value,
            skinName: document.getElementById('theme_name').value || 'My skin'
          };

          previewLabel.textContent = values.skinName;
          previewHeader.style.background = values.mainBackground;
          previewHeader.style.color = values.accentColor;
          previewBody.style.background = values.secondaryBackground;
          previewBody.style.color = values.darkColor;
          previewPrimary.style.background = values.accentColor;
          previewSecondary.style.background = values.lightSecondary;
        }

        form.addEventListener('input', updatePreview);
        form.addEventListener('change', updatePreview);
        updatePreview();
      })();
    </script>
  </body>
</html>
