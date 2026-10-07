<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('DATA_DIR',  __DIR__ . '/data');
define('DATA_FILE', DATA_DIR . '/users.json');
define('CONFIG_FILE', DATA_DIR . '/settings.json');

// ===== 管理员账号密码 =====
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0755, true);
if (!file_exists(DATA_FILE)) file_put_contents(DATA_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
if (!file_exists(CONFIG_FILE)) {
    $default = ['site_name' => '星云通行证'];
    file_put_contents(CONFIG_FILE, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ===== 系统配置读写 =====
function getConfig() {
    if (!file_exists(CONFIG_FILE)) return ['site_name' => '星云通行证'];
    $cfg = json_decode(file_get_contents(CONFIG_FILE), true);
    if (!is_array($cfg)) $cfg = [];
    if (empty($cfg['site_name'])) $cfg['site_name'] = '星云通行证';
    return $cfg;
}
function saveConfig($cfg) {
    file_put_contents(CONFIG_FILE, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function siteName() {
    static $name = null;
    if ($name === null) {
        $cfg = getConfig();
        $name = $cfg['site_name'];
    }
    return $name;
}
function adminUsername() { return ADMIN_USERNAME; }
function adminPassword() { return ADMIN_PASSWORD; }

if (!defined('SITE_NAME')) define('SITE_NAME', siteName());

// ===== 用户数据读写 =====
function getUsers() {
    if (!file_exists(DATA_FILE)) return [];
    $data = json_decode(file_get_contents(DATA_FILE), true);
    return is_array($data) ? $data : [];
}
function saveUsers($users) {
    file_put_contents(DATA_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function findUserByNickname($nickname) {
    foreach (getUsers() as $u) if (($u['nickname'] ?? '') === $nickname) return $u;
    return null;
}
function findUserById($id) {
    foreach (getUsers() as $u) if (($u['id'] ?? '') === $id) return $u;
    return null;
}
function isLoggedIn() { return !empty($_SESSION['user']); }
function isAdmin()    { return !empty($_SESSION['is_admin']); }
function requireLogin() { if (!isLoggedIn()) { header('Location: login.php'); exit; } }
function requireAdmin() { if (!isAdmin())    { header('Location: admin_login.php'); exit; } }

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }

function isSensitiveCaptcha($captcha) {
    $sensitive = ['9','1','7','8','c','n','m','j','b','3'];
    foreach ($sensitive as $c) {
        if (stripos($captcha, $c) !== false) return true;
    }
    return false;
}