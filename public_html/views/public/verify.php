<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยัน OTP | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

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
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>ผิดพลาด:</strong> <?= esc($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-premium shadow-lg">
                    <div class="card-header-gradient text-center">
                        <div class="mb-2" style="font-size: 2.5rem;">
                            <i class="fa-solid fa-envelope-shield text-warning"></i>
                        </div>
                        <h4 class="mb-1 fw-bold text-dark-green">ยืนยันตัวตนด้วยรหัส OTP</h4>
                        <p class="mb-0 text-muted">ส่งข้อมูลคำรับคำขอ สพม.นราธิวาส</p>
                    </div>
                    <div class="card-body p-4 p-md-5 text-center">
                        <p class="text-muted">
                            ระบบได้ส่งรหัสยืนยันตัวตนแบบใช้ครั้งเดียว (OTP) จำนวน 6 หลัก ไปยังอีเมล:
                            <strong class="text-primary d-block mt-1"><?= esc($temp['email']) ?></strong>
                        </p>

                        <?php if (isset($devOtp) && !empty($devOtp)): ?>
                            <div class="alert alert-warning border-warning rounded-3 mb-4 py-2" role="alert">
                                <i class="fa-solid fa-flask me-2 text-warning"></i><strong>[โหมดทดสอบ]</strong> รหัส OTP ปัจจุบันคือ: <strong class="fs-5 text-dark"><?= esc($devOtp) ?></strong> (หรือใช้ 123456)
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="POST" class="needs-validation mt-4" novalidate>
                            <!-- CSRF -->
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>

                            <div class="mb-4">
                                <label for="otp_code" class="form-label fw-bold d-block text-start mb-2">กรอกรหัส OTP 6 หลัก</label>
                                <input type="text" 
                                       class="form-control text-center py-3 fs-3 fw-bold rounded-3 letter-spacing-lg" 
                                       id="otp_code" 
                                       name="otp_code" 
                                       required 
                                       maxlength="6" 
                                       pattern="[0-9]{6}" 
                                       placeholder="------"
                                       value="<?= esc($devOtp ?? '') ?>"
                                       autocomplete="off"
                                       style="letter-spacing: 12px; padding-left: 20px;">
                                <div class="invalid-feedback text-start">กรุณากรอกตัวเลขรหัส OTP จำนวน 6 หลัก</div>
                            </div>

                            <button type="submit" class="btn btn-premium w-100 py-3 fs-5 fw-bold mb-3 shadow">
                                <i class="fa-solid fa-check-double me-2"></i>ยืนยันรหัส OTP และส่งคำขอ
                            </button>

                            <p class="text-muted small">
                                ไม่ได้รับอีเมล? ตรวจสอบโฟลเดอร์อีเมลขยะ (Spam) หรือ 
                                <a href="<?= \App\Config\Config::SITE_URL ?>/request/create?type=<?= $temp['type_id'] ?>" class="text-decoration-none fw-bold">ยื่นคำขอใหม่อีกครั้ง</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto border-top border-warning border-3">
        <div class="container text-center">
            <p class="mb-1">สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส (สพม.นราธิวาส)</p>
            <p class="text-muted small mb-0">ถนนศูนย์ราชการ ตำบลโคกเคียน อำเภอเมือง จังหวัดนราธิวาส 96000 | เบอร์โทรศัพท์: 073-511-182</p>
            <p class="text-muted small mt-2">&copy; <?= date('Y') ?> NWT Document Submission System. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <h5 class="fw-bold mb-0">กำลังยืนยันรหัส OTP และบันทึกคำขอเข้าระบบ...</h5>
        <span class="small text-white-50 mt-1">กรุณารอสักครู่ ห้ามปิดหน้านี้</span>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form Validation
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
