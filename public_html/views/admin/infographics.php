<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการแบนเนอร์ประชาสัมพันธ์ | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 px-0 admin-sidebar d-flex flex-column">
                <div class="p-4 border-bottom border-light text-center">
                    <span class="fs-4 brand-text d-block fw-bold"><i class="fa-solid fa-user-shield me-2"></i>NWT System</span>
                    <span class="text-muted small">ระบบงานหลังบ้าน</span>
                </div>
                
                <div class="p-3 text-dark border-bottom border-light bg-light text-center">
                    <div class="fw-bold text-dark-green"><i class="fa-solid fa-circle-user me-1"></i><?= esc($_SESSION['officer_name']) ?></div>
                    <span class="badge bg-secondary small mt-1">สิทธิ์: <?= esc($_SESSION['officer_role']) ?></span>
                </div>

                <div class="p-3 flex-grow-1">
                    <nav class="nav flex-column gap-2">
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard" class="admin-sidebar-link">
                            <i class="fa-solid fa-chart-pie me-2"></i> แดชบอร์ดสถิติ
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard?status=submitted" class="admin-sidebar-link">
                            <i class="fa-solid fa-inbox me-2"></i> คำขอรอดำเนินการ
                        </a>

                        <?php if (\App\Middleware\AuthMiddleware::hasPermission('departments.view')): ?>
                        <hr class="my-1 border-secondary opacity-25">
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/departments" class="admin-sidebar-link">
                            <i class="fa-solid fa-building me-2"></i> กลุ่มงาน/แผนก
                        </a>
                        <?php endif; ?>

                        <?php if (\App\Middleware\AuthMiddleware::hasPermission('services.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/services" class="admin-sidebar-link">
                            <i class="fa-solid fa-clipboard-list me-2"></i> ประเภทบริการ
                        </a>
                        <?php endif; ?>

                        <?php if (\App\Middleware\AuthMiddleware::hasPermission('officers.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="admin-sidebar-link">
                            <i class="fa-solid fa-users-gear me-2"></i> จัดการเจ้าหน้าที่
                        </a>
                        <?php endif; ?>

                        <?php if (\App\Middleware\AuthMiddleware::hasPermission('roles.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/roles" class="admin-sidebar-link">
                            <i class="fa-solid fa-user-lock me-2"></i> Roles & สิทธิ์
                        </a>
                        <?php endif; ?>

                        <hr class="my-1 border-secondary opacity-25">
                        <span class="text-muted small px-3 mb-1 d-block fw-bold text-teal"><i class="fa-solid fa-sliders me-1"></i> จัดการเนื้อหาหน้าเว็บหลัก</span>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/announcements" class="admin-sidebar-link">
                            <i class="fa-solid fa-bullhorn me-2"></i> จัดการประกาศ & ข่าวสาร
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/laws" class="admin-sidebar-link">
                            <i class="fa-solid fa-scale-balanced me-2"></i> จัดการกฎหมาย & ระเบียบ
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/downloads" class="admin-sidebar-link">
                            <i class="fa-solid fa-download me-2"></i> จัดการแบบฟอร์มเอกสาร
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/infographics" class="admin-sidebar-link active">
                            <i class="fa-solid fa-image me-2"></i> จัดการภาพสไลด์แบนเนอร์
                        </a>
                    </nav>
                </div>

                <div class="p-3 border-top border-secondary">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/logout" class="btn btn-danger w-100 py-2">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>ออกจากระบบ
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10 py-4 px-md-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-image me-2"></i> จัดการรูปภาพอินโฟกราฟิก & ภาพสไลด์แบนเนอร์</h2>
                        <p class="text-muted mb-0">แผงจัดการอัปโหลดภาพอินโฟกราฟิกสไลด์โชว์ที่แสดงด้านบนสุดของหน้าเว็บประชาชน</p>
                    </div>
                    <div>
                        <button class="btn btn-premium px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> อัปโหลดรูปภาพใหม่
                        </button>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?= esc($successMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>ผิดพลาด:</strong> <?= esc($errorMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Gallery Grid Content -->
                <div class="row g-4">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-0 shadow-sm overflow-hidden text-dark bg-white position-relative" style="border-radius: 12px;">
                                    <img src="<?= \App\Config\Config::SITE_URL ?>/<?= esc($item['image_path']) ?>" class="card-img-top" alt="<?= esc($item['title']) ?>" style="height: 200px; object-fit: cover;">
                                    
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-1 text-dark text-truncate"><?= esc($item['title']) ?></h6>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <span class="text-muted small" style="font-size: 0.75rem;"><i class="fa-regular fa-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?> น.</span>
                                            
                                            <form method="POST" class="d-inline mb-0" onsubmit="return confirm('ยืนยันการลบรูปภาพสไลด์แบนเนอร์ประชาสัมพันธ์ฉบับนี้หรือไม่?');">
                                                <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" title="ลบภาพแบนเนอร์">
                                                    <i class="fa-solid fa-trash-can me-1"></i> ลบรูป
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 text-muted">
                            <div class="card p-5 border-0 shadow-sm bg-white rounded-4">
                                <i class="fa-regular fa-image fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">ยังไม่มีการบันทึกหรืออัปโหลดรูปภาพสไลด์แบนเนอร์โฆษณาในขณะนี้</p>
                                <span class="small text-muted mt-1">รูปภาพที่อัปโหลดจะถูกแสดงผลเป็นแบนเนอร์สไลด์ที่หน้าหลักประชาชนเพื่อช่วยแนะนำการใช้งานระบบ</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form method="POST" enctype="multipart/form-data">
                    <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                    <input type="hidden" name="action" value="create">
                    
                    <div class="modal-header bg-teal text-white border-0 py-3">
                        <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-cloud-arrow-up me-2"></i>อัปโหลดรูปภาพแบนเนอร์สไลด์ประชาสัมพันธ์ใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">ชื่อหัวเรื่อง/คำอธิบายภาพสไลด์แบนเนอร์ <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="ระบุหัวเรื่องหรือควิกไกด์ (เช่น คู่มือการเขียนแผนและยื่นคำขอจัดตั้ง)" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">เลือกไฟล์รูปภาพแบนเนอร์ (JPG, JPEG, PNG, WEBP, GIF ขนาดห้ามเกิน 5MB) <span class="text-danger">*</span></label>
                            <input type="file" name="image_file" accept="image/*" class="form-control" required>
                            <div class="form-text text-muted small mt-1">แนะนำ: ใช้รูปภาพขนาดสัดส่วนกว้างยาวประมาณ 1200x500 พิกเซล เพื่อให้แสดงผลในสไลด์โชว์ได้อย่างสวยงามและคมชัด</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary-premium px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-premium px-4">ยืนยันอัปโหลดรูปภาพ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
