<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if (!empty($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare('SELECT * FROM user_theme_settings WHERE user_id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $savedTheme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$savedTheme) {
        $fallback = $pdo->prepare('SELECT * FROM user_theme_settings WHERE user_id = ? ORDER BY updated_at DESC, id DESC LIMIT 1');
        $fallback->execute([$_SESSION['user_id']]);
        $savedTheme = $fallback->fetch(PDO::FETCH_ASSOC);

        if ($savedTheme) {
            $pdo->prepare('UPDATE user_theme_settings SET is_active = 0 WHERE user_id = ?')->execute([$_SESSION['user_id']]);
            $pdo->prepare('UPDATE user_theme_settings SET is_active = 1 WHERE id = ? AND user_id = ?')->execute([$savedTheme['id'], $_SESSION['user_id']]);
        }
    }

    if ($savedTheme) {
        foreach ($defaultTheme as $key => $value) {
            if (!empty($savedTheme[$key])) {
                $currentTheme[$key] = $savedTheme[$key];
            }
        }
    }
}
?>
<style>
  :root {
    --light: <?= htmlspecialchars($currentTheme['main_background'], ENT_QUOTES, 'UTF-8'); ?>;
    --primary: <?= htmlspecialchars($currentTheme['accent_color'], ENT_QUOTES, 'UTF-8'); ?>;
    --secondary: <?= htmlspecialchars($currentTheme['secondary_accent'], ENT_QUOTES, 'UTF-8'); ?>;
    --light-primary: <?= htmlspecialchars($currentTheme['secondary_background'], ENT_QUOTES, 'UTF-8'); ?>;
    --light-secondary: <?= htmlspecialchars($currentTheme['light_secondary'], ENT_QUOTES, 'UTF-8'); ?>;
    --dark: <?= htmlspecialchars($currentTheme['dark_color'], ENT_QUOTES, 'UTF-8'); ?>;
    --light-dark: <?= htmlspecialchars($currentTheme['light_dark_color'], ENT_QUOTES, 'UTF-8'); ?>;
  }
</style>
