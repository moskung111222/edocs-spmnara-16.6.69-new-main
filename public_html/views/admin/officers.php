<?php use App\Middleware\AuthMiddleware; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเจ้าหน้าที่ | สพม.นราธิวาส</title>
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
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="admin-sidebar-link active"><i class="fa-solid fa-users-gear me-2"></i> จัดการเจ้าหน้าที่</a>
                        <?php if (AuthMiddleware::hasPermission('roles.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/roles" class="admin-sidebar-link"><i class="fa-solid fa-user-lock me-2"></i> Roles & สิทธิ์</a>
                        <?php endif; ?>
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
                        <h2 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-users-gear me-2"></i>จัดการเจ้าหน้าที่</h2>
                        <p class="text-muted mb-0">ดูรายชื่อ เพิ่ม แก้ไข มอบหมายกลุ่มงานให้เจ้าหน้าที่</p>
                    </div>
                    <?php if (AuthMiddleware::hasPermission('officers.create')): ?>
                    <div>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officer/create" class="btn btn-primary rounded-pill px-4">
                            <i class="fa-solid fa-user-plus me-2"></i>เพิ่มเจ้าหน้าที่ใหม่
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($_SESSION['flash_success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <!-- Filters -->
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">-- กลุ่มงานทั้งหมด --</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name_th']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select form-select-sm">
                            <option value="">-- สิทธิ์ทั้งหมด --</option>
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= esc($r['code']) ?>" <?= ($filters['role'] ?? '') === $r['code'] ? 'selected' : '' ?>><?= esc($r['name_th']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหาชื่อ / username / email" value="<?= esc($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-filter me-1"></i>กรอง</button>
                    </div>
                </form>

                <!-- Officers Table -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>ID</th>
                                        <th>ชื่อ-สกุล</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>สิทธิ์ (Role)</th>
                                        <th>กลุ่มงาน</th>
                                        <th class="text-center">สถานะ</th>
                                        <?php if (AuthMiddleware::hasPermission('officers.edit')): ?>
                                        <th class="text-center">จัดการ</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($officers)): ?>
                                        <?php foreach ($officers as $off): ?>
                                        <tr>
                                            <td class="text-muted"><?= (int)$off['id'] ?></td>
                                            <td><strong><?= esc($off['name']) ?></strong></td>
                                            <td class="text-primary"><?= esc($off['username']) ?></td>
                                            <td class="small"><?= esc($off['email']) ?></td>
                                            <td>
                                                <?php
                                                    $roleBadge = 'bg-secondary';
                                                    if ($off['role'] === 'admin') $roleBadge = 'bg-danger';
                                                    elseif ($off['role'] === 'head') $roleBadge = 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?= $roleBadge ?> rounded-pill"><?= esc($off['role']) ?></span>
                                            </td>
                                            <td class="small"><?= esc($off['department_name'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <?php if (isset($off['active']) && $off['active']): ?>
                                                    <span class="badge bg-success rounded-pill">เปิด</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">ปิด</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (AuthMiddleware::hasPermission('officers.edit')): ?>
                                            <td class="text-center">
                                                <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officer/edit?id=<?= $off['id'] ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1">
                                                    <i class="fa-solid fa-pen-to-square me-1"></i>แก้ไข
                                                </a>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="text-center py-5 text-muted">ไม่พบเจ้าหน้าที่ตามเงื่อนไข</td></tr>
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
