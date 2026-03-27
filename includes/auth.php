<?php
function hms_table_exists($mysqli, $tableName)
{
    $tableName = $mysqli->real_escape_string($tableName);
    $result = $mysqli->query("SHOW TABLES LIKE '{$tableName}'");
    if (!$result) {
        return false;
    }

    $exists = $result->num_rows > 0;
    $result->close();

    return $exists;
}

function hms_column_exists($mysqli, $tableName, $columnName)
{
    $tableName = $mysqli->real_escape_string($tableName);
    $columnName = $mysqli->real_escape_string($columnName);
    $result = $mysqli->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
    if (!$result) {
        return false;
    }

    $exists = $result->num_rows > 0;
    $result->close();

    return $exists;
}

function hms_bootstrap_auth_schema($mysqli)
{
    static $bootstrapped = false;

    if ($bootstrapped || !($mysqli instanceof mysqli) || $mysqli->connect_errno) {
        return;
    }

    $bootstrapped = true;

    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `full_name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `username` varchar(100) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','user') NOT NULL DEFAULT 'user',
            `legacy_id` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_users_email` (`email`),
            UNIQUE KEY `uniq_users_username` (`username`),
            UNIQUE KEY `uniq_users_role_legacy` (`role`,`legacy_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    if (hms_table_exists($mysqli, 'userregistration')) {
        if (!hms_column_exists($mysqli, 'userregistration', 'regDate')) {
            $mysqli->query("ALTER TABLE `userregistration` ADD COLUMN `regDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");
        }

        if (!hms_column_exists($mysqli, 'userregistration', 'updationDate')) {
            $mysqli->query("ALTER TABLE `userregistration` ADD COLUMN `updationDate` varchar(255) NOT NULL DEFAULT ''");
        }

        if (!hms_column_exists($mysqli, 'userregistration', 'passUdateDate')) {
            $mysqli->query("ALTER TABLE `userregistration` ADD COLUMN `passUdateDate` varchar(255) NOT NULL DEFAULT ''");
        }
    }

    if (hms_table_exists($mysqli, 'admin')) {
        $mysqli->query(
            "INSERT INTO `users` (`full_name`, `email`, `username`, `phone`, `password`, `role`, `legacy_id`, `created_at`, `updated_at`)
             SELECT
                'Administrator',
                a.email,
                a.username,
                NULL,
                a.password,
                'admin',
                a.id,
                a.reg_date,
                a.reg_date
             FROM `admin` a
             LEFT JOIN `users` u ON u.role = 'admin' AND u.legacy_id = a.id
             WHERE u.id IS NULL
               AND NOT EXISTS (
                   SELECT 1
                   FROM `users` ux
                   WHERE ux.email = a.email OR ux.username = a.username
               )"
        );
    }

    if (hms_table_exists($mysqli, 'userregistration')) {
        $mysqli->query(
            "INSERT INTO `users` (`full_name`, `email`, `username`, `phone`, `password`, `role`, `legacy_id`, `created_at`, `updated_at`)
             SELECT
                TRIM(CONCAT(ur.firstName, ' ', ur.middleName, ' ', ur.lastName)),
                ur.email,
                CONCAT('student_', ur.id),
                ur.contactNo,
                ur.password,
                'user',
                ur.id,
                NOW(),
                NOW()
             FROM `userregistration` ur
             LEFT JOIN `users` u ON u.role = 'user' AND u.legacy_id = ur.id
             WHERE u.id IS NULL
               AND NOT EXISTS (
                   SELECT 1
                   FROM `users` ux
                   WHERE ux.email = ur.email OR ux.username = CONCAT('student_', ur.id)
               )"
        );
    }
}

function hms_password_hash($plainPassword)
{
    return password_hash($plainPassword, PASSWORD_DEFAULT);
}

function hms_password_verify($plainPassword, $storedPassword)
{
    $storedPassword = (string) $storedPassword;
    if ($storedPassword === '') {
        return false;
    }

    $info = password_get_info($storedPassword);
    if (!empty($info['algo'])) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, (string) $plainPassword);
}

function hms_password_needs_upgrade($storedPassword)
{
    $storedPassword = (string) $storedPassword;
    if ($storedPassword === '') {
        return true;
    }

    $info = password_get_info($storedPassword);
    if (empty($info['algo'])) {
        return true;
    }

    return password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
}

function hms_redirect($path)
{
    header('Location: ' . $path);
    exit();
}

function hms_is_logged_in()
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
}

function hms_current_role()
{
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function hms_set_flash($type, $message)
{
    $_SESSION['flash_message'] = array(
        'type' => $type,
        'message' => $message,
    );
}

function hms_pull_flash()
{
    if (empty($_SESSION['flash_message']) || !is_array($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flash;
}

function hms_login_user(array $user)
{
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['full_name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['login'] = $user['email'];
    $_SESSION['legacy_id'] = (int) $user['legacy_id'];
    $_SESSION['id'] = (int) $user['legacy_id'];
}

function hms_logout_user()
{
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function hms_user_dashboard_path($role)
{
    return $role === 'admin' ? 'admin/dashboard.php' : 'dashboard.php';
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    hms_bootstrap_auth_schema($mysqli);
}
