<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบยื่นคำขอเอกสารออนไลน์ | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= \App\Config\Config::SITE_URL ?>">
                <div class="logo-vector me-3">NWT</div>
                <div>
                    <span class="brand-text">ระบบยื่นเอกสารออนไลน์</span>
                    <span class="brand-subtitle">สพม.นราธิวาส</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-2">
                    <li class="nav-item"><a class="nav-link active" href="<?= \App\Config\Config::SITE_URL ?>">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="#request-types">ประเภทคำขอ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-to-submit">ขั้นตอนการยื่น</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">ติดต่อ</a></li>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/request/track" class="btn btn-outline-light btn-sm px-3 py-2 rounded-3 text-center">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>ติดตามคำขอ
                    </a>
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/login" class="btn btn-warning btn-sm px-3 py-2 rounded-pill text-dark fw-bold shadow-sm text-center">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>เข้าสู่ระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient py-5 text-dark position-relative overflow-hidden">
        <!-- Floating shapes background pattern -->
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background-image: radial-gradient(circle, #0d9488 1px, transparent 1px); background-size: 20px 20px;"></div>
        
        <div class="container py-4 position-relative z-2">
            <div class="row align-items-center g-5">
                <!-- Left Column -->
                <div class="col-lg-7 text-start">
                    <div class="badge-online-service mb-3 d-inline-flex align-items-center px-3 py-2 rounded-pill shadow-sm">
                        <i class="fa-regular fa-clock text-warning me-2 animate-spin-slow"></i>
                        <span class="text-secondary fs-7 fw-semibold">บริการออนไลน์ ตลอด 24 ชั่วโมง</span>
                    </div>
                    
                    <h1 class="display-4 fw-bold mb-3 lh-sm text-dark-green">
                        ยื่นคำขอการจัด<br>การศึกษา <span class="text-gradient">ออนไลน์</span>
                    </h1>
                    
                    <p class="lead mb-4 text-muted fs-5">
                        ยื่นคำขอเกี่ยวกับการจัดการศึกษาถึงเจ้าหน้าที่ สพม.นราธิวาส แนบเอกสาร PDF ยืนยันตัวตนด้วย OTP และติดตามสถานะได้ทุกที่ทุกเวลา
                    </p>
                    
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#request-types" class="btn btn-warning btn-lg px-4 py-3 fw-bold text-dark rounded-3 shadow d-flex align-items-center btn-graphite-action">
                            <span>เริ่มยื่นคำขอ</span>
                            <i class="fa-solid fa-arrow-right ms-2 fs-5"></i>
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/request/track" class="btn btn-outline-light btn-lg px-4 py-3 rounded-3 shadow btn-graphite-action">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> ติดตามคำขอ
                        </a>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="col-lg-5 text-center position-relative">
                    <div class="position-relative d-inline-block">
                        <img src="<?= \App\Config\Config::SITE_URL ?>/assets/images/hero_education.png" 
                             alt="ระบบยื่นเอกสารออนไลน์ สพม.นราธิวาส" 
                             class="img-fluid rounded-4 shadow-lg border main-hero-img"
                             style="max-height: 380px; width: 100%; object-fit: cover;">
                        
                        <!-- Floating Glassmorphism Badge -->
                        <div class="glass-premium-card p-3 rounded-3 shadow-md position-absolute text-start border" 
                             style="bottom: -15px; left: -15px; max-width: 290px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="fa-solid fa-graduation-cap text-dark fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="text-dark fw-bold mb-0 small">สพม.นราธิวาส</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.7rem;">การศึกษาโดยครอบครัว / อัธยาศัย / ทางเลือก</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Submenu Bar -->
    <div class="container position-relative" style="margin-top: -35px; z-index: 10;">
        <div class="bg-white rounded-4 shadow-lg p-3 border border-light">
            <div class="row text-center g-2 justify-content-center">
                <div class="col-6 col-sm-4 col-md-2-custom">
                    <a href="#request-types" class="submenu-item d-flex flex-column align-items-center justify-content-center p-3 rounded-3 text-decoration-none">
                        <div class="submenu-icon mb-2">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <span class="submenu-text fw-bold">ประเภทคำขอ</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-2-custom">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/request/track" class="submenu-item d-flex flex-column align-items-center justify-content-center p-3 rounded-3 text-decoration-none">
                        <div class="submenu-icon mb-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <span class="submenu-text fw-bold">ติดตามสถานะ</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-2-custom">
                    <a href="#how-to-submit" class="submenu-item d-flex flex-column align-items-center justify-content-center p-3 rounded-3 text-decoration-none">
                        <div class="submenu-icon mb-2">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <span class="submenu-text fw-bold">ขั้นตอนการยื่น</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-2-custom">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/login" class="submenu-item d-flex flex-column align-items-center justify-content-center p-3 rounded-3 text-decoration-none">
                        <div class="submenu-icon mb-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                        </div>
                        <span class="submenu-text fw-bold">เข้าสู่ระบบ</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-2-custom">
                    <a href="#how-to-submit" class="submenu-item d-flex flex-column align-items-center justify-content-center p-3 rounded-3 text-decoration-none">
                        <div class="submenu-icon mb-2">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <span class="submenu-text fw-bold">สมัครสมาชิก</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container py-5" id="request-types">
        <div class="text-center mb-5 mt-5">
            <span class="text-teal fw-bold fs-7 tracking-wider d-block mb-2">บริการของเรา</span>
            <h2 class="fw-bold text-dark-green display-6">เลือกประเภทคำขอ</h2>
            <p class="text-muted">เลือกบริการที่ต้องการยื่นคำขอเกี่ยวกับการจัดการศึกษา</p>
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
                                        ยื่นคำขอประเภทนี้ <i class="fa-solid fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">ไม่พบข้อมูลประเภทคำขอเอกสารในขณะนี้</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Grid -->
        <div class="row mt-5 pt-5 g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 border shadow-sm h-100">
                    <div class="text-primary mb-3" style="font-size: 2.5rem;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h5 class="fw-bold">ระบบยืนยันตัวตนด้วย OTP</h5>
                    <p class="text-muted mb-0">ป้องกันบุคคลอื่นสวมสิทธิ์การขอรับเอกสารสำคัญ โดยระบบจะจัดส่งรหัสยืนยันไปยังอีเมลของผู้ยื่นคำขอทุกครั้ง</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 border shadow-sm h-100">
                    <div class="text-success mb-3" style="font-size: 2.5rem;">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h5 class="fw-bold">จัดเก็บข้อมูลปลอดภัย</h5>
                    <p class="text-muted mb-0">เอกสารสำคัญและไฟล์ PDF ทั้งหมดจะถูกเก็บรักษาไว้นอกโฟลเดอร์สาธารณะ และต้องดาวน์โหลดผ่านตัวกลางควบคุมสิทธิ์เท่านั้น</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 border shadow-sm h-100">
                    <div class="text-warning mb-3" style="font-size: 2.5rem;">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h5 class="fw-bold">แชทคุยตรงกับเจ้าหน้าที่</h5>
                    <p class="text-muted mb-0">หากเอกสารไม่ถูกต้อง หรือเจ้าหน้าที่ต้องการข้อมูลเพิ่ม ระบบมีฟังก์ชันสนทนาโต้ตอบผ่านรหัสคำขอได้โดยตรง</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5 border-top border-warning border-3">
        <div class="container text-center">
            <p class="mb-1">สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส (สพม.นราธิวาส)</p>
            <p class="text-muted small mb-0">ถนนศูนย์ราชการ ตำบลโคกเคียน อำเภอเมือง จังหวัดนราธิวาส 96000 | เบอร์โทรศัพท์: 073-511-182</p>
            <p class="text-muted small mt-2">&copy; <?= date('Y') ?> NWT Document Submission System. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
