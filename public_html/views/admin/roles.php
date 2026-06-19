<?php use App\Middleware\AuthMiddleware; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ Roles | สพม.นราธิวาส</title>
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
                        <?php if (AuthMiddleware::hasPermission('departments.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/departments" class="admin-sidebar-link"><i class="fa-solid fa-building me-2"></i> กลุ่มงาน/แผนก</a>
                        <?php endif; ?>
                        <?php if (AuthMiddleware::hasPermission('services.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/services" class="admin-sidebar-link"><i class="fa-solid fa-clipboard-list me-2"></i> ประเภทบริการ</a>
                        <?php endif; ?>
                        <?php if (AuthMiddleware::hasPermission('officers.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="admin-sidebar-link"><i class="fa-solid fa-users-gear me-2"></i> จัดการเจ้าหน้าที่</a>
                        <?php endif; ?>
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
                        <h2 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-user-lock me-2"></i>จัดการ Roles & สิทธิ์ (RBAC)</h2>
                        <p class="text-muted mb-0">กำหนดสิทธิ์การใช้งานแต่ละ Role ในระบบ</p>
                    </div>
                </div>

                <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($_SESSION['flash_success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <!-- Create new role form (admin only) -->
                <?php if (AuthMiddleware::hasPermission('roles.manage')): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-plus-circle me-2"></i>สร้าง Role ใหม่</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= \App\Config\Config::SITE_URL ?>/admin/role/create" class="row g-3">
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">รหัส (Code)</label>
                                <input type="text" name="code" class="form-control form-control-sm" required placeholder="เช่น supervisor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">ชื่อ (ภาษาไทย)</label>
                                <input type="text" name="name_th" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">คำอธิบาย</label>
                                <input type="text" name="description" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100 py-2"><i class="fa-solid fa-plus me-1"></i>สร้าง</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Roles Table -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Code</th>
                                        <th>ชื่อ Role</th>
                                        <th>คำอธิบาย</th>
                                        <th class="text-center">เจ้าหน้าที่</th>
                                        <th class="text-center">ประเภท</th>
                                        <?php if (AuthMiddleware::hasPermission('roles.manage')): ?>
                                        <th class="text-center">จัดการสิทธิ์</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td class="text-muted"><?= (int)$role['id'] ?></td>
                                            <td><strong class="text-primary"><?= esc($role['code']) ?></strong></td>
                                            <td><?= esc($role['name_th']) ?></td>
                                            <td class="small text-muted"><?= esc($role['description'] ?? '-') ?></td>
                                            <td class="text-center"><span class="badge bg-info"><?= (int)($role['officer_count'] ?? 0) ?></span></td>
                                            <td class="text-center">
                                                <?php if ($role['is_system']): ?>
                                                    <span class="badge bg-warning text-dark rounded-pill">System</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">Custom</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (AuthMiddleware::hasPermission('roles.manage')): ?>
                                            <td class="text-center">
                                                <a href="<?= \App\Config\Config::SITE_URL ?>/admin/role/edit?id=<?= $role['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
                                                    <i class="fa-solid fa-shield-halved me-1"></i>จัดการสิทธิ์
                                                </a>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center py-5 text-muted">ไม่พบ Role</td></tr>
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
