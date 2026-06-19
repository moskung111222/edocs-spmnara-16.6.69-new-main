<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบสำหรับเจ้าหน้าที่ | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= \App\Config\Config::SITE_URL ?>">
                <div class="logo-vector me-3">NWT</div>
                <div>
                    <span class="brand-text">ระบบยื่นเอกสารออนไลน์ (เจ้าหน้าที่)</span>
                    <span class="brand-subtitle">สพม.นราธิวาส</span>
                </div>
            </a>
            <div class="ms-auto">
                <a href="<?= \App\Config\Config::SITE_URL ?>" class="btn btn-outline-light btn-sm px-3 py-2 rounded-3">
                    <i class="fa-solid fa-house me-2"></i>สำหรับประชาชน
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Container -->
    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>เข้าสู่ระบบล้มเหลว:</strong> <?= esc($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-premium shadow-lg border-warning border-top border-4">
                    <div class="card-header bg-white border-bottom text-center py-4">
                        <div class="text-primary mb-2" style="font-size: 3rem;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-primary">ลงชื่อเข้าใช้งานเจ้าหน้าที่</h4>
                        <span class="text-muted small">ระบบหลังบ้าน สพม.นราธิวาส</span>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="" method="POST" class="needs-validation" novalidate>
                            <!-- CSRF -->
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">ชื่อผู้ใช้งาน (Username)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-regular fa-user text-muted"></i></span>
                                    <input type="text" class="form-control py-2 rounded-end" id="username" name="username" required 
                                           placeholder="ระบุรหัสเจ้าหน้าที่" value="<?= esc($_POST['username'] ?? '') ?>">
                                    <div class="invalid-feedback">กรุณากรอกชื่อผู้ใช้งาน</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">รหัสผ่าน (Password)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-key text-muted"></i></span>
                                    <input type="password" class="form-control py-2 rounded-end" id="password" name="password" required 
                                           placeholder="กรอกรหัสผ่านเข้าเครื่อง">
                                    <div class="invalid-feedback">กรุณากรอกรหัสผ่านเข้าใช้งาน</div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-premium w-100 py-3 fs-5 fw-bold shadow">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>เข้าสู่ระบบควบคุม
                            </button>
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
        <h5 class="fw-bold mb-0">กำลังตรวจสอบข้อมูลผู้ใช้งาน...</h5>
        <span class="small text-white-50 mt-1">กรุณารอสักครู่</span>
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
