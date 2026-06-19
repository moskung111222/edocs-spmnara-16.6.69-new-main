<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ไม่พบหน้านี้ (404) | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= \App\Config\Config::SITE_URL ?>">
                <div class="me-3 text-warning" style="font-size: 2.2rem;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <span class="brand-text">สพม.นราธิวาส</span>
                    <span class="brand-subtitle">ระบบยื่นคำขอเอกสารออนไลน์สำหรับประชาชน</span>
                </div>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-auto py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-danger mb-4" style="font-size: 5rem;">
                    <i class="fa-regular fa-compass"></i>
                </div>
                <h1 class="display-3 fw-bold text-primary">404</h1>
                <h3 class="fw-bold mb-3">ไม่พบหน้าที่คุณต้องการค้นหา</h3>
                <p class="text-muted mb-4">ลิงก์ที่คุณเรียกใช้อาจจะชำรุด หรือไม่มีอยู่อีกต่อไปในระบบ โปรดตรวจสอบที่อยู่ลิงก์และลองใหม่อีกครั้ง</p>
                <a href="<?= \App\Config\Config::SITE_URL ?>" class="btn btn-premium btn-lg px-5 py-3 shadow">
                    <i class="fa-solid fa-house me-2"></i>กลับสู่หน้าหลัก
                </a>
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
