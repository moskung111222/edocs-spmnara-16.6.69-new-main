<?php use App\Middleware\AuthMiddleware; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทบริการ | สพม.นราธิวาส</title>
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
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/services" class="admin-sidebar-link active"><i class="fa-solid fa-clipboard-list me-2"></i> ประเภทบริการ</a>
                        <?php if (AuthMiddleware::hasPermission('officers.view')): ?>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/officers" class="admin-sidebar-link"><i class="fa-solid fa-users-gear me-2"></i> จัดการเจ้าหน้าที่</a>
                        <?php endif; ?>
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
                        <h2 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-clipboard-list me-2"></i>จัดการประเภทบริการ (Document Types)</h2>
                        <p class="text-muted mb-0">เพิ่ม แก้ไข ประเภทคำขอเอกสารและรายการเอกสารที่ต้องแนบ</p>
                    </div>
                </div>

                <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($successMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-2"></i><?= esc($errorMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <!-- Create Form -->
                <?php if (AuthMiddleware::hasPermission('services.create')): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-plus-circle me-2"></i>เพิ่มประเภทบริการใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                            <input type="hidden" name="action" value="create">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">รหัส</label>
                                    <input type="text" name="code" class="form-control form-control-sm" required placeholder="เช่น HS">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">ชื่อประเภทบริการ</label>
                                    <input type="text" name="name_th" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">กลุ่มงานที่รับผิดชอบ</label>
                                    <select name="department_id" class="form-select form-select-sm">
                                        <option value="">-- ไม่ระบุ --</option>
                                        <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= esc($d['name_th']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small fw-bold">ลำดับ</label>
                                    <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">รายการเอกสารที่ต้องแนบ (1 รายการต่อ 1 บรรทัด)</label>
                                    <textarea name="doc_checklist" class="form-control form-control-sm" rows="3" required placeholder="สำเนาบัตรประจำตัวประชาชน&#10;รูปถ่ายหน้าตรง 1.5 นิ้ว"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">คำอธิบาย</label>
                                    <textarea name="description" class="form-control form-control-sm" rows="3"></textarea>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm w-100 py-2"><i class="fa-solid fa-plus me-1"></i>เพิ่ม</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Services Table -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ชื่อประเภทบริการ</th>
                                        <th>กลุ่มงาน</th>
                                        <th>เอกสารที่ต้องแนบ</th>
                                        <th class="text-center">สถานะ</th>
                                        <?php if (AuthMiddleware::hasPermission('services.edit')): ?>
                                        <th class="text-center">จัดการ</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($services)): ?>
                                        <?php foreach ($services as $svc): ?>
                                        <tr>
                                            <td><strong class="text-primary"><?= esc($svc['code']) ?></strong></td>
                                            <td><?= esc($svc['name_th']) ?></td>
                                            <td class="small text-muted"><?= esc($svc['department_name'] ?? '-') ?></td>
                                            <td>
                                                <?php if (!empty($svc['doc_checklist'])): ?>
                                                <ul class="mb-0 ps-3 small">
                                                    <?php foreach ($svc['doc_checklist'] as $doc): ?>
                                                    <li><?= esc($doc) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($svc['active']): ?>
                                                    <span class="badge bg-success rounded-pill">เปิด</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">ปิด</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (AuthMiddleware::hasPermission('services.edit')): ?>
                                            <td class="text-center">
                                                <button class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#editSvcModal<?= $svc['id'] ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1"><i class="fa-solid fa-power-off"></i></button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editSvcModal<?= $svc['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                                        <div class="modal-header"><h5 class="modal-title fw-bold">แก้ไขประเภทบริการ: <?= esc($svc['code']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <div class="modal-body">
                                                            <div class="mb-3"><label class="form-label small fw-bold">ชื่อ</label><input type="text" name="name_th" class="form-control" value="<?= esc($svc['name_th']) ?>" required></div>
                                                            <div class="mb-3"><label class="form-label small fw-bold">คำอธิบาย</label><textarea name="description" class="form-control" rows="2"><?= esc($svc['description'] ?? '') ?></textarea></div>
                                                            <div class="mb-3"><label class="form-label small fw-bold">กลุ่มงาน</label><select name="department_id" class="form-select"><option value="">-- ไม่ระบุ --</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($svc['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name_th']) ?></option><?php endforeach; ?></select></div>
                                                            <div class="mb-3"><label class="form-label small fw-bold">เอกสารที่ต้องแนบ (1 รายการ/บรรทัด)</label><textarea name="doc_checklist" class="form-control" rows="4" required><?= esc(implode("\n", $svc['doc_checklist'] ?? [])) ?></textarea></div>
                                                            <div class="mb-3"><label class="form-label small fw-bold">ลำดับ</label><input type="number" name="sort_order" class="form-control" value="<?= (int)($svc['sort_order'] ?? 0) ?>"></div>
                                                        </div>
                                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>บันทึก</button></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-5 text-muted">ยังไม่มีประเภทบริการ</td></tr>
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
