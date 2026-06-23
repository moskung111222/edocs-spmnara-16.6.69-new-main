<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยื่นคำขอจัดการเรียนรู้แบบบ้านเรียนออนไลน์ | สพม.นราธิวาส</title>
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
                <div class="logo-vector me-3">HSM</div>
                <div>
                    <span class="brand-text">ระบบบ้านเรียนออนไลน์</span>
                    <span class="brand-subtitle">สพม.นราธิวาส</span>
                </div>
            </a>
            <div class="ms-auto">
                <a href="<?= \App\Config\Config::SITE_URL ?>" class="btn btn-outline-light btn-sm px-3 py-2 rounded-3">
                    <i class="fa-solid fa-house me-2"></i>กลับหน้าหลัก
                </a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Back link -->
                <div class="mb-4">
                    <a href="<?= \App\Config\Config::SITE_URL ?>" class="text-primary text-decoration-none fw-bold">
                        <i class="fa-solid fa-chevron-left me-1"></i> ย้อนกลับไปหน้าหลัก
                    </a>
                </div>

                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>เกิดข้อผิดพลาด:</strong> <?= esc($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($selectedType): ?>
                    <!-- Form Card -->
                    <div class="card card-premium shadow-lg">
                        <div class="card-header-gradient">
                            <h4 class="mb-1 fw-bold text-dark-green"><i class="fa-solid fa-file-signature me-2"></i>แบบฟอร์มยื่นเอกสารคำขอ</h4>
                            <p class="mb-0 text-muted"><?= esc($selectedType['name_th']) ?></p>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <!-- CSRF -->
                                <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>

                                <!-- Section 1: Personal Info -->
                                <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">
                                    <i class="fa-solid fa-user me-2 text-warning"></i>1. ข้อมูลผู้ยื่นคำขอ (ผู้ปกครอง/ผู้ดำเนินการจัดตั้ง)
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label fw-bold">ชื่อ-นามสกุลจริง <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control py-2 rounded-3" id="full_name" name="full_name" required 
                                           placeholder="ตัวอย่าง: นายสมหมาย ปัญญาดี" value="<?= esc($_POST['full_name'] ?? '') ?>">
                                    <div class="invalid-feedback">กรุณากรอกชื่อ-นามสกุลของคุณ</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-bold">อีเมลติดต่อ <span class="text-danger">* (สำหรับส่งข้อมูลบัญชีผู้ใช้)</span></label>
                                        <input type="email" class="form-control py-2 rounded-3" id="email" name="email" required 
                                               placeholder="ตัวอย่าง: parent@example.com" value="<?= esc($_POST['email'] ?? '') ?>">
                                        <div class="invalid-feedback">กรุณากรอกอีเมลติดต่อที่ถูกต้อง</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-bold">เบอร์โทรศัพท์มือถือ <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control py-2 rounded-3" id="phone" name="phone" required 
                                               placeholder="ตัวอย่าง: 0812345678" pattern="[0-9]{9,10}" value="<?= esc($_POST['phone'] ?? '') ?>">
                                        <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์ที่ถูกต้อง (ตัวเลข 9-10 หลัก)</div>
                                    </div>
                                </div>

                                <!-- Password field for existing accounts -->
                                <div class="mb-4 bg-light p-3 rounded-3 border">
                                    <label for="account_password" class="form-label fw-bold text-teal">รหัสผ่านเข้าใช้งาน <span class="text-secondary small">(ระบุเฉพาะเมื่อท่านเคยยื่นคำขอในระบบนี้แล้ว)</span></label>
                                    <input type="password" class="form-control py-2 rounded-3" id="account_password" name="account_password" placeholder="ระบุรหัสผ่านบัญชีของท่าน">
                                    <div class="form-text text-muted small">เว้นว่างไว้หากเป็นการยื่นคำขอเป็นครั้งแรก ระบบจะทำการสร้างบัญชีและรหัสผ่านชั่วคราวให้ท่านโดยอัตโนมัติ</div>
                                </div>

                                <!-- Section 2: Request Detail -->
                                <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">
                                    <i class="fa-solid fa-graduation-cap me-2 text-warning"></i>2. รายละเอียดแผนจัดตั้ง / สถานะการจัดตั้ง
                                </h5>

                                <div class="mb-3">
                                    <label for="school_name" class="form-label fw-bold">ชื่อบ้านเรียนที่ประสงค์จะตั้ง หรือโรงเรียนล่าสุที่ลงทะเบียน <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control py-2 rounded-3" id="school_name" name="form_data[school_name]" required 
                                           placeholder="เช่น บ้านเรียนปัญญารักษ์ หรือ โรงเรียนนราสิขาลัย" value="<?= esc($_POST['form_data']['school_name'] ?? '') ?>">
                                    <div class="invalid-feedback">กรุณากรอกชื่อบ้านเรียนหรือโรงเรียน</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="grad_year" class="form-label fw-bold">ระดับชั้นที่ประสงค์จัดตั้งการศึกษา (พ.ศ. เริ่มจัดตั้ง) <span class="text-danger">*</span></label>
                                        <input type="number" min="2500" max="2600" class="form-control py-2 rounded-3" id="grad_year" name="form_data[grad_year]" required 
                                               placeholder="ตัวอย่าง: 2569" value="<?= esc($_POST['form_data']['grad_year'] ?? '') ?>">
                                        <div class="invalid-feedback">กรุณาระบุปี พ.ศ. ที่ถูกต้อง</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="purpose" class="form-label fw-bold">กลุ่มสาระ/วัตถุประสงค์ในการขอจัดตั้ง <span class="text-danger">*</span></label>
                                        <select class="form-select py-2 rounded-3" id="purpose" name="form_data[purpose]" required>
                                            <option value="" disabled selected>-- เลือกแผนหลัก --</option>
                                            <option value="แผนการศึกษาบูรณาการ" <?= isset($_POST['form_data']['purpose']) && $_POST['form_data']['purpose'] == 'แผนการศึกษาบูรณาการ' ? 'selected' : '' ?>>แผนการศึกษาบูรณาการ</option>
                                            <option value="แผนเน้นวิทยาศาสตร์และเทคโนโลยี" <?= isset($_POST['form_data']['purpose']) && $_POST['form_data']['purpose'] == 'แผนเน้นวิทยาศาสตร์และเทคโนโลยี' ? 'selected' : '' ?>>แผนเน้นวิทยาศาสตร์และเทคโนโลยี</option>
                                            <option value="แผนเน้นศาสนาและคุณธรรม" <?= isset($_POST['form_data']['purpose']) && $_POST['form_data']['purpose'] == 'แผนเน้นศาสนาและคุณธรรม' ? 'selected' : '' ?>>แผนเน้นศาสนาและคุณธรรม</option>
                                            <option value="ทางเลือกเพื่อพัฒนาศักยภาพพิเศษ" <?= isset($_POST['form_data']['purpose']) && $_POST['form_data']['purpose'] == 'ทางเลือกเพื่อพัฒนาศักยภาพพิเศษ' ? 'selected' : '' ?>>ทางเลือกเพื่อพัฒนาศักยภาพพิเศษ</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกวิชา/แผนหลัก</div>
                                    </div>
                                </div>

                                <!-- Section 3: Document Uploads -->
                                <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">
                                    <i class="fa-solid fa-paperclip me-2 text-warning"></i>3. แนบหลักฐานเอกสาร (เฉพาะไฟล์ PDF ขนาดไม่เกิน 10MB เท่านั้น)
                                </h5>

                                <div class="bg-light p-3 rounded-3 mb-4 border">
                                    <p class="text-danger small mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>คำเตือน:</strong> เอกสารหลักฐานทั้งหมดต้องชัดเจน ถูกต้อง และตรงตามคู่มือที่กำหนด</p>
                                </div>

                                <?php foreach ($selectedType['doc_checklist'] as $index => $docName): ?>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold d-block">
                                            <?= esc($docName) ?> <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control rounded-3" name="doc_file_<?= $index ?>" accept="application/pdf" required>
                                        <div class="invalid-feedback">กรุณาแนบไฟล์ PDF สำหรับ <?= esc($docName) ?></div>
                                        <div class="form-text text-muted">แนบไฟล์ PDF ขนาดไฟล์ไม่เกิน 10MB</div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- PDPA Consent Checkbox -->
                                <div class="form-check mb-4 bg-light p-3 rounded-3 border-start border-3 border-teal ms-0">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" value="1" id="pdpa_consent" name="pdpa_consent" required>
                                    <label class="form-check-label fw-semibold text-dark small" for="pdpa_consent" style="cursor:pointer;">
                                        ฉันยินยอมให้สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส เก็บรวบรวม ใช้ และเปิดเผยข้อมูลส่วนบุคคลที่ระบุในคำขอนี้ตามนโยบาย PDPA ของระบบเพื่อพิจารณาอนุมัติจัดตั้ง
                                    </label>
                                    <div class="invalid-feedback">คุณต้องกดยอมรับข้อตกลง PDPA เพื่อทำรายการยื่นคำขอต่อ</div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mt-5 pt-3 border-top d-flex gap-3 justify-content-end">
                                    <a href="<?= \App\Config\Config::SITE_URL ?>" class="btn btn-secondary-premium px-4">ยกเลิก</a>
                                    <button type="submit" class="btn btn-premium px-5 py-2">
                                        <i class="fa-regular fa-paper-plane me-2"></i> ยื่นคำขอจัดตั้งระบบบ้านเรียน
                                    </button>
                                </div>

                             </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card card-premium p-5 text-center">
                        <div class="text-danger mb-3" style="font-size: 3rem;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <h4 class="fw-bold">ไม่พบประเภทคำขอที่ระบบระบุ</h4>
                        <p class="text-muted">ข้อมูลบริการที่เลือกไม่ถูกต้อง หรือหมดระยะเวลายื่นส่ง</p>
                        <div>
                            <a href="<?= \App\Config\Config::SITE_URL ?>" class="btn btn-premium mt-3">กลับหน้าหลัก</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
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

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <h5 class="fw-bold mb-0">กำลังประมวลผลคำขอและตรวจสอบไฟล์ PDF...</h5>
        <span class="small text-white-50 mt-1">กรุณารอสักครู่ ห้ามปิดหรือกดย้อนกลับหน้านี้</span>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        } else {
                            document.getElementById('loading-overlay').style.display = 'flex';
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
