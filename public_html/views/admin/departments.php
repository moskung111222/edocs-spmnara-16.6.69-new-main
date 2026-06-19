<?php use App\Middleware\AuthMiddleware; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการกลุ่มงาน | สพม.นราธิวาส</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
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
                        <hr class="my-1 border-secondary opacity-25">
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/departments" class="admin-sidebar-link active">
                            <i class="fa-solid fa-building me-2"></i> กลุ่มงาน/แผนก
                        </a>
                        <?php if (AuthMiddleware::hasPermission('services.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/services" class="admin-sidebar-link">
                            <i class="fa-solid fa-clipboard-list me-2"></i> ประเภทบริการ
                        </a>
                        <?php endif; ?>
                        <?php if (AuthMiddleware::hasPermission('officers.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="admin-sidebar-link">
                            <i class="fa-solid fa-users-gear me-2"></i> จัดการเจ้าหน้าที่
                        </a>
                        <?php endif; ?>
                        <?php if (AuthMiddleware::hasPermission('roles.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/roles" class="admin-sidebar-link">
                            <i class="fa-solid fa-user-lock me-2"></i> Roles & สิทธิ์
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="p-3 border-top border-secondary">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/logout" class="btn btn-danger w-100 py-2">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>ออกจากระบบ
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 py-4 px-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-building me-2"></i>จัดการกลุ่มงาน/แผนก</h2>
                        <p class="text-muted mb-0">เพิ่ม แก้ไข เปิด/ปิดกลุ่มงานในระบบ</p>
                    </div>
                </div>

                <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($successMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= esc($errorMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <!-- Create Form -->
                <?php if (AuthMiddleware::hasPermission('departments.create')): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-plus-circle me-2"></i>เพิ่มกลุ่มงานใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                            <input type="hidden" name="action" value="create">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">รหัส</label>
                                <input type="text" name="code" class="form-control form-control-sm" required placeholder="เช่น HR">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">ชื่อ (ภาษาไทย)</label>
                                <input type="text" name="name_th" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">ชื่อ (English)</label>
                                <input type="text" name="name_en" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">ลำดับ</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-2"><i class="fa-solid fa-plus me-1"></i>เพิ่ม</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Departments Table -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ชื่อกลุ่มงาน (TH)</th>
                                        <th>ชื่อกลุ่มงาน (EN)</th>
                                        <th class="text-center">เจ้าหน้าที่</th>
                                        <th class="text-center">บริการ</th>
                                        <th class="text-center">ลำดับ</th>
                                        <th class="text-center">สถานะ</th>
                                        <?php if (AuthMiddleware::hasPermission('departments.edit')): ?>
                                        <th class="text-center">จัดการ</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($departments)): ?>
                                        <?php foreach ($departments as $dept): ?>
                                        <tr id="dept-row-<?= $dept['id'] ?>">
                                            <td><strong class="text-primary"><?= esc($dept['code']) ?></strong></td>
                                            <td><?= esc($dept['name_th']) ?></td>
                                            <td class="text-muted small"><?= esc($dept['name_en'] ?? '-') ?></td>
                                            <td class="text-center"><span class="badge bg-info"><?= (int)($dept['officer_count'] ?? 0) ?></span></td>
                                            <td class="text-center"><span class="badge bg-primary"><?= (int)($dept['service_count'] ?? 0) ?></span></td>
                                            <td class="text-center"><?= (int)$dept['sort_order'] ?></td>
                                            <td class="text-center">
                                                <?php if ($dept['active']): ?>
                                                    <span class="badge bg-success rounded-pill">เปิดใช้งาน</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">ปิดใช้งาน</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (AuthMiddleware::hasPermission('departments.edit')): ?>
                                            <td class="text-center">
                                                <button class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $dept['id'] ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= $dept['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1" title="เปิด/ปิด">
                                                        <i class="fa-solid fa-power-off"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $dept['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?= $dept['id'] ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">แก้ไขกลุ่มงาน: <?= esc($dept['code']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">รหัส</label>
                                                                <input type="text" name="code" class="form-control" value="<?= esc($dept['code']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">ชื่อ (TH)</label>
                                                                <input type="text" name="name_th" class="form-control" value="<?= esc($dept['name_th']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">ชื่อ (EN)</label>
                                                                <input type="text" name="name_en" class="form-control" value="<?= esc($dept['name_en'] ?? '') ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">คำอธิบาย</label>
                                                                <textarea name="description" class="form-control" rows="2"><?= esc($dept['description'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">ลำดับ</label>
                                                                <input type="number" name="sort_order" class="form-control" value="<?= (int)$dept['sort_order'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>บันทึก</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="text-center py-5 text-muted">ยังไม่มีข้อมูลกลุ่มงาน</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
