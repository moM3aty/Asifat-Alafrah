<?php
require __DIR__ . '/includes/auth.php';
$pageTitle = 'مدير الوسائط';

if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);

// حذف
if (isset($_GET['delete']) && csrf_check($_GET['csrf'] ?? '')) {
    $s = $pdo->prepare("SELECT file_path FROM gallery WHERE id=?");
    $s->execute([(int)$_GET['delete']]);
    if ($r = $s->fetch()) {
        if ($r['file_path'] && file_exists(__DIR__ . '/../' . $r['file_path'])) {
            @unlink(__DIR__ . '/../' . $r['file_path']);
        }
    }
    $pdo->prepare("DELETE FROM gallery WHERE id=?")->execute([(int)$_GET['delete']]);
    flash('success','تم الحذف بنجاح'); redirect('gallery.php');
}

// رفع ملفات جديدة (يدعم الرفع المتعدد)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? '')) {
    if (!empty($_FILES['files']['tmp_name'][0])) {
        $count = count($_FILES['files']['name']);
        $uploaded = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $tmp = $_FILES['files']['tmp_name'][$i];
            $name = $_FILES['files']['name'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg','jpeg','png','webp','gif','mp4','webm'])) {
                $newName = bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($tmp, UPLOAD_DIR . $newName)) {
                    $type = in_array($ext, ['mp4','webm']) ? 'video' : 'image';
                    $pdo->prepare("INSERT INTO gallery (type, file_path, alt_text, sort_order) VALUES (?,?,?,?)")
                        ->execute([$type, 'uploads/' . $newName, 'وسائط المعرض', 0]);
                    $uploaded++;
                }
            }
        }
        if ($uploaded > 0) flash('success', "تم رفع $uploaded ملف بنجاح");
        else flash('error', 'فشل الرفع، تأكد من الصيغة');
    }
    redirect('gallery.php');
}

$rows = $pdo->query("SELECT * FROM gallery ORDER BY id DESC")->fetchAll();
include __DIR__ . '/includes/header.php';
?>

<style>
    /* ====== تنسيقات صفحة المعرض ====== */
    .upload-zone {
        border: 2px dashed rgba(255,255,255,0.1);
        background: rgba(0,0,0,0.2);
        padding: 40px;
        text-align: center;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    .upload-zone:hover {
        border-color: #F59E0B;
        background: rgba(245, 158, 11, 0.05);
    }
    .upload-zone .icon {
        font-size: 3rem;
        margin-bottom: 15px;
        display: block;
    }
    .upload-zone h3 { font-size: 1.2rem; font-weight: 800; color: #F3F4F6; margin-bottom: 5px; }
    .upload-zone p { color: #9CA3AF; font-size: 0.9rem; }

    /* شبكة الوسائط */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }

    .media-card {
        background: #111827;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
    }
    .media-card:hover {
        border-color: rgba(245, 158, 11, 0.4);
        box-shadow: 0 10px 25px -10px rgba(0,0,0,0.5);
        transform: translateY(-3px);
    }

    .media-preview {
        height: 180px;
        background: #000;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .media-preview img, 
    .media-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    
    .media-card:hover .media-preview img,
    .media-card:hover .media-preview video {
        transform: scale(1.05);
    }

    /* تأثيرات الفيديو */
    .video-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.3s;
        pointer-events: none;
    }
    
    .play-icon {
        width: 50px;
        height: 50px;
        background: rgba(245, 158, 11, 0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 1.2rem;
        padding-left: 4px; /* لضبط شكل المثلث */
        box-shadow: 0 0 20px rgba(245,158,11,0.5);
    }
    
    .media-card:hover .video-overlay {
        opacity: 0; /* إخفاء الأوفرلاي عند الهوفر */
    }

    /* بيانات الوسائط (الأسفل) */
    .media-info {
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #1F2937;
        border-top: 1px solid rgba(255,255,255,0.05);
    }
    
    .media-type {
        font-size: 0.8rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .media-type.img { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
    .media-type.vid { background: rgba(245, 158, 11, 0.15); color: #F59E0B; }

    .media-actions { display: flex; gap: 6px; }
</style>

<div class="topbar">
    <div class="topbar-title">
        <h1>مدير الوسائط</h1>
        <p>إدارة صور وفيديوهات المعرض</p>
    </div>
</div>

<div class="card">
    <h2>رفع ملفات جديدة</h2>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
            <span class="icon">☁️</span>
            <h3>اسحب وأفلت الملفات هنا</h3>
            <p>أو اضغط لاختيار الملفات (يدعم الصور والفيديو)</p>
            <input type="file" name="files[]" id="fileInput" multiple accept="image/*,video/*" style="display: none;" onchange="document.getElementById('fileCount').innerText = this.files.length + ' ملفات تم اختيارها'">
        </div>
        
        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span id="fileCount" style="color: #F59E0B; font-weight: bold;"></span>
            <button type="submit" class="btn btn-primary">بدء الرفع</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>الوسائط الحالية (<?= count($rows) ?>)</h2>
    
    <?php if(empty($rows)): ?>
        <p style="text-align:center; color:#9CA3AF; padding:40px;">لا توجد وسائط بعد.</p>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach($rows as $r): 
                $src = SITE_URL . '/' . $r['file_path'];
                $isVideo = $r['type'] === 'video';
            ?>
            <div class="media-card">
                
                <div class="media-preview" onclick="window.open('<?= e($src) ?>', '_blank')">
                    <?php if($isVideo): ?>
                        <!-- الفيديو: بيشتغل تلقائي بدون صوت عند الهوفر -->
                        <video 
                            class="hover-video" 
                            src="<?= e($src) ?>" 
                            muted 
                            loop 
                            playsinline 
                            preload="metadata">
                        </video>
                        <div class="video-overlay">
                            <div class="play-icon">▶</div>
                        </div>
                    <?php else: ?>
                        <img src="<?= e($src) ?>" loading="lazy" alt="صورة">
                    <?php endif; ?>
                </div>
                
                <div class="media-info">
                    <span class="media-type <?= $isVideo ? 'vid' : 'img' ?>">
                        <?= $isVideo ? '🎬 فيديو' : '🖼️ صورة' ?>
                    </span>
                    <div class="media-actions">
                        <a href="<?= e($src) ?>" target="_blank" class="btn btn-sm btn-ghost" title="فتح في نافذة جديدة">👁️</a>
                        <a href="?delete=<?= $r['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-sm btn-danger" data-confirm="تأكيد حذف هذا الملف نهائياً؟" title="حذف">🗑</a>
                    </div>
                </div>
                
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // سكريبت تشغيل الفيديو عند تحريك الماوس عليه
    document.querySelectorAll('.media-preview').forEach(preview => {
        const video = preview.querySelector('video.hover-video');
        
        if (video) {
            // عند دخول الماوس: تشغيل الفيديو
            preview.addEventListener('mouseenter', () => {
                video.play().catch(e => console.log("المتصفح منع التشغيل التلقائي", e));
            });
            
            // عند خروج الماوس: إيقاف الفيديو وإرجاعه للبداية
            preview.addEventListener('mouseleave', () => {
                video.pause();
                video.currentTime = 0;
            });
        }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>