<?php
// ================== config.php ==================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true, 'samesite' => 'Lax'
    ]);
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'u315614949_afrah');
define('DB_USER', 'u315614949_afrah');
define('DB_PASS', '8&AmNm3d7@r');

define('SITE_URL', 'https://asifataleafrah.com');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('WA_NUMBER', '966564710254');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed.');
}

// =============== Helpers ===============
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check($t) { return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t); }

function is_logged_in() { return !empty($_SESSION['admin_id']); }
function require_login() {
    if (!is_logged_in()) { header('Location: ' . SITE_URL . '/admin/login.php'); exit; }
}

function get_setting($key, $default = '') {
    global $pdo; static $c = [];
    if (!array_key_exists($key, $c)) {
        $s = $pdo->prepare("SELECT svalue FROM settings WHERE skey=?");
        $s->execute([$key]);
        $r = $s->fetch();
        $c[$key] = $r ? $r['svalue'] : $default;
    }
    return $c[$key];
}

function flash($type, $msg) { $_SESSION['flash'][] = ['type'=>$type,'msg'=>$msg]; }
function get_flashes() { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function redirect($url) { header("Location: $url"); exit; }

function client_ip() {
    return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}