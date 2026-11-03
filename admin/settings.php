<?php
require __DIR__ . '/includes/auth.php';
$pageTitle = 'إعدادات الموقع';

$settingsMap = [
    'hero' => [
        'title' => '🖼️ القسم الرئيسي (Hero)',
        'fields' => [
            'hero_kicker' => 'السطر العلوي الصغير (Kicker)',
            'hero_title'  => 'العنوان الرئيسي',
            'hero_accent' => 'الجزء الملون من العنوان (Accent)',
            'hero_desc'   => 'وصف الهيرو (Textarea)',
        ]
    ],
    'about' => [
        'title' => 'ℹ️ من نحن (About)',
        'fields' => [
            'about_title' => 'عنوان القسم',
            'about_p1'    => 'الفقرة الأولى (Textarea)',
            'about_p2'    => 'الفقرة الثانية (Textarea)',
        ]
    ],
    'contact' => [
        'title' => '📞 بيانات التواصل',
        'fields' => [
            'phone'    => 'رقم الهاتف (للعرض)',
            'whatsapp' => 'رقم الواتساب (مثال: 9665xxxx)',
        ]
    ],
    'footer' => [
        'title' => '🔻 الفوتر (Footer)',
        'fields' => [
            'footer_desc' => 'وصف المؤسسة في الفوتر',
            'footer_copy' => 'نص حقوق النشر (Copyright)',
        ]
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? '')) {
    $upd = $pdo->prepare("INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)");
    foreach ($_POST as $key => $val) {
        if ($key !== 'csrf') {
            $upd->execute([$key, trim($val)]);
        }
    }
    flash('success', 'تم حفظ الإعدادات بنجاح');
    redirect('settings.php');
}

include __DIR__ . '/includes/header.php';
?>

<style>
    /* فرض الدارك مود على الصفحة دي بالكامل */
    .settings-page-dark { color: #F3F4F6; }
    
    .settings-tabs { 
        display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.08); 
        margin-bottom: 30px; padding-bottom: 15px; overflow-x: auto; -webkit-overflow-scrolling: touch;
    }
    .settings-tabs::-webkit-scrollbar { height: 4px; }
    .settings-tabs::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
    
    .settings-tab { 
        background: transparent; border: none; color: #9CA3AF; 
        font-family: inherit; font-weight: 700; font-size: 1rem; 
        padding: 10px 24px; cursor: pointer; border-radius: 10px; 
        transition: all 0.3s; white-space: nowrap;
    }
    .settings-tab:hover { background: rgba(255,255,255,0.05); color: #F3F4F6; }
    .settings-tab.active { background: linear-gradient(135deg, #F59E0B, #D97706); color: #000; box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
    
    .tab-pane { display: none; animation: fadeIn 0.4s ease; }
    .tab-pane.active { display: block; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    
    .settings-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); }

    /* تظبيط الحقول جوه الصفحة دي */
    .settings-page-dark input[type="text"], 
    .settings-page-dark textarea {
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #F3F4F6 !important;
    }
    .settings-page-dark input[type="text"]:focus, 
    .settings-page-dark textarea:focus {
        border-color: #F59E0B !important;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15) !important;
    }
    .settings-page-dark label.fld { color: #F3F4F6; }
</style>

<div class="settings-page-dark">
    <div class="topbar">
        <div class="topbar-title">
            <h1 style="color: #F3F4F6;">إعدادات الموقع</h1>
            <p style="color: #9CA3AF;">تحكم في كل النصوص والبيانات الأساسية التي تظهر للزوار</p>
        </div>
    </div>

    <div class="card" style="background: rgba(31, 41, 55, 0.6); border: 1px solid rgba(255,255,255,0.08);">
        <form method="POST">
            <?= csrf_field() ?>
            
            <div class="settings-tabs" id="settingsTabs">
                <?php $i=0; foreach($settingsMap as $key => $group): ?>
                    <button type="button" class="settings-tab <?= $i===0?'active':'' ?>" data-target="tab-<?= $key ?>">
                        <?= $group['title'] ?>
                    </button>
                <?php $i++; endforeach; ?>
            </div>

            <?php $i=0; foreach($settingsMap as $key => $group): ?>
                <div class="tab-pane <?= $i===0?'active':'' ?>" id="tab-<?= $key ?>">
                    <div class="form-grid">
                        <?php foreach($group['fields'] as $fkey => $flabel): 
                            $isArea = in_array($fkey, ['hero_desc','about_p1','about_p2','footer_desc']);
                            $colSpan = $isArea ? '1/-1' : 'auto';
                        ?>
                            <label class="fld" style="grid-column:<?= $colSpan ?>;">
                                <?= $flabel ?>
                                <?php if($isArea): ?>
                                    <textarea name="<?= $fkey ?>" rows="4"><?= e(get_setting($fkey)) ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?= $fkey ?>" value="<?= e(get_setting($fkey)) ?>">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php $i++; endforeach; ?>

            <div class="settings-actions">
                <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-size: 1.05rem;">حفظ جميع التغييرات</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.settings-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.settings-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.target).classList.add('active');
        });
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>