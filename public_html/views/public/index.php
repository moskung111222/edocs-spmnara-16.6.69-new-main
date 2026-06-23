<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ศูนย์บริการข้อมูลและจัดการระบบบ้านเรียนออนไลน์ | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .news-card {
            border-left: 4px solid #0d9488;
            transition: all 0.2s ease;
        }
        .news-card:hover {
            transform: translateY(-2px);
        }
        .category-header {
            background-color: #f0fdfa;
            color: #0f766e;
            font-weight: bold;
            border-left: 4px solid #0d9488;
        }
        .law-item, .doc-item {
            transition: background-color 0.2s;
        }
        .law-item:hover, .doc-item:hover {
            background-color: #f3f4f6;
        }
        .pdpa-box {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #f8fafc;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= \App\Config\Config::SITE_URL ?>">
                <div class="logo-vector me-3">HSM</div>
                <div>
                    <span class="brand-text">ระบบบ้านเรียนออนไลน์</span>
                    <span class="brand-subtitle">สพม.นราธิวาส</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-2">
                    <li class="nav-item"><a class="nav-link active" href="<?= \App\Config\Config::SITE_URL ?>">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="#announcements-section">ประกาศข่าว</a></li>
                    <li class="nav-item"><a class="nav-link" href="#laws-section">กฎหมาย & ระเบียบ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#downloads-section">ศูนย์ดาวน์โหลด</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pdpa-section">นโยบาย PDPA</a></li>
                    <li class="nav-item"><a class="nav-link" href="#request-types">ยื่นคำขอ</a></li>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/request/track" class="btn btn-outline-light btn-sm px-3 py-2 rounded-3 text-center">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>ติดตามคำขอ
                    </a>
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/login" class="btn btn-warning btn-sm px-3 py-2 rounded-pill text-dark fw-bold shadow-sm text-center">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>เข้าสู่ระบบเจ้าหน้าที่
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Infographics Slider/Gallery Section -->
    <section class="container mt-4">
        <?php if (!empty($infographics)): ?>
            <div id="infographicCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($infographics as $index => $info): ?>
                        <button type="button" data-bs-target="#infographicCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($infographics as $index => $info): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-bs-interval="5000">
                            <img src="<?= \App\Config\Config::SITE_URL ?>/<?= esc($info['image_path']) ?>" class="d-block w-100" alt="<?= esc($info['title']) ?>" style="height: 380px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 8px; padding: 10px 20px;">
                                <h5 class="fw-bold mb-0 text-white"><?= esc($info['title']) ?></h5>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#infographicCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#infographicCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php else: ?>
            <div class="hero-gradient py-5 text-dark rounded-4 shadow-sm position-relative overflow-hidden">
                <div class="container py-4 text-center">
                    <h2 class="fw-bold text-dark-green mb-3">ระบบบริหารจัดการการศึกษาขั้นพื้นฐานโดยครอบครัว</h2>
                    <p class="lead text-muted max-w-2xl mx-auto">ยินดีต้อนรับสู่ระบบยื่นคำขอจัดตั้งบ้านเรียน (Homeschool) และรายงานผลประเมินสัมฤทธิ์ สพม.นราธิวาส แบบครบวงจรผ่านช่องทางออนไลน์ตลอด 24 ชั่วโมง</p>
                    <a href="#request-types" class="btn btn-warning px-4 py-2 fw-bold rounded-3 mt-2">เริ่มต้นยื่นคำขอที่นี่</a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- News & Announcements Section -->
    <section class="container py-5" id="announcements-section">
        <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
            <i class="fa-solid fa-bullhorn text-teal fs-4"></i>
            <h3 class="fw-bold text-dark-green m-0">ข่าวสารประชาสัมพันธ์ & ประกาศ</h3>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 news-card">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-teal text-white small px-2.5 py-1">
                                        <?php 
                                            if ($item['type'] === 'announcement') echo 'ประกาศราชการ';
                                            elseif ($item['type'] === 'public_notice') echo 'แจ้งประชาสัมพันธ์';
                                            else echo 'อัปเดตระบบ';
                                        ?>
                                    </span>
                                    <span class="text-muted small"><i class="fa-regular fa-calendar me-1"></i><?= date('d/m/Y', strtotime($item['created_at'])) ?></span>
                                </div>
                                <h5 class="fw-bold text-dark-green mb-2"><?= esc($item['title']) ?></h5>
                                <p class="text-muted small flex-grow-1"><?= nl2br(esc(mb_substr($item['content'], 0, 150) . (mb_strlen($item['content']) > 150 ? '...' : ''))) ?></p>
                                <?php if (mb_strlen($item['content']) > 150): ?>
                                    <button class="btn btn-link btn-sm text-teal fw-bold p-0 text-start mt-2" data-bs-toggle="modal" data-bs-target="#announceModal<?= $item['id'] ?>">อ่านเพิ่มเติม...</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Announcement Modal -->
                    <div class="modal fade" id="announceModal<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-header bg-teal text-white border-0 py-3">
                                    <h5 class="modal-title fw-bold text-white"><?= esc($item['title']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted small mb-3"><i class="fa-regular fa-calendar me-1"></i>เขียนเมื่อวันที่ <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></p>
                                    <p class="text-dark" style="line-height: 1.7;"><?= nl2br(esc($item['content'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">ไม่มีประกาศข่าวประชาสัมพันธ์ในขณะนี้</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Laws & Regulations Section -->
    <section class="container py-4" id="laws-section">
        <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
            <i class="fa-solid fa-scale-balanced text-teal fs-4"></i>
            <h3 class="fw-bold text-dark-green m-0">กฎหมาย ระเบียบ & แนวทางที่เกี่ยวข้อง</h3>
        </div>

        <div class="row g-4">
            <?php if (!empty($lawsGrouped)): ?>
                <?php foreach ($lawsGrouped as $category => $laws): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                            <div class="card-header category-header py-3 px-4"><?= esc($category) ?></div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($laws as $law): ?>
                                    <a href="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $law['id'] ?>&source=laws" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 border-bottom-0 law-item">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                            <span class="text-dark small fw-medium"><?= esc($law['title']) ?></span>
                                        </div>
                                        <i class="fa-solid fa-arrow-down-long text-teal"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">ไม่มีกฎระเบียบข้อกฎหมายในศูนย์ข้อมูลขณะนี้</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Download Center Section -->
    <section class="container py-5" id="downloads-section">
        <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
            <i class="fa-solid fa-folder-open text-teal fs-4"></i>
            <h3 class="fw-bold text-dark-green m-0">ศูนย์ดาวน์โหลดแบบฟอร์มเอกสารเปล่า</h3>
        </div>

        <div class="row g-4">
            <?php 
                $standardCategories = [
                    'Homeschool Application' => 'คำขอจัดการศึกษา (Homeschool)',
                    'Education Plan Templates' => 'แผนการจัดการเรียนรู้',
                    'Learning Report Templates' => 'แบบรายงานประเมินผลการเรียนรู้',
                    'Transfer Requests' => 'คำขอโอนย้ายสถานศึกษา',
                    'Graduation Requests' => 'คำขออนุมัติจบการศึกษา',
                    'Other Documents' => 'เอกสารแนวทางอื่นๆ'
                ];
            ?>
            <?php if (!empty($docsGrouped)): ?>
                <?php foreach ($docsGrouped as $categoryKey => $docs): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                            <div class="card-header category-header py-3 px-4">
                                <?= esc($standardCategories[$categoryKey] ?? $categoryKey) ?>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($docs as $doc): ?>
                                    <a href="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $doc['id'] ?>&source=download_center&download=1" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 border-bottom-0 doc-item">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php 
                                                $ext = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                                                if ($ext === 'pdf') echo '<i class="fa-solid fa-file-pdf text-danger fs-5"></i>';
                                                else echo '<i class="fa-solid fa-file-word text-primary fs-5"></i>';
                                            ?>
                                            <span class="text-dark small fw-medium"><?= esc($doc['title']) ?></span>
                                        </div>
                                        <i class="fa-solid fa-download text-teal"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">ไม่มีเอกสารแบบฟอร์มให้ดาวน์โหลดในศูนย์บริการขณะนี้</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- PDPA Information Section -->
    <section class="container py-4" id="pdpa-section">
        <div class="pdpa-box p-5 rounded-4 shadow-sm position-relative overflow-hidden border border-slate-700">
            <div class="position-absolute top-0 end-0 p-4 opacity-5 text-white" style="font-size: 6rem;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            
            <h3 class="fw-bold mb-3 d-flex align-items-center gap-2 text-warning">
                <i class="fa-solid fa-user-lock"></i> การคุ้มครองข้อมูลส่วนบุคคล (PDPA Consent Notice)
            </h3>
            
            <p style="font-size: 0.95rem; line-height: 1.8; color: #cbd5e1;">
                สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส (สพม.นราธิวาส) ให้ความสำคัญอย่างยิ่งต่อความเป็นส่วนตัวและการคุ้มครองข้อมูลส่วนบุคคลของผู้ยื่นคำขอจัดตั้งและติดตามระบบบ้านเรียน การอัปโหลดเอกสารประกอบคำขอต่างๆ (เช่น สำเนาบัตรประจำตัวประชาชน, สำเนาทะเบียนบ้าน หรือระเบียนการเรียน) จะถูกนำส่งและเก็บรักษาไว้ในไดเรกทอรีส่วนตัวที่มีความปลอดภัยสูงนอก Web Root ซึ่งบุคคลภายนอกไม่สามารถเข้าถึงได้โดยตรงผ่านช่องทางอินเทอร์เน็ต
            </p>
            <p class="mb-0" style="font-size: 0.95rem; line-height: 1.8; color: #cbd5e1;">
                เมื่อท่านกดกดยื่นส่งคำขอ (Submit Request) ระบบจะถือว่าท่านได้ตรวจสอบข้อมูลและ<strong>ให้ความยินยอมโดยสมัครใจ (Consent)</strong> ในการจัดเก็บและประมวลผลข้อมูลส่วนตัวเหล่านี้เพื่อใช้ในการตรวจสอบพิจารณาคุณสมบัติและการประเมินผลการเรียนรู้ตามกระบวนการทำงานของกลุ่มงานส่งเสริมการจัดการศึกษา สพม.นราธิวาส
            </p>
        </div>
    </section>

    <!-- Request Creation (Portal Entry) Section -->
    <main class="container py-5" id="request-types">
        <div class="text-center mb-5 mt-4">
            <span class="text-teal fw-bold fs-7 tracking-wider d-block mb-2">ยื่นส่งเอกสารออนไลน์</span>
            <h2 class="fw-bold text-dark-green display-6">เลือกบริการที่ท่านต้องการยื่นคำขอ</h2>
            <p class="text-muted">กรุณาดาวน์โหลดแบบฟอร์มเอกสารเปล่าจากหน้าเว็บ กรอกข้อมูล และแนบหลักฐานให้ถูกต้อง</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php if (!empty($types)): ?>
                <?php foreach ($types as $index => $type): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-premium h-100 border-0 shadow-sm position-relative overflow-hidden">
                            <div class="card-body d-flex flex-column p-4 p-md-5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="service-number"><?= sprintf(".%02d", $index + 1) ?></div>
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 fs-7"><?= esc($type['code']) ?></span>
                                </div>
                                <h4 class="card-title fw-bold text-dark-green mb-3"><?= esc($type['name_th']) ?></h4>
                                
                                <h6 class="fw-bold text-secondary mb-3 mt-2"><i class="fa-solid fa-list-check me-2"></i>เอกสารหลักฐานที่ต้องแนบ:</h6>
                                <div class="flex-grow-1 d-flex flex-column gap-2 mb-4">
                                    <?php foreach ($type['doc_checklist'] as $doc): ?>
                                        <div class="checklist-item py-2 px-3 rounded-3 bg-light border-start border-3 border-teal d-flex align-items-center m-0">
                                            <i class="fa-regular fa-circle-check text-success me-2"></i>
                                            <span class="small text-muted fw-semibold"><?= esc($doc) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="pt-3 border-top mt-auto">
                                    <a href="<?= \App\Config\Config::SITE_URL ?>/request/create?type=<?= $type['id'] ?>" class="btn btn-premium w-100 py-2">
                                        เขียนและยื่นส่งแบบฟอร์ม <i class="fa-solid fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">ไม่พบข้อมูลประเภทบริการหรือแบบฟอร์มในขณะนี้</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5 border-top border-warning border-3">
        <div class="container text-center">
            <p class="mb-1">กลุ่มส่งเสริมการจัดการศึกษา สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส (สพม.นราธิวาส)</p>
            <p class="text-muted small mb-0">ถนนศูนย์ราชการ ตำบลโคกเคียน อำเภอเมือง จังหวัดนราธิวาส 96000 | เบอร์โทรศัพท์: 073-511-182</p>
            <p class="text-muted small mt-2">&copy; <?= date('Y') ?> Homeschool Management Portal. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
