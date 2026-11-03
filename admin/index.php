<?php
require __DIR__ . '/includes/auth.php';
$pageTitle = 'الرئيسية';

// === جلب الإحصائيات ===
$totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$newBookings   = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='new'")->fetchColumn();
$totalGallery  = (int)$pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
$totalServices = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

// نسبة النمو
$thisMonth = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
$lastMonth = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE MONTH(created_at)=MONTH(NOW() - INTERVAL 1 MONTH) AND YEAR(created_at)=YEAR(NOW() - INTERVAL 1 MONTH)")->fetchColumn();
$growth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : ($thisMonth > 0 ? 100 : 0);
$growthClass = $growth >= 0 ? 'up' : 'down';

// Chart Data (7 Days)
$chart7 = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM bookings WHERE created_at >= (NOW() - INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY d")->fetchAll();
$labels7 = []; $data7 = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $labels7[] = date('M d', strtotime($day));
    $data7[] = 0;
    foreach ($chart7 as $r) if ($r['d'] === $day) { $data7[count($data7)-1] = (int)$r['c']; break; }
}

// Chart Data (Status)
$statusChart = $pdo->query("SELECT status, COUNT(*) c FROM bookings GROUP BY status")->fetchAll();
$statusLabels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'confirmed' => 'مؤكد', 'completed' => 'منتهي', 'cancelled' => 'ملغي'];
$chartStatusLabels = array_map(fn($s) => $statusLabels[$s['status']] ?? $s['status'], $statusChart);

// Chart Data (Types)
$typeChart = $pdo->query("SELECT event_type, COUNT(*) c FROM bookings GROUP BY event_type ORDER BY c DESC LIMIT 5")->fetchAll();

// Recent Bookings
$recentBookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    /* =========================================
       Dashboard Specific Styles (Premium Dark)
       ========================================= */
    .dashboard-wrapper { color: #F3F4F6; }

    /* Header */
    .dash-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; }
    .dash-greeting { font-size: 2.2rem; font-weight: 900; margin-bottom: 5px; background: linear-gradient(to left, #fff, #9CA3AF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .dash-date { color: #9CA3AF; font-size: 1rem; }
    .dash-header-actions { display: flex; gap: 12px; }

    /* Stats Grid */
    .dash-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 35px; }
    .dash-stat-card {
        background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px; padding: 25px; position: relative; overflow: hidden;
        display: flex; align-items: center; gap: 20px; transition: all 0.3s ease;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
        animation: fadeInUp 0.6s ease backwards;
    }
    .dash-stat-card:nth-child(2) { animation-delay: 0.1s; }
    .dash-stat-card:nth-child(3) { animation-delay: 0.2s; }
    .dash-stat-card:nth-child(4) { animation-delay: 0.3s; }
    .dash-stat-card:hover { transform: translateY(-5px); border-color: rgba(245, 158, 11, 0.4); box-shadow: 0 15px 30px -10px rgba(245, 158, 11, 0.15); }
    
    .stat-icon-box { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 2; }
    .stat-icon-box svg { width: 28px; height: 28px; }
    
    .bg-orange { background: rgba(245, 158, 11, 0.15); color: #F59E0B; }
    .bg-red { background: rgba(239, 68, 68, 0.15); color: #EF4444; }
    .bg-blue { background: rgba(59, 130, 246, 0.15); color: #3B82F6; }
    .bg-green { background: rgba(16, 185, 129, 0.15); color: #10B981; }
    
    .stat-data { z-index: 2; position: relative; }
    .stat-title { color: #9CA3AF; font-size: 0.9rem; font-weight: 700; display: block; margin-bottom: 5px; }
    .stat-value { font-size: 2rem; font-weight: 900; line-height: 1; margin-bottom: 5px; color: #fff; }
    .stat-trend { font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 4px; }
    .stat-trend.up { color: #10B981; }
    .stat-trend.down { color: #EF4444; }
    .stat-trend.neutral { color: #9CA3AF; }
    
    .stat-glow { position: absolute; left: -20px; top: -20px; width: 100px; height: 100px; filter: blur(40px); opacity: 0.15; border-radius: 50%; z-index: 1; }
    .bg-orange ~ .stat-glow { background: #F59E0B; }
    .bg-red ~ .stat-glow { background: #EF4444; }
    .bg-blue ~ .stat-glow { background: #3B82F6; }
    .bg-green ~ .stat-glow { background: #10B981; }

    /* Main Grid */
    .dash-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
    .dash-col-main { display: flex; flex-direction: column; gap: 25px; }
    .dash-col-side { display: flex; flex-direction: column; gap: 25px; }
    .dash-sub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    
    .dash-card {
        background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.2);
        animation: fadeInUp 0.8s ease backwards;
    }
    .dash-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .dash-card-header h3 { font-size: 1.1rem; font-weight: 800; color: #F3F4F6; }
    .dash-link { font-size: 0.85rem; color: #F59E0B; font-weight: 700; text-decoration: none; transition: color 0.2s; }
    .dash-link:hover { color: #D97706; }

    /* Recent List */
    .recent-list { display: flex; flex-direction: column; gap: 12px; }
    .recent-item { display: flex; align-items: center; gap: 15px; padding: 12px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid transparent; transition: all 0.2s ease; }
    .recent-item:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); transform: translateX(-5px); }
    .recent-avatar { width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #F59E0B, #D97706); color: #000; font-weight: 900; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); }
    .recent-info { flex: 1; }
    .recent-info h4 { font-size: 0.95rem; font-weight: 800; margin-bottom: 3px; color: #fff; }
    .recent-info p { font-size: 0.8rem; color: #9CA3AF; }
    .recent-status .badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 6px; }
    
    /* Quick Actions */
    .quick-actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .quick-btn {
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
        padding: 15px; border-radius: 12px; text-align: center; font-weight: 700; font-size: 0.9rem;
        color: #F3F4F6; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; gap: 8px;
    }
    .quick-btn svg { width: 24px; height: 24px; stroke: #9CA3AF; transition: stroke 0.2s; }
    .quick-btn:hover { background: rgba(245, 158, 11, 0.1); border-color: #F59E0B; color: #F59E0B; transform: translateY(-2px); }
    .quick-btn:hover svg { stroke: #F59E0B; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }

    /* Badges for statuses */
    .badge.new { background: rgba(245, 158, 11, 0.15); color: #F59E0B; }
    .badge.confirmed { background: rgba(16, 185, 129, 0.15); color: #10B981; }
    .badge.cancelled { background: rgba(239, 68, 68, 0.15); color: #EF4444; }
    .badge.contacted { background: rgba(139, 92, 246, 0.15); color: #A78BFA; }
    .badge.completed { background: rgba(16, 185, 129, 0.2); color: #10B981; }

    /* =========================================
       Media Queries (Responsive Fixes)
       ========================================= */
    @media (max-width: 1200px) {
        .dash-grid { grid-template-columns: 1fr; }
        .dash-sub-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 768px) {
        .dash-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .dash-header-actions { width: 100%; display: flex; gap: 10px; }
        .dash-header-actions .btn { flex: 1; text-align: center; justify-content: center; }
        .dash-sub-grid { grid-template-columns: 1fr; }
        .dash-stats { grid-template-columns: 1fr; }
        .dash-greeting { font-size: 1.8rem; }
        .stat-value { font-size: 1.8rem; }
    }

    @media (max-width: 480px) {
        .quick-actions-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard-wrapper">
    
    <!-- Header -->
    <div class="dash-header">
        <div>
            <h1 class="dash-greeting">أهلاً بك، <?= e($_SESSION['admin_user']) ?> 👋</h1>
            <p class="dash-date">إليك ملخص أداء الموقع اليوم: <?= date('l, F j, Y') ?></p>
        </div>
        <div class="dash-header-actions">
            <a href="bookings.php" class="btn btn-ghost">عرض الحجوزات</a>
            <a href="services.php" class="btn btn-primary">إضافة خدمة +</a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="dash-stats">
        <!-- Card 1 -->
        <div class="dash-stat-card">
            <div class="stat-icon-box bg-orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div class="stat-data">
                <span class="stat-title">إجمالي الحجوزات</span>
                <h2 class="stat-value"><?= $totalBookings ?></h2>
                <span class="stat-trend <?= $growthClass ?>">
                    <?= $growth >= 0 ? '↑' : '↓' ?> <?= abs($growth) ?>% هذا الشهر
                </span>
            </div>
            <div class="stat-glow"></div>
        </div>
        
        <!-- Card 2 -->
        <div class="dash-stat-card">
            <div class="stat-icon-box bg-red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="stat-data">
                <span class="stat-title">حجوزات جديدة</span>
                <h2 class="stat-value"><?= $newBookings ?></h2>
                <span class="stat-trend neutral">تحتاج إلى مراجعة</span>
            </div>
            <div class="stat-glow"></div>
        </div>
        
        <!-- Card 3 -->
        <div class="dash-stat-card">
            <div class="stat-icon-box bg-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </div>
            <div class="stat-data">
                <span class="stat-title">وسائط المعرض</span>
                <h2 class="stat-value"><?= $totalGallery ?></h2>
                <span class="stat-trend neutral">صورة وفيديو</span>
            </div>
            <div class="stat-glow"></div>
        </div>
        
        <!-- Card 4 -->
        <div class="dash-stat-card">
            <div class="stat-icon-box bg-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-data">
                <span class="stat-title">الخدمات النشطة</span>
                <h2 class="stat-value"><?= $totalServices ?></h2>
                <span class="stat-trend neutral">خدمة معروضة</span>
            </div>
            <div class="stat-glow"></div>
        </div>
    </div>

    <!-- Charts & Lists -->
    <div class="dash-grid">
        
        <!-- Main Column -->
        <div class="dash-col-main">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>📈 نظرة عامة على الحجوزات (آخر 7 أيام)</h3>
                </div>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
            
            <div class="dash-sub-grid">
                <div class="dash-card">
                    <div class="dash-card-header"><h3>🎯 حالة الحجوزات</h3></div>
                    <div style="position: relative; height: 220px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="dash-card-header"><h3>🎉 أنواع المناسبات</h3></div>
                    <div style="position: relative; height: 220px; width: 100%;">
                        <canvas id="typesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Side Column -->
        <div class="dash-col-side">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>📋 أحدث الحجوزات</h3>
                    <a href="bookings.php" class="dash-link">عرض الكل ←</a>
                </div>
                <div class="recent-list">
                    <?php if(empty($recentBookings)): ?>
                        <p style="color: #9CA3AF; text-align: center; padding: 20px;">لا توجد حجوزات حديثة</p>
                    <?php else: ?>
                        <?php foreach($recentBookings as $b): $initial = mb_substr($b['name'], 0, 1); ?>
                        <div class="recent-item">
                            <div class="recent-avatar"><?= e($initial) ?></div>
                            <div class="recent-info">
                                <h4><?= e($b['name']) ?></h4>
                                <p><?= e($b['event_type']) ?> • <?= date('M d', strtotime($b['created_at'])) ?></p>
                            </div>
                            <div class="recent-status">
                                <span class="badge <?= e($b['status']) ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dash-card">
                <div class="dash-card-header"><h3>⚡ إجراءات سريعة</h3></div>
                <div class="quick-actions-grid">
                    <a href="services.php" class="quick-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        إضافة خدمة
                    </a>
                    <a href="gallery.php" class="quick-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        رفع صور
                    </a>
                    <a href="articles.php" class="quick-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        مقال جديد
                    </a>
                    <a href="settings.php" class="quick-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        الإعدادات
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // إعدادات Chart.js العامة
    Chart.defaults.color = 'rgba(156, 163, 175, 0.8)'; 
    Chart.defaults.font.family = 'Tajawal';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';

    const tooltipConfig = { backgroundColor: '#111827', titleColor: '#F3F4F6', bodyColor: '#9CA3AF', borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1, padding: 12, cornerRadius: 8, displayColors: false };

    // 1. الرسم البياني الخطي (Main Line Chart)
    const ctxMain = document.getElementById('mainChart').getContext('2d');
    const gradientMain = ctxMain.createLinearGradient(0, 0, 0, 400);
    gradientMain.addColorStop(0, 'rgba(245, 158, 11, 0.5)');
    gradientMain.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

    new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels7) ?>,
            datasets: [{ label: 'الحجوزات', data: <?= json_encode($data7) ?>, borderColor: '#F59E0B', backgroundColor: gradientMain, borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#0B0F19', pointBorderColor: '#F59E0B', pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 8 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipConfig }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, padding: 10 }, grid: { drawBorder: false, color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false }, ticks: { padding: 10 } } } }
    });

    // 2. الرسم البياني الدائري (Status Doughnut Chart)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: <?= json_encode($chartStatusLabels, JSON_UNESCAPED_UNICODE) ?>, datasets: [{ data: <?= json_encode(array_map('intval', array_column($statusChart, 'c'))) ?>, backgroundColor: ['#F59E0B', '#8B5CF6', '#10B981', '#059669', '#EF4444'], borderWidth: 0, hoverOffset: 10 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' } }, tooltip: tooltipConfig } }
    });

    // 3. الرسم البياني بالأعمدة (Types Bar Chart)
    const ctxTypes = document.getElementById('typesChart').getContext('2d');
    const gradientTypes = ctxTypes.createLinearGradient(0, 0, 0, 300);
    gradientTypes.addColorStop(0, 'rgba(59, 130, 246, 0.9)');
    gradientTypes.addColorStop(1, 'rgba(59, 130, 246, 0.2)');

    new Chart(ctxTypes, {
        type: 'bar',
        data: { labels: <?= json_encode(array_column($typeChart, 'event_type'), JSON_UNESCAPED_UNICODE) ?>, datasets: [{ data: <?= json_encode(array_map('intval', array_column($typeChart, 'c'))) ?>, backgroundColor: gradientTypes, borderRadius: 8, borderSkipped: false, barThickness: 20 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipConfig }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } } }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>