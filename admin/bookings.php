<?php
require __DIR__ . '/includes/auth.php';
$pageTitle = 'الحجوزات';

// تحديث الحالة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? '')) {
    $allowed = ['new','contacted','confirmed','completed','cancelled'];
    $st = in_array($_POST['status'] ?? '', $allowed, true) ? $_POST['status'] : 'new';
    $pdo->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$st, (int)$_POST['id']]);
    flash('success', 'تم تحديث حالة الحجز بنجاح'); 
    redirect('bookings.php' . (!empty($_GET['status']) ? '?status=' . $_GET['status'] : ''));
}

// حذف حجز
if (isset($_GET['delete']) && csrf_check($_GET['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM bookings WHERE id=?")->execute([(int)$_GET['delete']]);
    flash('success', 'تم حذف الحجز بنجاح'); 
    redirect('bookings.php');
}

// الفلترة
$filter = $_GET['status'] ?? '';
$validFilters = ['new','contacted','confirmed','completed','cancelled'];
$sql = "SELECT * FROM bookings";
if ($filter && in_array($filter, $validFilters)) {
    $sql .= " WHERE status = ?";
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($filter ? [$filter] : []);
$rows = $stmt->fetchAll();

// حساب عدد الحجوزات لكل حالة
$counts = ['all' => 0];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM bookings GROUP BY status") as $r) {
    $counts[$r['status']] = $r['c'];
    $counts['all'] += $r['c'];
}

$statusLabels = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'confirmed' => 'مؤكد', 'completed' => 'منتهي', 'cancelled' => 'ملغي'];

include __DIR__ . '/includes/header.php';
?>

<style>
    /* =========================================
       Bookings Page Styles (Premium Dark)
       ========================================= */
    .bookings-wrapper { color: #F3F4F6; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { font-size: 2rem; font-weight: 900; margin: 0; }
    .page-header p { color: #9CA3AF; margin: 5px 0 0 0; font-size: 0.95rem; }

    /* Filter Tabs */
    .filter-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
    .filter-btn {
        padding: 10px 20px; border-radius: 12px;
        background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);
        color: #9CA3AF; font-weight: 700; transition: all 0.2s ease;
        text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;
    }
    .filter-btn:hover { background: rgba(255,255,255,0.05); color: #F3F4F6; transform: translateY(-2px); }
    .filter-btn.active { background: #F59E0B; color: #000; border-color: #F59E0B; box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
    
    .tab-count { 
        background: rgba(0,0,0,0.2); padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 800;
    }
    .filter-btn.active .tab-count { background: rgba(0,0,0,0.15); color: #000; }

    /* Table */
    .table-container {
        background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
    }
    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    
    table.tbl { width: 100%; border-collapse: collapse; min-width: 850px; }
    table.tbl th { 
        background: rgba(0,0,0,0.2); padding: 18px 20px; text-align: right; 
        color: #9CA3AF; font-weight: 800; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.08);
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    table.tbl td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); color: #F3F4F6; font-size: 0.95rem; vertical-align: middle; }
    table.tbl tr:last-child td { border-bottom: none; }
    table.tbl tr { transition: all 0.2s; }
    table.tbl tr:hover { background: rgba(255,255,255,0.02); }

    /* Badges */
    .badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; }
    .badge.new { background: rgba(245, 158, 11, 0.15); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.3); }
    .badge.contacted { background: rgba(139, 92, 246, 0.15); color: #A78BFA; border: 1px solid rgba(139, 92, 246, 0.3); }
    .badge.confirmed { background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .badge.completed { background: rgba(16, 185, 129, 0.2); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.4); }
    .badge.cancelled { background: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.3); }

    /* Select Dropdown */
    .status-form { display: inline-block; }
    .status-select {
        background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
        color: #F3F4F6; padding: 8px 12px; border-radius: 8px; font-family: inherit;
        font-size: 0.85rem; cursor: pointer; outline: none; transition: all 0.2s;
    }
    .status-select:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245,158,11,0.15); }

    /* Actions (SVG Icons) */
    .action-btns { display: flex; gap: 8px; }
    .action-btn {
        width: 34px; height: 34px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.2s; border: 1px solid transparent;
    }
    .action-btn svg { width: 16px; height: 16px; transition: all 0.2s; }
    
    .action-call { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
    .action-call:hover { background: rgba(59, 130, 246, 0.2); border-color: #3B82F6; }
    
    .action-delete { background: rgba(239, 68, 68, 0.1); color: #EF4444; }
    .action-delete:hover { background: rgba(239, 68, 68, 0.2); border-color: #EF4444; }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state svg { width: 60px; height: 60px; stroke: #4B5563; margin-bottom: 15px; }
    .empty-state h3 { font-size: 1.2rem; color: #F3F4F6; margin-bottom: 5px; }
    .empty-state p { color: #9CA3AF; font-size: 0.9rem; }

    /* =========================================
       Media Queries (Responsive)
       ========================================= */
    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; }
        .filter-tabs { justify-content: flex-start; gap: 8px; }
        .filter-btn { flex: 1 1 auto; justify-content: center; padding: 8px 12px; font-size: 0.85rem; }
        .table-container { border-radius: 12px; }
    }
</style>

<div class="bookings-wrapper">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>سجل الحجوزات</h1>
            <p>إدارة ومتابعة طلبات الحجز الواردة من الموقع</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="bookings.php" class="filter-btn <?= $filter === '' ? 'active' : '' ?>">
            الكل <span class="tab-count"><?= $counts['all'] ?></span>
        </a>
        <?php foreach($statusLabels as $key => $label): ?>
            <a href="?status=<?= $key ?>" class="filter-btn <?= $filter === $key ? 'active' : '' ?>">
                <?= $label ?> <span class="tab-count"><?= $counts[$key] ?? 0 ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="table-container">
        <?php if(empty($rows)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
                <h3>لا توجد حجوزات</h3>
                <p>لم يتم العثور على أي حجوزات في هذه القائمة حالياً.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم العميل</th>
                            <th>رقم الجوال</th>
                            <th>المناسبة</th>
                            <th>التاريخ</th>
                            <th>الضيوف</th>
                            <th>الحالة</th>
                            <th>تغيير الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $r): ?>
                        <tr>
                            <td style="color: #6B7280; font-weight: 700;">#<?= (int)$r['id'] ?></td>
                            <td><strong style="color: #fff;"><?= e($r['name']) ?></strong></td>
                            <td dir="ltr" style="text-align: right; color: #9CA3AF;"><?= e($r['phone'] ?: '-') ?></td>
                            <td><?= e($r['event_type']) ?></td>
                            <td><?= e($r['event_date'] ?: 'غير محدد') ?></td>
                            <td><?= (int)$r['guests'] ?></td>
                            <td>
                                <span class="badge <?= e($r['status']) ?>">
                                    <?= $statusLabels[$r['status']] ?? $r['status'] ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="status-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <?php foreach($statusLabels as $k => $lbl): ?>
                                            <option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <?php if($r['phone']): ?>
                                    <a href="tel:<?= e($r['phone']) ?>" class="action-btn action-call" title="اتصال">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" class="action-btn action-delete" data-confirm="هل أنت متأكد من حذف هذا الحجز نهائياً؟" title="حذف">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>