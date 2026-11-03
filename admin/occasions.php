<?php
require __DIR__ . '/includes/auth.php';
$pageTitle = 'المناسبات';

// حذف مناسبة
if (isset($_GET['delete']) && csrf_check($_GET['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM occasions WHERE id=?")->execute([(int)$_GET['delete']]);
    flash('success', 'تم حذف المناسبة بنجاح'); 
    redirect('occasions.php');
}

// إضافة أو تعديل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? '')) {
    $data = [
        trim($_POST['icon'] ?? ''),
        trim($_POST['title'] ?? ''),
        trim($_POST['description'] ?? ''),
        (int)($_POST['sort_order'] ?? 0),
        isset($_POST['is_active']) ? 1 : 0,
    ];
    
    if (!empty($_POST['id'])) {
        $data[] = (int)$_POST['id'];
        $pdo->prepare("UPDATE occasions SET icon=?,title=?,description=?,sort_order=?,is_active=? WHERE id=?")->execute($data);
        flash('success', 'تم تحديث المناسبة بنجاح');
    } else {
        $pdo->prepare("INSERT INTO occasions (icon,title,description,sort_order,is_active) VALUES (?,?,?,?,?)")->execute($data);
        flash('success', 'تمت إضافة المناسبة بنجاح');
    }
    redirect('occasions.php');
}

// جلب بيانات المناسبة للتعديل
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM occasions WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}

$rows = $pdo->query("SELECT * FROM occasions ORDER BY sort_order, id")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    /* =========================================
       Occasions Page Styles (Premium Dark)
       ========================================= */
    .occasions-wrapper { color: #F3F4F6; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { font-size: 2rem; font-weight: 900; margin: 0; }
    .page-header p { color: #9CA3AF; margin: 5px 0 0 0; font-size: 0.95rem; }

    /* Form Card */
    .form-card {
        background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px; padding: 30px; margin-bottom: 30px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }
    .form-card.edit-mode { border-color: rgba(245, 158, 11, 0.4); box-shadow: 0 0 20px rgba(245, 158, 11, 0.1); }
    
    .form-card h2 { font-size: 1.3rem; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: #fff; }
    .form-card h2 svg { width: 24px; height: 24px; stroke: #F59E0B; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group.full { grid-column: 1 / -1; }
    
    .form-group label { font-size: 0.9rem; font-weight: 700; color: #E5E7EB; }
    
    .form-control {
        width: 100%; padding: 14px 18px; border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(0, 0, 0, 0.3);
        color: #F3F4F6; font-family: inherit; font-size: 0.95rem; transition: all 0.3s;
    }
    .form-control:focus { outline: none; border-color: #F59E0B; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15); background: rgba(0, 0, 0, 0.5); }
    .form-control::placeholder { color: #6B7280; }
    
    textarea.form-control { min-height: 100px; resize: vertical; }

    /* Checkbox Toggle */
    .switch-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 10px; }
    .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #10B981; }
    input:checked + .slider:before { transform: translateX(22px); }
    .switch-label { font-size: 0.9rem; font-weight: 700; color: #E5E7EB; }

    /* Form Actions */
    .form-actions { display: flex; gap: 15px; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08); }

    /* Table Card */
    .table-card {
        background: #111827; border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
    }
    .table-header { padding: 20px 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1); }
    .table-header h3 { font-size: 1.1rem; font-weight: 800; color: #fff; margin: 0; }
    
    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table.tbl { width: 100%; border-collapse: collapse; min-width: 750px; }
    table.tbl th { padding: 16px 20px; text-align: right; color: #9CA3AF; font-weight: 800; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
    table.tbl td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); color: #F3F4F6; font-size: 0.95rem; vertical-align: middle; }
    table.tbl tr:last-child td { border-bottom: none; }
    table.tbl tr { transition: all 0.2s; }
    table.tbl tr:hover { background: rgba(255,255,255,0.02); }

    /* Badges */
    .badge { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; }
    .badge.active { background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .badge.inactive { background: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.3); }

    /* Action Buttons */
    .action-btns { display: flex; gap: 8px; }
    .action-btn {
        width: 34px; height: 34px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.2s; border: 1px solid transparent; text-decoration: none;
    }
    .action-btn svg { width: 16px; height: 16px; transition: all 0.2s; }
    
    .action-edit { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
    .action-edit:hover { background: rgba(59, 130, 246, 0.2); border-color: #3B82F6; }
    
    .action-delete { background: rgba(239, 68, 68, 0.1); color: #EF4444; }
    .action-delete:hover { background: rgba(239, 68, 68, 0.2); border-color: #EF4444; }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-card { padding: 20px; }
        .page-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="occasions-wrapper">
    
    <div class="page-header">
        <div>
            <h1>إدارة المناسبات</h1>
            <p>البطاقات الظاهرة في قسم "المناسبات" بالصفحة الرئيسية</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="form-card <?= $edit ? 'edit-mode' : '' ?>">
        <h2>
            <?php if($edit): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                تعديل المناسبة
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                إضافة مناسبة جديدة
            <?php endif; ?>
        </h2>
        
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>الأيقونة (إيموجي)</label>
                    <input type="text" name="icon" class="form-control" value="<?= e($edit['icon'] ?? '💍') ?>" maxlength="10" placeholder="مثال: 💍" required>
                </div>
                
                <div class="form-group">
                    <label>اسم المناسبة</label>
                    <input type="text" name="title" class="form-control" value="<?= e($edit['title'] ?? '') ?>" placeholder="مثال: الأفراح" required>
                </div>
                
                <div class="form-group full">
                    <label>الوصف</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="وصف مختصر للمناسبة..." required><?= e($edit['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>ترتيب العرض (1 = يظهر أولاً)</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= e($edit['sort_order'] ?? 0) ?>">
                </div>

                <div class="form-group">
                    <label>حالة المناسبة</label>
                    <label class="switch-wrapper">
                        <div class="switch">
                            <input type="checkbox" name="is_active" <?= !isset($edit['is_active']) || $edit['is_active'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </div>
                        <span class="switch-label">نشطة (تظهر في الموقع)</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                    <?= $edit ? 'حفظ التعديلات' : 'إضافة المناسبة' ?>
                </button>
                <?php if($edit): ?>
                    <a href="occasions.php" class="btn btn-ghost" style="padding: 12px 30px;">إلغاء</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-header">
            <h3>قائمة المناسبات (<?= count($rows) ?>)</h3>
        </div>
        
        <?php if(empty($rows)): ?>
            <div style="text-align: center; padding: 50px 20px; color: #9CA3AF;">
                <p>لا توجد مناسبات مضافة حالياً. ابدأ بإضافة مناسبة جديدة من الفورم بالأعلى.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>الأيقونة</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>الترتيب</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $r): ?>
                        <tr>
                            <td style="font-size: 1.5rem;"><?= e($r['icon']) ?></td>
                            <td><strong style="color: #fff;"><?= e($r['title']) ?></strong></td>
                            <td style="color: #9CA3AF; font-size: 0.9rem; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($r['description']) ?>
                            </td>
                            <td style="color: #9CA3AF; font-weight: 700;"><?= (int)$r['sort_order'] ?></td>
                            <td>
                                <span class="badge <?= $r['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $r['is_active'] ? 'نشطة' : 'مخفية' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?= $r['id'] ?>" class="action-btn action-edit" title="تعديل">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <a href="?delete=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" class="action-btn action-delete" data-confirm="هل أنت متأكد من حذف هذه المناسبة نهائياً؟" title="حذف">
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