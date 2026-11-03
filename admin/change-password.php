<?php
require __DIR__ . '/includes/auth.php';
$pageTitle = 'تغيير كلمة المرور';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? '')) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id=?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current, $admin['password_hash'])) {
        flash('error', 'كلمة المرور الحالية غير صحيحة.');
    } elseif (mb_strlen($new) < 8) {
        flash('error', 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.');
    } elseif ($new !== $confirm) {
        flash('error', 'كلمة المرور الجديدة وتأكيدها غير متطابقين.');
    } else {
        $newHash = password_hash($new, PASSWORD_ARGON2ID);
        $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([$newHash, $_SESSION['admin_id']]);
        
        session_regenerate_id(true);
        $_SESSION['admin_id'] = null;
        $_SESSION['admin_user'] = null;
        session_destroy();
        
        redirect(SITE_URL . '/admin/login.php?changed=1');
    }
    redirect('change-password.php');
}

include __DIR__ . '/includes/header.php';
?>

<style>
    /* ====== خلفية متحركة (Mesh Gradient) ====== */
    .pwd-bg {
        position: fixed;
        inset: 0;
        z-index: -1;
        background-color: #0B0F19;
        overflow: hidden;
    }
    .pwd-bg .orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.15;
    }
    .pwd-bg .orb-1 { width: 400px; height: 400px; background: #F59E0B; top: -100px; right: -100px; animation: float 8s infinite alternate; }
    .pwd-bg .orb-2 { width: 300px; height: 300px; background: #8B5CF6; bottom: -50px; left: -50px; animation: float 10s infinite alternate-reverse; }
    
    @keyframes float {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-30px, 30px) scale(1.1); }
    }

    /* ====== الحاوية الأساسية ====== */
    .pwd-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 100px);
        padding: 20px;
        position: relative;
        z-index: 1;
    }

    /* ====== الكارت الزجاجي ====== */
    .pwd-card {
        width: 100%;
        max-width: 480px;
        background: rgba(17, 24, 39, 0.6);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 28px;
        padding: 45px 40px;
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.6);
        animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes cardIn {
        from { opacity: 0; transform: translateY(20px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ====== الهيدر ====== */
    .pwd-header {
        text-align: center;
        margin-bottom: 35px;
    }
    .pwd-icon {
        width: 72px; height: 72px;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.05));
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.2);
    }
    .pwd-icon svg { width: 32px; height: 32px; stroke: #F59E0B; }
    
    .pwd-header h1 { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 8px; }
    .pwd-header p { font-size: 0.95rem; color: #9CA3AF; }

    /* ====== حقول الإدخال ====== */
    .input-group { margin-bottom: 22px; }
    .input-group label {
        display: block; font-size: 0.9rem; font-weight: 700; color: #E5E7EB; margin-bottom: 8px;
    }
    
    .input-wrapper { position: relative; }
    .input-wrapper input {
        width: 100%;
        padding: 15px 48px 15px 50px; /* مساحة لليسار واليمين */
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        color: #fff;
        font-family: inherit; font-size: 1rem;
        transition: all 0.3s ease;
    }
    .input-wrapper input:focus {
        outline: none;
        border-color: #F59E0B;
        background: rgba(245, 158, 11, 0.03);
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    }
    .input-wrapper input::placeholder { color: #6B7280; }

    /* الأيقونات */
    .icon-right {
        position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
        width: 20px; height: 20px; stroke: #6B7280; pointer-events: none; transition: stroke 0.3s;
    }
    .input-wrapper input:focus ~ .icon-right { stroke: #F59E0B; }

    /* زر الإظهار / الإخفاء */
    .toggle-btn {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; padding: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .toggle-btn svg { width: 20px; height: 20px; stroke: #6B7280; transition: stroke 0.2s; }
    .toggle-btn:hover svg { stroke: #F59E0B; }

    /* ====== مؤشر قوة كلمة المرور ====== */
    .strength-container { margin-top: 10px; }
    .strength-bar-bg {
        height: 4px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;
    }
    .strength-bar {
        height: 100%; width: 0%; border-radius: 4px; transition: all 0.4s ease;
    }
    .strength-text {
        display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 700; margin-top: 6px;
    }
    
    /* ألوان القوة */
    .str-weak .strength-bar { background: #EF4444; width: 25%; }
    .str-fair .strength-bar { background: #F59E0B; width: 50%; }
    .str-good .strength-bar { background: #3B82F6; width: 75%; }
    .str-strong .strength-bar { background: #10B981; width: 100%; }
    
    .str-weak .strength-text span:last-child { color: #EF4444; }
    .str-fair .strength-text span:last-child { color: #F59E0B; }
    .str-good .strength-text span:last-child { color: #3B82F6; }
    .str-strong .strength-text span:last-child { color: #10B981; }

    /* ====== زر الحفظ ====== */
    .btn-save {
        width: 100%; padding: 16px;
        margin-top: 15px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        color: #000; border: none; border-radius: 14px;
        font-family: inherit; font-weight: 800; font-size: 1.05rem;
        cursor: pointer; transition: all 0.3s ease;
        box-shadow: 0 10px 20px -10px rgba(245, 158, 11, 0.5);
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 15px 30px -10px rgba(245, 158, 11, 0.7); }
    .btn-save:active { transform: scale(0.98); }
    .btn-save svg { width: 20px; height: 20px; stroke: #000; }

    /* الموبايل */
    @media (max-width: 576px) {
        .pwd-card { padding: 30px 20px; border-radius: 20px; }
        .pwd-header h1 { font-size: 1.4rem; }
        .input-wrapper input { padding: 14px 42px 14px 45px; font-size: 0.95rem; }
        .icon-right { right: 14px; }
        .toggle-btn { left: 14px; }
    }
</style>

<div class="pwd-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="pwd-container">
    <div class="pwd-card">
        
        <div class="pwd-header">
            <div class="pwd-icon">
                <!-- أيقونة درع -->
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
            <h1>تغيير كلمة المرور</h1>
            <p>تأكد من اختيار كلمة مرور قوية لحماية حسابك</p>
        </div>

        <form method="POST" autocomplete="off">
            <?= csrf_field() ?>
            
            <!-- الحقل الأول: كلمة المرور الحالية -->
            <div class="input-group">
                <label>كلمة المرور الحالية</label>
                <div class="input-wrapper">
                    <input type="password" name="current_password" id="pwd1" required placeholder="أدخل كلمة المرور الحالية">
                    <button type="button" class="toggle-btn" onclick="togglePass('pwd1', this)">
                        <svg id="eye1" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <!-- أيقونة المفتاح -->
                    <svg class="icon-right" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                </div>
            </div>

            <!-- الحقل الثاني: كلمة المرور الجديدة -->
            <div class="input-group">
                <label>كلمة المرور الجديدة</label>
                <div class="input-wrapper">
                    <input type="password" name="new_password" id="pwd2" required minlength="8" placeholder="8 أحرف على الأقل" oninput="checkStrength(this.value)">
                    <button type="button" class="toggle-btn" onclick="togglePass('pwd2', this)">
                        <svg id="eye2" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <!-- أيقونة القفل -->
                    <svg class="icon-right" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <!-- مؤشر القوة -->
                <div class="strength-container" id="strengthContainer">
                    <div class="strength-bar-bg">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text">
                        <span>قوة كلمة المرور</span>
                        <span id="strengthText">-</span>
                    </div>
                </div>
            </div>

            <!-- الحقل الثالث: تأكيد كلمة المرور -->
            <div class="input-group">
                <label>تأكيد كلمة المرور الجديدة</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="pwd3" required minlength="8" placeholder="أعد إدخال كلمة المرور">
                    <button type="button" class="toggle-btn" onclick="togglePass('pwd3', this)">
                        <svg id="eye3" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <!-- أيقونة الصح -->
                    <svg class="icon-right" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                تحديث كلمة المرور
            </button>
        </form>
    </div>
</div>

<script>
    // دالة إظهار وإخفاء الباسورد مع تغيير شكل العين
    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const eyeIcon = btn.querySelector('svg');
        
        if (input.type === 'password') {
            input.type = 'text';
            // شكل العين المشطوبة (إخفاء)
            eyeIcon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            `;
        } else {
            input.type = 'password';
            // شكل العين المفتوحة (إظهار)
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            `;
        }
    }

    // دالة حساب قوة كلمة المرور
    function checkStrength(password) {
        const container = document.getElementById('strengthContainer');
        const text = document.getElementById('strengthText');
        
        // إزالة كل الكلاسات القديمة
        container.className = 'strength-container';
        
        if (password.length === 0) {
            text.textContent = '-';
            return;
        }

        let strength = 0;
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]+/)) strength += 1;
        if (password.match(/[A-Z]+/)) strength += 1;
        if (password.match(/[0-9]+/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;

        // تعيين اللون والنص بناءً على النتيجة
        if (strength <= 2) {
            container.classList.add('str-weak');
            text.textContent = 'ضعيفة';
        } else if (strength === 3) {
            container.classList.add('str-fair');
            text.textContent = 'متوسطة';
        } else if (strength === 4) {
            container.classList.add('str-good');
            text.textContent = 'جيدة';
        } else if (strength === 5) {
            container.classList.add('str-strong');
            text.textContent = 'قوية جداً';
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>