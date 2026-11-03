<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

// Rate limit بسيط: 5 حجوزات / 10 دقائق لكل IP
$ip = client_ip();
$chk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE ip=? AND created_at > (NOW() - INTERVAL 10 MINUTE)");
$chk->execute([$ip]);
if ((int)$chk->fetchColumn() >= 5) {
    http_response_code(429); echo json_encode(['ok'=>false,'error'=>'محاولات كثيرة']); exit;
}

$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$type    = trim($_POST['event_type'] ?? 'أخرى');
$date    = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
$guests  = !empty($_POST['guests']) ? (int)$_POST['guests'] : null;
$items   = is_array($_POST['items'] ?? null) ? implode('، ', array_map('trim', $_POST['items'])) : ($_POST['items'] ?? '');
$notes   = trim($_POST['notes'] ?? '');

if (!$name || mb_strlen($name) > 120) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'الاسم مطلوب']); exit; }

$stmt = $pdo->prepare("INSERT INTO bookings (name,phone,event_type,event_date,guests,items,notes,ip) VALUES (?,?,?,?,?,?,?,?)");
$stmt->execute([$name,$phone,$type,$date,$guests,$items,$notes,$ip]);

echo json_encode(['ok'=>true, 'id' => $pdo->lastInsertId()]);