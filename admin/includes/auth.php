<?php
require_once __DIR__ . '/../../config.php';
require_login();

// Timeout جلسة: 4 ساعات خمول
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > 14400) {
    session_destroy();
    redirect(SITE_URL . '/admin/login.php?timeout=1');
}
$_SESSION['last_active'] = time();