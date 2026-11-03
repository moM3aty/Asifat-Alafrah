<?php
// admin/includes/header.php
if (!defined('SITE_URL')) die('Access Denied');
$current = basename($_SERVER['PHP_SELF'], '.php');
$flashes = get_flashes();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'لوحة التحكم') ?> — عاصفة الأفراح</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <button class="mobile-menu-btn" id="mobMenu">
        <svg style="width:24px; height:24px; min-width:24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-layout">
        
        <aside class="sidebar" id="sidebar">
            <div class="side-brand">
                <span class="mark">⛺</span>
                <div>
                    <strong>عاصفة الأفراح</strong>
                    <small>Premium Dashboard</small>
                </div>
            </div>
            
            <nav>
                <a href="index.php" class="<?= $current === 'index' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    الرئيسية
                </a>
                
                <a href="bookings.php" class="<?= $current === 'bookings' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    الحجوزات
                </a>
                
                <a href="services.php" class="<?= $current === 'services' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    الخدمات
                </a>
                
                <a href="occasions.php" class="<?= $current === 'occasions' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    المناسبات
                </a>
                
                <a href="why-us.php" class="<?= $current === 'why-us' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                    لماذا نحن
                </a>
                
                <a href="process.php" class="<?= $current === 'process' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    خطوات الحجز
                </a>
                
                <a href="articles.php" class="<?= $current === 'articles' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    الدليل
                </a>
                
                <a href="faqs.php" class="<?= $current === 'faqs' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    الأسئلة الشائعة
                </a>
                
                <a href="gallery.php" class="<?= $current === 'gallery' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    المعرض
                </a>
                
                <a href="settings.php" class="<?= $current === 'settings' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    الإعدادات
                </a>
                
                <hr style="border:none; border-top:1px solid rgba(255,255,255,0.08); margin:10px 0;">
                
                <a href="change-password.php" class="<?= $current === 'change-password' ? 'active' : '' ?>">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    تغيير كلمة المرور
                </a>
                
                <a href="<?= SITE_URL ?>" target="_blank">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    عرض الموقع
                </a>
                
                <a href="logout.php" class="danger">
                    <svg style="width:22px; height:22px; min-width:22px; flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    تسجيل الخروج
                </a>
            </nav>
            
            <div class="side-footer">
                مرحباً، <b><?= e($_SESSION['admin_user'] ?? 'Admin') ?></b>
            </div>
        </aside>
        
        <main class="main-content">
            <?php if (!empty($flashes)): ?>
                <script id="flash-data" type="application/json">
                    <?= json_encode($flashes, JSON_UNESCAPED_UNICODE) ?>
                </script>
            <?php endif; ?>