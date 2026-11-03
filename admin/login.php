<?php
require __DIR__ . '/../config.php';

if (is_logged_in()) redirect(SITE_URL . '/admin/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'انتهت الجلسة، حاول مرة أخرى.';
    } elseif (!empty($_POST['website'])) {
        $error = 'خطأ.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = client_ip();

        $rl = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip=? AND success=0 AND attempted_at > (NOW() - INTERVAL 15 MINUTE)");
        $rl->execute([$ip]);
        $failCount = (int)$rl->fetchColumn();

        if ($failCount >= 5) {
            $error = 'محاولات كثيرة فاشلة. انتظر 15 دقيقة.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=? OR email=? LIMIT 1");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                if (password_needs_rehash($admin['password_hash'], PASSWORD_ARGON2ID)) {
                    $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([password_hash($password, PASSWORD_ARGON2ID), $admin['id']]);
                }
                session_regenerate_id(true);
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                $_SESSION['login_time'] = time();

                $pdo->prepare("UPDATE admins SET last_login=NOW(), last_ip=? WHERE id=?")->execute([$ip, $admin['id']]);
                $pdo->prepare("INSERT INTO login_attempts (username,ip,success) VALUES (?,?,1)")->execute([$username, $ip]);

                redirect(SITE_URL . '/admin/index.php');
            } else {
                $pdo->prepare("INSERT INTO login_attempts (username,ip,success) VALUES (?,?,0)")->execute([$username, $ip]);
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تسجيل الدخول — عاصفة الأفراح</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  :root {
    --primary: #F59E0B;
    --primary-hover: #D97706;
    --bg-dark: #0B0F19;
    --text-main: #F3F4F6;
    --text-muted: #9CA3AF;
    --border: rgba(255, 255, 255, 0.1);
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Tajawal', sans-serif;
    background-color: var(--bg-dark);
    color: var(--text-main);
    height: 100vh;
    display: flex;
    overflow: hidden;
  }

  .login-wrapper { display: flex; width: 100%; height: 100vh; }

  /* Image Side (Right) */
  .login-image {
    flex: 1.2;
    background-image: url('https://images.unsplash.com/photo-1519225421980-715cb0215aed?q=80&w=2070&auto=format&fit=crop');
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex; align-items: center; justify-content: center;
  }
  .login-image::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(11, 15, 25, 0.8) 0%, rgba(11, 15, 25, 0.4) 100%);
  }
  
  /* Glass Card inside Image Side */
  .glass-brand {
    position: relative; z-index: 2; text-align: center;
    padding: 40px 50px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 24px; box-shadow: 0 30px 60px rgba(0,0,0,0.4);
    animation: floatAnim 6s ease-in-out infinite;
  }
  @keyframes floatAnim { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
  
  .glass-brand .icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    border-radius: 20px; display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: #000; margin: 0 auto 20px;
    box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
  }
  .glass-brand h1 {
    font-size: 2.5rem; font-weight: 900; margin-bottom: 10px;
    background: linear-gradient(to left, #fff, #d1d5db);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  }
  .glass-brand p { font-size: 1.1rem; color: rgba(255, 255, 255, 0.7); letter-spacing: 1px; }

  /* Form Side (Left) */
  .login-form-side {
    flex: 1; background: var(--bg-dark);
    display: flex; align-items: center; justify-content: center;
    padding: 40px; position: relative; z-index: 10;
  }
  .login-form-side::before {
    content: ''; position: absolute; width: 400px; height: 400px;
    background: var(--primary); filter: blur(150px); opacity: 0.1; z-index: -1;
  }

  .login-box { width: 100%; max-width: 420px; animation: slideIn 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
  @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }

  .login-box h2 { font-size: 2.2rem; font-weight: 900; margin-bottom: 8px; }
  .login-box p.sub { color: var(--text-muted); margin-bottom: 35px; font-size: 1rem; }

  .input-group { margin-bottom: 24px; position: relative; }
  .input-group label { display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 8px; color: var(--text-main); }
  .input-group input {
    width: 100%; padding: 16px 20px; background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border); border-radius: 16px; color: var(--text-main);
    font-family: inherit; font-size: 1rem; transition: all 0.3s ease;
  }
  .input-group input:focus {
    outline: none; border-color: var(--primary); background: rgba(245, 158, 11, 0.05);
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
  }
  .toggle-pass {
    position: absolute; left: 18px; bottom: 18px; background: none; border: none;
    color: var(--primary); cursor: pointer; font-weight: 800; font-family: inherit; font-size: 0.85rem;
  }
  .toggle-pass:hover { color: var(--primary-hover); }

  .btn-submit {
    width: 100%; padding: 16px;
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: #000; border: none; border-radius: 16px;
    font-family: inherit; font-weight: 900; font-size: 1.1rem;
    cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 20px -10px rgba(245, 158, 11, 0.6);
  }
  .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 25px -10px rgba(245, 158, 11, 0.8); }
  .btn-submit:active { transform: scale(0.98); }
  .hp { position: absolute; left: -9999px; opacity: 0; }

  @media (max-width: 992px) {
    .login-wrapper { flex-direction: column; }
    .login-image { display: none; }
    .login-form-side { flex: 1; padding: 20px; }
    .login-box { padding: 20px; }
  }
</style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-image">
    <div class="glass-brand">
      <div class="icon">⛺</div>
      <h1>عاصفة الأفراح</h1>
      <p>نظام إدارة الحفلات والمناسبات</p>
    </div>
  </div>

  <div class="login-form-side">
    <div class="login-box">
      <h2>تسجيل الدخول</h2>
      <p class="sub">أدخل بياناتك للوصول إلى لوحة التحكم</p>

      <form method="POST" autocomplete="off" id="loginForm">
        <?= csrf_field() ?>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">

        <div class="input-group">
          <label>اسم المستخدم أو البريد الإلكتروني</label>
          <input type="text" name="username" required autofocus placeholder="أدخل اسم المستخدم">
        </div>

        <div class="input-group">
          <label>كلمة المرور</label>
          <input type="password" name="password" id="pwd" required placeholder="••••••••">
          <button type="button" class="toggle-pass" onclick="togglePwd()">إظهار</button>
        </div>

        <button type="submit" class="btn-submit">دخول آمن →</button>
      </form>
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const p = document.getElementById('pwd');
  const b = event.target;
  p.type = p.type === 'password' ? 'text' : 'password';
  b.textContent = p.type === 'password' ? 'إظهار' : 'إخفاء';
}

document.addEventListener('DOMContentLoaded', () => {
  const errorMsg = <?= json_encode($error) ?>;
  const successMsg = <?= json_encode(isset($_GET['changed']) ? 'تم تغيير كلمة المرور بنجاح، يرجى الدخول بالكلمة الجديدة.' : '') ?>;

  const swalDark = Swal.mixin({
    background: '#111827', color: '#F3F4F6', confirmButtonColor: '#F59E0B',
    customClass: { popup: 'border', border: '1px solid rgba(255,255,255,0.1)' }
  });

  if (errorMsg) swalDark.fire({ icon: 'error', title: 'عذراً', text: errorMsg });
  else if (successMsg) swalDark.fire({ icon: 'success', title: 'تم بنجاح', text: successMsg });
});
</script>
</body>
</html>