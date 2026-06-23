<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการระเบียบกฎหมาย | สพม.นราธิวาส</title>
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
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/laws" class="admin-sidebar-link active">
                            <i class="fa-solid fa-scale-balanced me-2"></i> จัดการกฎหมาย & ระเบียบ
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/downloads" class="admin-sidebar-link">
                            <i class="fa-solid fa-download me-2"></i> จัดการแบบฟอร์มเอกสาร
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/infographics" class="admin-sidebar-link">
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
                        <h2 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-scale-balanced me-2"></i>จัดการกฎระเบียบ & แนวทางราชการ</h2>
                        <p class="text-muted mb-0">แผงอัปโหลดและลบไฟล์กฎกระทรวง พรบ. กฎระเบียบแนวทางปฏิบัติเกี่ยวกับสิทธิการจัดตั้งบ้านเรียน</p>
                    </div>
                    <div>
                        <button class="btn btn-premium px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> อัปโหลดไฟล์ระเบียบใหม่
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

                <!-- Table Content -->
                <div class="card card-premium shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive text-dark bg-white rounded-3 p-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th style="width: 35%;">หัวเรื่องเอกสารระเบียบ</th>
                                        <th style="width: 25%;">หมวดหมู่/หัวข้อกฎหมาย</th>
                                        <th style="width: 15%;" class="text-center">ขนาดไฟล์</th>
                                        <th style="width: 15%;" class="text-center">วันที่ลงทะเบียน</th>
                                        <th style="width: 10%;" class="text-center">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                                        <div>
                                                            <strong class="text-dark d-block"><?= esc($item['title']) ?></strong>
                                                            <span class="text-muted small" style="font-size: 0.75rem;"><?= esc($item['file_name']) ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border font-monospace small px-2.5 py-1">
                                                        <?= esc($item['category']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center"><span class="small text-secondary"><?= esc(round($item['file_size'] / (1024*1024), 2)) ?> MB</span></td>
                                                <td class="text-center"><span class="small text-secondary"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?> น.</span></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $item['id'] ?>&source=laws" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="เปิดดูเอกสาร">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันที่จะลบไฟล์ระเบียบและเอกสารฉบับนี้อย่างถาวรหรือไม่?');">
                                                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="ลบกฎระเบียบ">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fa-regular fa-folder-open fs-2 mb-2 d-block opacity-50"></i>
                                                ยังไม่มีการบันทึกหรืออัปโหลดเอกสารระเบียบแนวทาง
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                        <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-cloud-arrow-up me-2"></i>อัปโหลดระเบียบกฎหมายใหม่</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">หัวข้อกฎหมาย/คำอธิบายระเบียบ <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="เช่น กฎกระทรวงว่าด้วยการจัดการศึกษาขั้นพื้นฐานโดยครอบครัว พ.ศ. 2547" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">หมวดหมู่/ประเภท <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="พระราชบัญญัติการศึกษา" selected>พระราชบัญญัติการศึกษา</option>
                                <option value="กฎกระทรวง">กฎกระทรวง</option>
                                <option value="แนวทาง/ระเบียบปฏิบัติ">แนวทาง/ระเบียบปฏิบัติ</option>
                                <option value="กฎหมายที่เกี่ยวข้องอื่น">กฎหมายที่เกี่ยวข้องอื่น</option>
                            </select>
                        </div>
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-bold">เลือกไฟล์กฎหมาย (เฉพาะไฟล์ PDF ไม่เกิน 10MB) <span class="text-danger">*</span></label>
                            <input type="file" name="law_file" accept="application/pdf" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary-premium px-4" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-premium px-4">ยืนยันอัปโหลดระเบียบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
