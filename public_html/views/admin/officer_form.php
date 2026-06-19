<?php use App\Middleware\AuthMiddleware; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editMode ? 'แก้ไขเจ้าหน้าที่' : 'เพิ่มเจ้าหน้าที่ใหม่' ?> | สพม.นราธิวาส</title>
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
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="admin-sidebar-link active"><i class="fa-solid fa-users-gear me-2"></i> จัดการเจ้าหน้าที่</a>
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
                            <i class="fa-solid fa-<?= $editMode ? 'user-pen' : 'user-plus' ?> me-2"></i>
                            <?= $editMode ? 'แก้ไขเจ้าหน้าที่: ' . esc($officer['name']) : 'เพิ่มเจ้าหน้าที่ใหม่' ?>
                        </h2>
                    </div>
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i>กลับ
                    </a>
                </div>

                <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($successMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= esc($errorMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST">
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                            <?php if ($editMode): ?>
                            <input type="hidden" name="action" value="update">
                            <?php endif; ?>

                            <div class="row g-3">
                                <!-- Username -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ชื่อผู้ใช้งาน (Username)</label>
                                    <?php if ($editMode): ?>
                                    <input type="text" class="form-control bg-light" value="<?= esc($officer['username']) ?>" disabled>
                                    <?php else: ?>
                                    <input type="text" name="username" class="form-control" required placeholder="username">
                                    <?php endif; ?>
                                </div>

                                <!-- Password -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold"><?= $editMode ? 'รหัสผ่านใหม่ (เว้นว่างหากไม่เปลี่ยน)' : 'รหัสผ่าน' ?></label>
                                    <input type="password" name="password" class="form-control" <?= $editMode ? '' : 'required' ?> minlength="6" placeholder="อย่างน้อย 6 ตัวอักษร">
                                </div>

                                <!-- Name -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ชื่อ-สกุล</label>
                                    <input type="text" name="name" class="form-control" required value="<?= esc($officer['name'] ?? '') ?>">
                                </div>

                                <!-- Email -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">อีเมล</label>
                                    <input type="email" name="email" class="form-control" required value="<?= esc($officer['email'] ?? '') ?>">
                                </div>

                                <!-- Role -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">สิทธิ์ (Role)</label>
                                    <select name="role" class="form-select" required>
                                        <?php foreach ($roles as $r): ?>
                                        <option value="<?= esc($r['code']) ?>" <?= (($officer['role'] ?? 'staff') === $r['code']) ? 'selected' : '' ?>>
                                            <?= esc($r['name_th']) ?> (<?= esc($r['code']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Primary Department -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">กลุ่มงานหลัก</label>
                                    <select name="department_id" class="form-select">
                                        <option value="">-- ไม่ระบุ --</option>
                                        <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= (($officer['department_id'] ?? '') == $d['id']) ? 'selected' : '' ?>>
                                            <?= esc($d['name_th']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Department Assignments (M:N) -->
                            <hr>
                            <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-building-user me-2"></i>มอบหมายกลุ่มงาน (สามารถเลือกหลายกลุ่ม)</h5>
                            <?php
                                $assignedDeptIds = array_column($officerDepartments, 'id');
                                $headDeptId = null;
                                foreach ($officerDepartments as $od) {
                                    if (!empty($od['is_head'])) $headDeptId = $od['id'];
                                }
                            ?>
                            <div class="row g-2 mb-3">
                                <?php foreach ($departments as $d): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="departments[]" value="<?= $d['id'] ?>" 
                                               id="dept_<?= $d['id'] ?>" <?= in_array($d['id'], $assignedDeptIds) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="dept_<?= $d['id'] ?>"><?= esc($d['name_th']) ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Head Department Selection -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-warning"><i class="fa-solid fa-crown me-2"></i>เป็นหัวหน้าของกลุ่มงาน</label>
                                <select name="head_department_id" class="form-select form-select-sm" style="max-width: 400px;">
                                    <option value="">-- ไม่เป็นหัวหน้ากลุ่มงานใด --</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= ($headDeptId == $d['id']) ? 'selected' : '' ?>>
                                        <?= esc($d['name_th']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fa-solid fa-save me-2"></i><?= $editMode ? 'บันทึกการแก้ไข' : 'สร้างเจ้าหน้าที่' ?>
                                </button>

                                <?php if ($editMode): ?>
                                <!-- Toggle Active -->
                                </form>
                                <form method="POST">
                                    <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <button type="submit" class="btn btn-outline-secondary px-4 py-2">
                                        <i class="fa-solid fa-power-off me-2"></i>
                                        <?= (isset($officer['active']) && $officer['active']) ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน' ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                </form>
                                <?php endif; ?>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
