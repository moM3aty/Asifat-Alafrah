<?php
require __DIR__ . '/config.php';

// ============ جلب البيانات ============
$services  = $pdo->query("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
$occasions = $pdo->query("SELECT * FROM occasions WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
$whyUs     = $pdo->query("SELECT * FROM why_us WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
$steps     = $pdo->query("SELECT * FROM process_steps WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
$faqs      = $pdo->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
$articles  = $pdo->query("SELECT * FROM articles WHERE is_active=1 ORDER BY sort_order,id")->fetchAll();
$gallery   = $pdo->query("SELECT * FROM gallery WHERE is_active=1 ORDER BY sort_order,id DESC")->fetchAll();

// ============ الإعدادات ============
$sitePhone = get_setting('phone', '0564710254');
$siteWa    = get_setting('whatsapp', '966564710254');
$heroKicker = get_setting('hero_kicker') ?: 'مؤسسة عاصفة الأفراح للحفلات والمناسبات';
$heroTitle  = get_setting('hero_title')  ?: 'تأجير خيام لحفلاتك ومناسباتك';
$heroAccent = get_setting('hero_accent') ?: 'بعاصفة من الفرح';
$heroDesc   = get_setting('hero_desc')   ?: 'نوفر لك تأجير خيام وبيوت شعر ودفايات وكراسي وطاولات ومكيفات، كل ما تحتاجه لمناسبة مريحة وجميلة تحت سقف واحد.';

$stripTaj = fn($t) => trim(preg_replace('/^تأجير\s+/u', '', $t));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأجير خيام | عاصفة الأفراح للحفلات والمناسبات - بيوت شعر ودفايات وكراسي وطاولات ومكيفات</title>
    <meta name="description" content="تأجير خيام وبيوت شعر ودفايات وكراسي وطاولات ومكيفات للأفراح والحفلات والمناسبات من مؤسسة عاصفة الأفراح للحفلات والمناسبات. اتصال وواتساب: <?= e($sitePhone) ?>">
    <meta name="keywords" content="تأجير خيام, تاجير خيام, ايجار خيام, تأجير بيوت شعر, تأجير دفايات, تأجير كراسي, تأجير طاولات, تأجير مكيفات, عاصفة الأفراح, <?= e($sitePhone) ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:title" content="تأجير خيام | عاصفة الأفراح للحفلات والمناسبات">
    <meta property="og:description" content="تأجير خيام، بيوت شعر، دفايات، كراسي، طاولات ومكيفات لكل أنواع المناسبات.">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E⛺%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lalezar&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "مؤسسة عاصفة الأفراح للحفلات والمناسبات",
      "description": "تأجير خيام وبيوت شعر ودفايات وكراسي وطاولات ومكيفات للحفلات والمناسبات.",
      "telephone": "+<?= e($siteWa) ?>",
      "makesOffer": [
        <?php foreach($services as $i=>$s): ?>
        {"@type":"Offer","itemOffered":{"@type":"Service","name":"<?= e($s['title']) ?>"}}<?= $i < count($services)-1 ? ',' : '' ?>
        <?php endforeach; ?>
      ]
    }
    </script>
</head>

<body>

    <div class="cursor-glow" id="cursorGlow"></div>

    <!-- ===== HEADER ===== -->
    <header class="nav-wrap" id="siteHeader">
        <div class="nav-pill">
            <a href="#top" class="brand">
                <span class="brand-mark">⛺</span>
                <span class="brand-text"><strong>عاصفة الأفراح</strong><small>للحفلات والمناسبات</small></span>
            </a>
            <nav class="main-nav" id="mainNav">
                <a href="#services">خدماتنا</a>
                <a href="#tents">تأجير خيام</a>
                <a href="#gallery">أعمالنا</a>
                <a href="#planner">احسب تجهيزك</a>
                <a href="#guide">الدليل</a>
                <a href="#faq">الأسئلة</a>
                <a href="#contact">احجز</a>
            </nav>
            <a href="tel:+<?= e($siteWa) ?>" class="btn btn-primary nav-call">اتصل <?= e($sitePhone) ?></a>
            <button class="burger" id="burger" aria-label="فتح القائمة"><span></span><span></span><span></span></button>
        </div>
    </header>

    <!-- ===== HERO ===== -->
    <section class="hero" id="top">
        <div class="sky" aria-hidden="true">
            <div class="stars" id="stars"></div>
            <div class="moon"></div>
            <div class="gusts" id="gusts"></div>
            <div class="lanterns" id="lanterns"></div>
            <div class="lights" id="lights">
                <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                    <path d="M0,10 Q360,90 720,10 Q1080,90 1440,10" fill="none" stroke="rgba(255,244,224,.35)" stroke-width="2" />
                </svg>
            </div>
        </div>

        <div class="wrap hero-grid">
            <div class="hero-text">
                <p class="hero-kicker"><?= e($heroKicker) ?></p>
                <h1 class="hero-title">
                    <?= e($heroTitle) ?><br>
                    <span class="hero-accent"><?= e($heroAccent) ?></span>
                </h1>
                <p class="hero-desc"><?= nl2br(e($heroDesc)) ?></p>
                <div class="hero-actions">
                    <a href="https://wa.me/<?= e($siteWa) ?>?text=<?= urlencode('أريد الاستفسار عن تأجير خيام') ?>"
                        target="_blank" rel="noopener" class="btn btn-primary">احجز على واتساب</a>
                    <a href="tel:+<?= e($siteWa) ?>" class="btn btn-ghost">اتصال مباشر</a>
                </div>
                <div class="hero-chips">
                    <?php foreach($services as $s): ?><span><?= e($s['title']) ?></span><?php endforeach; ?>
                </div>
            </div>

            <div class="hero-tent" aria-hidden="true">
                <div class="tent-glow"></div>
                <svg class="tent" viewBox="0 0 520 440">
                    <defs>
                        <linearGradient id="gRose" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#F26B93" /><stop offset="1" stop-color="#B83264" />
                        </linearGradient>
                        <linearGradient id="gCream" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#FFF4E0" /><stop offset="1" stop-color="#EBCB98" />
                        </linearGradient>
                        <radialGradient id="gDoor" cx="50%" cy="85%" r="75%">
                            <stop offset="0" stop-color="#FFE9A8" /><stop offset=".55" stop-color="#FFB347" /><stop offset="1" stop-color="#E0701F" />
                        </radialGradient>
                    </defs>
                    <ellipse cx="260" cy="392" rx="235" ry="26" fill="rgba(0,0,0,.4)" />
                    <g>
                        <polygon points="260,70 40,360 115,360" fill="url(#gRose)" />
                        <polygon points="260,70 115,360 190,360" fill="url(#gCream)" />
                        <polygon points="260,70 190,360 260,360" fill="url(#gRose)" />
                        <polygon points="260,70 260,360 330,360" fill="url(#gCream)" />
                        <polygon points="260,70 330,360 405,360" fill="url(#gRose)" />
                        <polygon points="260,70 405,360 480,360" fill="url(#gCream)" />
                    </g>
                    <g class="valance">
                        <circle cx="58" cy="360" r="18" fill="#B83264" /><circle cx="94" cy="360" r="18" fill="#B83264" />
                        <circle cx="130" cy="360" r="18" fill="#EBCB98" /><circle cx="166" cy="360" r="18" fill="#EBCB98" />
                        <circle cx="202" cy="360" r="18" fill="#B83264" /><circle cx="238" cy="360" r="18" fill="#B83264" />
                        <circle cx="278" cy="360" r="18" fill="#EBCB98" /><circle cx="314" cy="360" r="18" fill="#EBCB98" />
                        <circle cx="350" cy="360" r="18" fill="#B83264" /><circle cx="386" cy="360" r="18" fill="#B83264" />
                        <circle cx="422" cy="360" r="18" fill="#EBCB98" /><circle cx="458" cy="360" r="18" fill="#EBCB98" />
                    </g>
                    <path class="door" d="M232 362 L260 205 L288 362 Z" fill="url(#gDoor)" />
                    <line x1="260" y1="70" x2="260" y2="26" stroke="#FFB347" stroke-width="4" stroke-linecap="round" />
                    <path class="flag" d="M260 26 L306 38 L260 52 Z" fill="#E04F7B" />
                    <g class="tent-lanterns">
                        <line x1="22" y1="150" x2="22" y2="230" stroke="#FFB347" stroke-width="2" />
                        <circle class="tl" cx="22" cy="244" r="14" fill="#FFB347" />
                        <line x1="498" y1="150" x2="498" y2="230" stroke="#FFB347" stroke-width="2" />
                        <circle class="tl" cx="498" cy="244" r="14" fill="#FFB347" />
                    </g>
                </svg>
            </div>
        </div>
    </section>

    <!-- ===== RIBBON ===== -->
    <div class="ribbon" aria-hidden="true">
        <div class="ribbon-track">
            <?php foreach($services as $s): ?><span><?= e($s['title']) ?></span><i>✦</i><?php endforeach; ?>
            <?php foreach($services as $s): ?><span><?= e($s['title']) ?></span><i>✦</i><?php endforeach; ?>
        </div>
    </div>

    <!-- ===== INTRO ===== -->
    <section class="intro scallop-top" id="about">
        <div class="wrap intro-grid">
            <div class="intro-text">
                <span class="chip">من نحن</span>
                <h2><?= e(get_setting('about_title', 'مؤسسة عاصفة الأفراح، حيث تبدأ مناسبتك بتجهيز مضبوط')) ?></h2>
                <p><?= nl2br(e(get_setting('about_p1', 'اسم "عاصفة الأفراح" يعبّر عن الحماس الذي نحبه في المناسبات: حركة وفرح وضيوف وتفاصيل كثيرة. وبين كل هذا، تحتاج مناسبتك إلى مكان مريح، وجلسة منظمة، وتدفئة أو تبريد يناسب الجو. هنا يأتي دورنا: نؤجر لك الخيام وبيوت الشعر والدفايات والكراسي والطاولات والمكيفات، لتركّز أنت على استقبال ضيوفك والاستمتاع بالمناسبة.'))) ?></p>
                <p><?= nl2br(e(get_setting('about_p2', 'تأجير الخيام ليس مجرد قطعة قماش تُنصب. هو اختيار حجم يناسب عدد الضيوف، وشكل يناسب نوع المناسبة، وتجهيز يناسب الموسم. لذلك نحرص على سؤالك عن مناسبتك قبل الاتفاق، لنقترح عليك الأنسب بدل أن نعطيك تجهيزًا عامًا قد لا يناسبك.'))) ?></p>
            </div>
            <div class="stat-stack">
                <div class="stat"><b data-count="<?= count($services) ?>">0</b><span>خدمات تأجير في مكان واحد</span></div>
                <div class="stat"><b data-count="<?= count($occasions) ?>">0</b><span>أنواع مناسبات نجهزها</span></div>
                <div class="stat"><b data-count="1">0</b><span>اتصال واحد يجهز لك كل شيء</span></div>
            </div>
        </div>
    </section>

    <!-- ===== SERVICES BENTO ===== -->
    <section class="services scallop-top" id="services">
        <div class="wrap">
            <div class="head light">
                <span class="chip chip-glow">خدماتنا</span>
                <h2>كل ما تحتاجه مناسبتك للإيجار</h2>
                <p>ستة تجهيزات أساسية، تجمعها كلها معًا أو تطلب منها ما يناسبك فقط.</p>
            </div>

            <div class="bento">
                <?php if (empty($services)): ?>
                    <p style="color:rgba(255,244,224,.6);text-align:center;padding:40px;grid-column:1/-1;">لا توجد خدمات مضافة بعد.</p>
                <?php else: ?>
                    <?php foreach($services as $s):
                        $features = array_filter(array_map('trim', explode("\n", $s['features'] ?? '')));
                    ?>
                    <article class="tile tile-<?= e($s['tile_size']) ?>" data-reveal>
                        <div class="tile-ico"><?= e($s['icon']) ?></div>
                        <h3><?= e($s['title']) ?></h3>
                        <p><?= nl2br(e($s['short_desc'])) ?></p>
                        <?php if($features): ?>
                            <ul class="tile-list">
                                <?php foreach($features as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a class="tile-link"
                           href="https://wa.me/<?= e($siteWa) ?>?text=<?= urlencode($s['wa_message'] ?: $s['title']) ?>"
                           target="_blank" rel="noopener">اطلب <?= e($stripTaj($s['title'])) ?></a>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===== TENTS SEO SECTION ===== -->
    <section class="tents scallop-top" id="tents">
        <div class="wrap tents-grid">
            <div class="tents-text">
                <span class="chip">تأجير خيام</span>
                <h2>تأجير خيام لكل أنواع المناسبات</h2>
                <p>
                    إذا كنت تبحث عن <strong>تأجير خيام</strong> لمناسبتك، فأنت في المكان المناسب. نؤجر الخيام للأفراح
                    والعزائم وحفلات التخرج والتجمعات العائلية والمخيمات والفعاليات، ونساعدك في تحديد المناسب من حيث
                    المساحة والشكل والتجهيزات المصاحبة.
                </p>
                <p>
                    الخيمة الجيدة تمنحك ظلًّا في الصيف وحماية في الشتاء، وتصنع للمناسبة مكانًا واضح الحدود يجتمع فيه
                    الضيوف. ويمكن أن تكمّلها بالكراسي والطاولات، وبالدفايات في الشتاء أو المكيفات في الصيف، فتحصل على
                    مناسبة متكاملة بطلب واحد.
                </p>
                <div class="check-grid">
                    <div>✔ خيام للأفراح والاحتفالات</div>
                    <div>✔ خيام للعزائم والمناسبات العائلية</div>
                    <div>✔ خيام لحفلات التخرج</div>
                    <div>✔ خيام للمخيمات والرحلات</div>
                    <div>✔ تجهيز بالكراسي والطاولات</div>
                    <div>✔ تدفئة وتبريد حسب الموسم</div>
                </div>
                <a href="https://wa.me/<?= e($siteWa) ?>?text=<?= urlencode('أريد تأجير خيام لمناسبة') ?>"
                   target="_blank" rel="noopener" class="btn btn-primary">اسأل عن تأجير الخيام</a>
            </div>

            <div class="tents-art" aria-hidden="true">
                <div class="mini-tents">
                    <div class="mini-tent t1"></div>
                    <div class="mini-tent t2"></div>
                    <div class="mini-tent t3"></div>
                </div>
                <div class="art-note">خيمة واحدة تكفي لتبدأ الحكاية</div>
            </div>
        </div>
    </section>

    <!-- ===== HAIR HOUSES ===== -->
    <section class="hair scallop-top" id="hair">
        <div class="wrap hair-grid">
            <div class="hair-art" aria-hidden="true">
                <div class="hair-roof"><span></span><span></span><span></span><span></span><span></span></div>
                <div class="hair-pole p1"></div>
                <div class="hair-pole p2"></div>
                <div class="hair-fire"><i></i><i></i><i></i></div>
            </div>
            <div class="hair-text">
                <span class="chip chip-glow">بيوت الشعر</span>
                <h2>تأجير بيوت شعر بأصالة الضيافة العربية</h2>
                <p>
                    بيت الشعر من أجمل رموز الضيافة العربية. يُنسج من شعر الماعز والصوف، ويمنح المكان دفئًا وأصالة لا
                    تتكرر في غيره. وعندما تُقام فيه الجلسات والعزائم والضيافة، يشعر الضيوف أنهم في أجواء الأجداد الأصيلة.
                </p>
                <p>
                    نؤجر بيوت الشعر للمناسبات والجلسات العائلية، ويمكنك تكميلها بالفرش والكراسي والطاولات والدفايات لتكتمل
                    الأجواء. تواصل معنا وأخبرنا بعدد ضيوفك ونوع المناسبة لنقترح عليك الأنسب.
                </p>
                <a href="https://wa.me/<?= e($siteWa) ?>?text=<?= urlencode('أريد تأجير بيت شعر') ?>"
                   target="_blank" rel="noopener" class="btn btn-primary">اسأل عن بيوت الشعر</a>
            </div>
        </div>
    </section>

    <!-- ===== OCCASIONS ===== -->
    <section class="occasions scallop-top" id="occasions">
        <div class="wrap">
            <div class="head">
                <span class="chip">المناسبات</span>
                <h2>لأي مناسبة تجهّزها معنا</h2>
                <p>مهما كان نوع مناسبتك، نساعدك في تجهيز المكان المناسب لها.</p>
            </div>
            <div class="occ-grid">
                <?php if (empty($occasions)): ?>
                    <p style="color:var(--ink-soft);text-align:center;padding:30px;grid-column:1/-1;">لا توجد مناسبات مضافة بعد.</p>
                <?php else: ?>
                    <?php foreach($occasions as $o): ?>
                    <div class="occ" data-reveal>
                        <b><?= e($o['icon']) ?></b>
                        <h3><?= e($o['title']) ?></h3>
                        <p><?= nl2br(e($o['description'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===== PLANNER ===== -->
    <section class="planner scallop-top" id="planner">
        <div class="wrap planner-box">
            <div class="planner-info">
                <span class="chip chip-glow">احسب تجهيزك</span>
                <h2>كم كرسي وطاولة تحتاج؟</h2>
                <p>حرّك العدد واختر كم شخص يجلس على كل طاولة، وسنعطيك تقديرًا تقريبيًا. ثم أرسله لنا بضغطة واحدة.</p>
            </div>
            <div class="planner-tool">
                <label>عدد الضيوف: <b id="guestsOut">100</b></label>
                <input type="range" id="guests" min="10" max="500" step="5" value="100">
                <label>عدد الأشخاص على كل طاولة</label>
                <select id="perTable">
                    <option value="4">4 أشخاص</option>
                    <option value="6">6 أشخاص</option>
                    <option value="8" selected>8 أشخاص</option>
                    <option value="10">10 أشخاص</option>
                </select>
                <div class="planner-result">
                    <div><b id="outChairs">100</b><span>كرسي</span></div>
                    <div><b id="outTables">13</b><span>طاولة</span></div>
                </div>
                <p class="planner-note">تقدير تقريبي للمساعدة فقط، ويتغير حسب شكل المناسبة والبوفيهات والمساحات الإضافية.</p>
                <a href="#contact" class="btn btn-primary" id="sendPlan">أضف هذا التقدير لطلب الحجز</a>
            </div>
        </div>
    </section>

    <!-- ===== GALLERY ===== -->
    <section class="works-gallery" id="gallery">
        <div class="works-container">
            <div class="works-heading">
                <span class="works-label">معرض أعمالنا</span>
                <h2>لحظات تستحق الذكرى</h2>
                <p>شاهدوا أجمل تجهيزات الأفراح والمناسبات من أعمال عاصفة الأفراح.</p>
            </div>

            <?php if (empty($gallery)): ?>
                <div style="text-align:center;padding:60px 20px;color:#766c77;">
                    <p style="font-size:1.3rem;margin-bottom:8px;">📸 لا توجد صور في المعرض بعد</p>
                    <p>سيتم إضافة الأعمال قريباً</p>
                </div>
            <?php else: ?>
                <div class="works-grid">
                    <?php $gi = 0; foreach($gallery as $g):
                        $src    = SITE_URL . '/' . $g['file_path'];
                        $poster = $g['poster_path'] ? SITE_URL . '/' . $g['poster_path'] : '';
                        $tall   = $gi === 0 ? 'work-tall' : '';
                    ?>
                        <?php if($g['type'] === 'image'): ?>
                            <a href="<?= e($src) ?>" class="work-card <?= $tall ?>" target="_blank">
                                <img src="<?= e($src) ?>" alt="<?= e($g['alt_text']) ?>" loading="lazy">
                            </a>
                        <?php else: ?>
                            <div class="work-card work-video <?= $tall ?>">
                                <video controls preload="metadata" <?= $poster ? 'poster="'.e($poster).'"' : '' ?>>
                                    <source src="<?= e($src) ?>" type="video/mp4">
                                    المتصفح لا يدعم تشغيل الفيديو.
                                </video>
                            </div>
                        <?php endif; ?>
                    <?php $gi++; endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== PROCESS ===== -->
    <section class="process scallop-top" id="process">
        <div class="wrap">
            <div class="head light">
                <span class="chip chip-glow">كيف تحجز</span>
                <h2>أربع خطوات لمناسبة جاهزة</h2>
            </div>
            <div class="timeline" id="timeline">
                <?php if (empty($steps)): ?>
                    <p style="color:rgba(255,244,224,.5);text-align:center;">قيد الإنشاء...</p>
                <?php else: ?>
                    <?php foreach($steps as $st): ?>
                    <div class="tl-item">
                        <span class="tl-bulb"></span>
                        <h3><?= e($st['title']) ?></h3>
                        <p><?= nl2br(e($st['description'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="why scallop-top" id="why">
        <div class="wrap">
            <div class="head">
                <span class="chip">لماذا نحن</span>
                <h2>لماذا يختارنا أصحاب المناسبات؟</h2>
            </div>
            <div class="why-grid">
                <?php if (empty($whyUs)): ?>
                    <p style="color:var(--ink-soft);text-align:center;padding:30px;grid-column:1/-1;">قيد الإنشاء...</p>
                <?php else: ?>
                    <?php foreach($whyUs as $w): ?>
                    <div class="why-card" data-reveal>
                        <b><?= e($w['number']) ?></b>
                        <h3><?= e($w['title']) ?></h3>
                        <p><?= nl2br(e($w['description'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===== GUIDE ===== -->
    <section class="guide scallop-top" id="guide">
        <div class="wrap">
            <div class="head light">
                <span class="chip chip-glow">الدليل</span>
                <h2>دليل تأجير الخيام والتجهيزات</h2>
                <p>مقالات عملية تساعدك تخطط لمناسبتك بثقة.</p>
            </div>

            <?php if (empty($articles)): ?>
                <p style="color:rgba(255,244,224,.5);text-align:center;padding:30px;">قيد الإنشاء...</p>
            <?php else: ?>
                <div class="guide-box">
                    <div class="guide-tabs" id="guideTabs" role="tablist">
                        <?php $ai = 0; foreach($articles as $a): ?>
                            <button class="gtab <?= $ai === 0 ? 'active' : '' ?>" data-target="g<?= (int)$a['id'] ?>" role="tab"><?= e($a['title']) ?></button>
                        <?php $ai++; endforeach; ?>
                    </div>

                    <div class="guide-panels">
                        <?php $ai = 0; foreach($articles as $a): ?>
                            <article class="gpanel <?= $ai === 0 ? 'active' : '' ?>" id="g<?= (int)$a['id'] ?>">
                                <h3><?= e($a['title']) ?></h3>
                                <?= $a['content'] ?>
                            </article>
                        <?php $ai++; endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="faq scallop-top" id="faq">
        <div class="wrap">
            <div class="head">
                <span class="chip">الأسئلة الشائعة</span>
                <h2>أسئلة بتتكرر علينا</h2>
            </div>
            <div class="faq-grid">
                <?php if (empty($faqs)): ?>
                    <p style="color:var(--ink-soft);text-align:center;padding:30px;grid-column:1/-1;">قيد الإنشاء...</p>
                <?php else: ?>
                    <?php foreach($faqs as $f): ?>
                    <details>
                        <summary><?= e($f['question']) ?></summary>
                        <p><?= nl2br(e($f['answer'])) ?></p>
                    </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===== CONTACT / BOOKING ===== -->
    <section class="contact scallop-top" id="contact">
        <div class="wrap contact-grid">
            <div class="contact-info">
                <span class="chip chip-glow">احجز الآن</span>
                <h2>جاهزين نجهز مناسبتك</h2>
                <p>املأ النموذج وسيصلنا طلبك مباشرة، أو كلمنا.</p>
                <a class="call-card" href="tel:+<?= e($siteWa) ?>"><span>اتصال مباشر</span><b dir="ltr"><?= e($sitePhone) ?></b></a>
                <a class="call-card wa" href="https://wa.me/<?= e($siteWa) ?>" target="_blank" rel="noopener"><span>واتساب</span><b dir="ltr"><?= e($sitePhone) ?></b></a>
            </div>

            <form class="booking" id="bookingForm">
                <div class="f-row">
                    <label>الاسم<input type="text" id="fName" placeholder="اسمك الكريم" required></label>
                    <label>رقم الجوال<input type="text" inputmode="tel" id="fPhone" placeholder="05xxxxxxxx" required></label>
                </div>
                <div class="f-row">
                    <label>نوع المناسبة
                        <select id="fType">
                            <?php
                            $types = !empty($occasions) ? array_column($occasions, 'title') : ['فرح','عزيمة','حفل تخرج','تجمع عائلي','مخيم / رحلة','فعالية','أخرى'];
                            foreach ($types as $t): ?>
                                <option><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>تاريخ المناسبة<input type="date" id="fDate"></label>
                </div>
                <div class="f-row">
                    <label style="grid-column:1/-1;">عدد الضيوف<input type="number" id="fGuests" min="1" placeholder="مثال: 100"></label>
                </div>
                <fieldset>
                    <legend>المطلوب</legend>
                    <div class="checks" id="fItems">
                        <?php foreach($services as $s): $label = $stripTaj($s['title']); ?>
                            <label><input type="checkbox" value="<?= e($label) ?>"> <?= e($label) ?></label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <label>ملاحظات<textarea id="fNotes" rows="3" placeholder="اكتب أي تفاصيل إضافية أو موقع المناسبة"></textarea></label>
                <button type="submit" class="btn btn-primary btn-block">إرسال الطلب</button>
            </form>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="wrap footer-inner">
            <span class="brand-mark big">⛺</span>
            <strong>مؤسسة عاصفة الأفراح للحفلات والمناسبات</strong>
            <p><?= nl2br(e(get_setting('footer_desc', 'تأجير خيام، تأجير بيوت شعر، تأجير دفايات، تأجير كراسي، تأجير طاولات، وتأجير مكيفات للحفلات والمناسبات.'))) ?></p>
            <div class="footer-links">
                <a href="#services">خدماتنا</a><a href="#tents">تأجير خيام</a><a href="#gallery">أعمالنا</a><a href="#guide">الدليل</a><a href="#faq">الأسئلة</a><a href="#contact">احجز</a>
            </div>
            <p class="copy"><?= e(get_setting('footer_copy', '© ' . date('Y') . ' عاصفة الأفراح للحفلات والمناسبات. جميع الحقوق محفوظة.')) ?></p>
            <p class="designer-credit">
                تصميم وتنفيذ
                <a href="https://www.facebook.com/share/199H6ZiBeM/" target="_blank" style="text-decoration: none;">
                    <span class="gmt-text" style="color: var(--glow); font-weight: bold;">GMT Web</span>
                </a>
            </p>
        </div>
    </footer>

    <div class="fab">
        <a href="https://wa.me/<?= e($siteWa) ?>" target="_blank" rel="noopener" class="fab-wa" aria-label="واتساب">
            <svg viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.3 8.7 3.3 15.7c0 2.4.7 4.7 1.9 6.7L3 29l6.8-2.1c1.9 1.1 4 1.6 6.2 1.6 7 0 12.7-5.7 12.7-12.7C28.7 8.7 23 3 16 3zm0 23.2c-1.9 0-3.8-.5-5.4-1.5l-.4-.2-4 1.2 1.2-3.9-.3-.4a10.3 10.3 0 0 1-1.6-5.5C5.5 9.9 10.2 5.2 16 5.2S26.5 9.9 26.5 15.7 21.8 26.2 16 26.2z" /></svg>
        </a>
        <a href="tel:+<?= e($siteWa) ?>" class="fab-call" aria-label="اتصال">
            <svg viewBox="0 0 24 24" fill="none"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2C10.5 21 3 13.5 3 6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </a>
    </div>

    <div class="lightbox" id="lightbox">
        <button class="lb-close" id="lbClose" aria-label="إغلاق">×</button>
        <button class="lb-nav lb-prev" id="lbPrev" aria-label="السابق">›</button>
        <img id="lbImg" src="" alt="">
        <button class="lb-nav lb-next" id="lbNext" aria-label="التالي">‹</button>
        <span class="lb-count" id="lbCount"></span>
    </div>

    <script src="script.js"></script>

    <!-- ✅ حقن حفظ الحجز في الداتابيز + فتح واتساب -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('bookingForm');
        if (!form) return;
        const WA = '<?= e($siteWa) ?>';

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();

            const btn = form.querySelector('button[type="submit"]');
            const origText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'جاري الإرسال...';

            const name  = document.getElementById('fName').value.trim();
            const phone = document.getElementById('fPhone').value.trim();
            const type  = document.getElementById('fType').value;
            const date  = document.getElementById('fDate').value;
            const guests= document.getElementById('fGuests').value;
            const notes = document.getElementById('fNotes').value;
            const items = [...document.querySelectorAll('#fItems input:checked')].map(c => c.value);

            const fd = new FormData();
            fd.append('name', name);
            fd.append('phone', phone);
            fd.append('event_type', type);
            fd.append('event_date', date);
            fd.append('guests', guests);
            fd.append('notes', notes);
            items.forEach(i => fd.append('items[]', i));

            try {
                const res = await fetch('api/booking.php', { method: 'POST', body: fd });
                const json = await res.json();

                if (json.ok) {
                    const msg = `السلام عليكم، أرغب بحجز تجهيزات لمناسبة (طلب #${json.id}):
الاسم: ${name}
رقم الجوال: ${phone}
نوع المناسبة: ${type}
تاريخ المناسبة: ${date || 'غير محدد'}
عدد الضيوف: ${guests || 'غير محدد'}
المطلوب: ${items.join('، ') || 'غير محدد'}
ملاحظات: ${notes || '-'}`;

                    window.open(`https://wa.me/${WA}?text=${encodeURIComponent(msg)}`, '_blank');
                    form.reset();
                } else {
                    alert(json.error || 'حدث خطأ، حاول مرة أخرى');
                }
            } catch (err) {
                alert('تعذر الاتصال بالخادم، حاول مرة أخرى');
            } finally {
                btn.disabled = false;
                btn.textContent = origText;
            }
        }, true);
    });
    </script>
</body>
</html>