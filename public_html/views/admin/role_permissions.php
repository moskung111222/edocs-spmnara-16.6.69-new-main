<?php use App\Middleware\AuthMiddleware;
$moduleLabels = [
    'dashboard'   => 'แดชบอร์ด',
    'requests'    => 'คำขอเอกสาร',
    'officers'    => 'เจ้าหน้าที่',
    'departments' => 'กลุ่มงาน',
    'services'    => 'ประเภทบริการ',
    'roles'       => 'Roles & สิทธิ์',
    'audit'       => 'Audit Log',
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสิทธิ์: <?= esc($role['name_th']) ?> | สพม.นราธิวาส</title>
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
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard" class="admin-sidebar-link"><i class="fa-solid fa-chart-pie me-2"></i> แดชบอร์ดสถิติ</a>
                        <hr class="my-1 border-secondary opacity-25">
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/roles" class="admin-sidebar-link active"><i class="fa-solid fa-user-lock me-2"></i> Roles & สิทธิ์</a>
                    </nav>
                </div>
                <div class="p-3 border-top border-secondary">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/logout" class="btn btn-danger w-100 py-2"><i class="fa-solid fa-right-from-bracket me-2"></i>ออกจากระบบ</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 py-4 px-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="fw-bold mb-0 text-primary">
                            <i class="fa-solid fa-shield-halved me-2"></i>จัดการสิทธิ์: <?= esc($role['name_th']) ?>
                            <span class="badge bg-primary ms-2"><?= esc($role['code']) ?></span>
                        </h2>
                        <p class="text-muted mb-0"><?= esc($role['description'] ?? '') ?></p>
                    </div>
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/roles" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i>กลับ
                    </a>
                </div>

                <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($successMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= esc($errorMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <!-- Role Info Edit -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-info-circle me-2"></i>ข้อมูล Role</h5></div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                            <input type="hidden" name="action" value="update_info">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Code</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="<?= esc($role['code']) ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">ชื่อ (TH)</label>
                                <input type="text" name="name_th" class="form-control form-control-sm" value="<?= esc($role['name_th']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">คำอธิบาย</label>
                                <input type="text" name="description" class="form-control form-control-sm" value="<?= esc($role['description'] ?? '') ?>">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-2"><i class="fa-solid fa-save"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Permission Matrix -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-key me-2"></i>Permission Matrix</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                            <input type="hidden" name="action" value="update_permissions">

                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="document.querySelectorAll('input[name=\'permissions[]\']').forEach(c=>c.checked=true)">
                                    <i class="fa-solid fa-check-double me-1"></i>เลือกทั้งหมด
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.querySelectorAll('input[name=\'permissions[]\']').forEach(c=>c.checked=false)">
                                    <i class="fa-solid fa-xmark me-1"></i>ยกเลิกทั้งหมด
                                </button>
                            </div>

                            <?php foreach ($allPermissions as $module => $perms): ?>
                            <div class="card mb-3">
                                <div class="card-header py-2 bg-light">
                                    <strong><i class="fa-solid fa-cube me-2 text-primary"></i><?= esc($moduleLabels[$module] ?? $module) ?></strong>
                                    <span class="badge bg-secondary ms-2"><?= count($perms) ?></span>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row g-2">
                                        <?php foreach ($perms as $perm): ?>
                                        <div class="col-md-4 col-lg-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                       value="<?= (int)$perm['id'] ?>" id="perm_<?= (int)$perm['id'] ?>"
                                                       <?= in_array((int)$perm['id'], $rolePermissionIds) ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="perm_<?= (int)$perm['id'] ?>">
                                                    <code class="text-primary"><?= esc($perm['code']) ?></code><br>
                                                    <span class="text-muted"><?= esc($perm['name_th']) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <button type="submit" class="btn btn-success px-4 py-2 mt-3">
                                <i class="fa-solid fa-shield-halved me-2"></i>บันทึกสิทธิ์ทั้งหมด
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
