<?php
$host = 'localhost';
$dbname = 'budget-tracker';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_theme_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        theme_name VARCHAR(100) NOT NULL DEFAULT 'Default skin',
        main_background VARCHAR(7) NOT NULL DEFAULT '#FFFEF9',
        secondary_background VARCHAR(7) NOT NULL DEFAULT '#F9C8E0',
        accent_color VARCHAR(7) NOT NULL DEFAULT '#B8336A',
        secondary_accent VARCHAR(7) NOT NULL DEFAULT '#972957',
        light_secondary VARCHAR(7) NOT NULL DEFAULT '#eeaacb',
        dark_color VARCHAR(7) NOT NULL DEFAULT '#513229',
        light_dark_color VARCHAR(7) NOT NULL DEFAULT '#945C4C',
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_user_theme_user (user_id),
        KEY idx_user_theme_active (user_id, is_active),
        CONSTRAINT fk_user_theme_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )");

    $columns = $pdo->query("SHOW COLUMNS FROM user_theme_settings LIKE 'is_active'")->fetch();
    if (!$columns) {
        $pdo->exec("ALTER TABLE user_theme_settings ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0 AFTER light_dark_color");
    }

    $lightSecondaryColumn = $pdo->query("SHOW COLUMNS FROM user_theme_settings LIKE 'light_secondary'")->fetch();
    if (!$lightSecondaryColumn) {
        $pdo->exec("ALTER TABLE user_theme_settings ADD COLUMN light_secondary VARCHAR(7) NOT NULL DEFAULT '#eeaacb' AFTER secondary_accent");
    }

    $pdo->exec("UPDATE user_theme_settings SET light_secondary = '#eeaacb' WHERE light_secondary IS NULL OR light_secondary = '' OR light_secondary = '#972957'");

    try {
        $pdo->exec('ALTER TABLE user_theme_settings DROP FOREIGN KEY fk_user_theme_user');
    } catch (PDOException $e) {
    }

    try {
        $pdo->exec('ALTER TABLE user_theme_settings DROP INDEX user_id');
    } catch (PDOException $e) {
    }

    $hasUserKey = $pdo->query("SHOW INDEX FROM user_theme_settings WHERE Key_name = 'idx_user_theme_user'")->fetch();
    if (!$hasUserKey) {
        $pdo->exec('ALTER TABLE user_theme_settings ADD KEY idx_user_theme_user (user_id)');
    }

    $hasActiveKey = $pdo->query("SHOW INDEX FROM user_theme_settings WHERE Key_name = 'idx_user_theme_active'")->fetch();
    if (!$hasActiveKey) {
        $pdo->exec('ALTER TABLE user_theme_settings ADD KEY idx_user_theme_active (user_id, is_active)');
    }

    try {
        $pdo->exec('ALTER TABLE user_theme_settings ADD CONSTRAINT fk_user_theme_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE');
    } catch (PDOException $e) {
    }

    $activeRows = $pdo->prepare('SELECT id FROM user_theme_settings WHERE user_id = ? ORDER BY is_active DESC, updated_at DESC, id DESC LIMIT 1');
    $activeRows->execute([$_SESSION['user_id'] ?? null]);
    $activeSkin = $activeRows->fetch(PDO::FETCH_ASSOC);

    if ($activeSkin) {
        $pdo->prepare('UPDATE user_theme_settings SET is_active = 0 WHERE user_id = ? AND id != ?')->execute([$_SESSION['user_id'] ?? null, $activeSkin['id']]);
        $pdo->prepare('UPDATE user_theme_settings SET is_active = 1 WHERE id = ? AND user_id = ?')->execute([$activeSkin['id'], $_SESSION['user_id'] ?? null]);
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>