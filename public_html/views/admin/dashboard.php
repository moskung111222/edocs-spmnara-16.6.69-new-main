<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบควบคุมผู้ดูแลระบบ | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- jQuery (Required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard" class="admin-sidebar-link active">
                            <i class="fa-solid fa-chart-pie me-2"></i> แดชบอร์ดสถิติ
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard?status=submitted" class="admin-sidebar-link">
                            <i class="fa-solid fa-inbox me-2"></i> คำขอรอดำเนินการ
                        </a>
                        <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard?status=need_info" class="admin-sidebar-link text-warning">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> รอคำชี้แจงเพิ่ม
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
                
                <!-- Welcome Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="fw-bold mb-0 text-primary">ภาพรวมระบบยื่นคำขอเอกสารออนไลน์</h2>
                        <p class="text-muted mb-0">ระบบสนับสนุนงานบริการประชาชน สพม.นราธิวาส</p>
                    </div>
                    <div>
                        <a href="<?= \App\Config\Config::SITE_URL ?>" target="_blank" class="btn btn-outline-primary rounded-pill">
                            <i class="fa-solid fa-globe me-1"></i> หน้าเว็บประชาชน
                        </a>
                    </div>
                </div>

                <!-- Statistics Box Dashboard Row -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-2">
                        <div class="stat-box stat-box-total">
                            <div>
                                <span class="stat-label d-block text-muted">ทั้งหมด</span>
                                <span class="stat-number"><?= $counts['total'] ?></span>
                            </div>
                            <div class="fs-1 opacity-25"><i class="fa-solid fa-folder-open"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <a href="?status=submitted" class="text-decoration-none">
                            <div class="stat-box stat-box-submitted">
                                <div>
                                    <span class="stat-label d-block text-muted">ยื่นคำขอ</span>
                                    <span class="stat-number"><?= $counts['submitted'] ?></span>
                                </div>
                                <div class="fs-1 opacity-25"><i class="fa-solid fa-file-export"></i></div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-2">
                        <a href="?status=received" class="text-decoration-none">
                            <div class="stat-box stat-box-received">
                                <div>
                                    <span class="stat-label d-block text-muted">รับเรื่อง</span>
                                    <span class="stat-number"><?= $counts['received'] ?></span>
                                </div>
                                <div class="fs-1 opacity-25"><i class="fa-solid fa-envelope-open-text"></i></div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-2">
                        <a href="?status=in_review" class="text-decoration-none">
                            <div class="stat-box stat-box-review">
                                <div>
                                    <span class="stat-label d-block text-muted">กำลังตรวจ</span>
                                    <span class="stat-number"><?= $counts['in_review'] + $counts['need_info'] ?></span>
                                </div>
                                <div class="fs-1 opacity-25"><i class="fa-solid fa-magnifying-glass"></i></div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-2">
                        <a href="?status=approved" class="text-decoration-none">
                            <div class="stat-box stat-box-approved">
                                <div>
                                    <span class="stat-label d-block text-muted">อนุมัติแล้ว</span>
                                    <span class="stat-number"><?= $counts['approved'] + $counts['completed'] ?></span>
                                </div>
                                <div class="fs-1 opacity-25"><i class="fa-solid fa-circle-check"></i></div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-2">
                        <a href="?status=rejected" class="text-decoration-none">
                            <div class="stat-box stat-box-rejected">
                                <div>
                                    <span class="stat-label d-block text-muted">ปฏิเสธ</span>
                                    <span class="stat-number"><?= $counts['rejected'] ?></span>
                                </div>
                                <div class="fs-1 opacity-25"><i class="fa-solid fa-circle-xmark"></i></div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Charts Container Row -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm h-100 bg-white rounded-3 border">
                            <h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>แยกตามประเภทคำขอเอกสาร</h5>
                            <div style="max-height: 280px; position: relative;" class="d-flex justify-content-center">
                                <canvas id="typeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm h-100 bg-white rounded-3 border">
                            <h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-chart-line me-2"></i>จำนวนการยื่นคำขอรายเดือน (12 เดือนที่ผ่านมา)</h5>
                            <div style="max-height: 280px; position: relative;">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2: Statuses and KPI/Performance -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm h-100 bg-white rounded-3 border">
                            <h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-circle-nodes me-2"></i>สัดส่วนสถานะคำขอเอกสาร</h5>
                            <div style="height: 280px; position: relative;" class="d-flex justify-content-center">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm h-100 bg-white rounded-3 border">
                            <h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-users-gear me-2"></i>KPI และจำนวนงานที่ทำเสร็จแยกตามเจ้าหน้าที่</h5>
                            <div class="row g-2 align-items-center" style="min-height: 280px;">
                                <div class="col-5 border-end d-flex flex-column justify-content-around p-2 gap-3">
                                    <div class="text-center">
                                        <span class="d-block text-muted small fw-bold">เวลารอเฉลี่ย</span>
                                        <h3 class="fw-bold text-teal mb-0"><?= esc($kpis['avg_hours']) ?> <span class="fs-6 fw-normal text-muted">ชม.</span></h3>
                                    </div>
                                    <div class="text-center">
                                        <span class="d-block text-muted small fw-bold">ทำตาม SLA (72 ชม.)</span>
                                        <h3 class="fw-bold text-success mb-0"><?= esc($kpis['sla_percent']) ?>%</h3>
                                    </div>
                                    <div class="text-center">
                                        <span class="d-block text-muted small fw-bold">อัตราความสำเร็จ</span>
                                        <h3 class="fw-bold text-primary mb-0"><?= esc($kpis['success_rate']) ?>%</h3>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div style="height: 280px; position: relative;">
                                        <canvas id="officerPerformanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Queue Table Filters -->
                <div class="card card-premium shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-list-check me-2"></i>รายการคิวตรวจรับคำขอเอกสาร</h5>
                        <div class="text-muted small">พบคำขอในเงื่อนไขทั้งหมด: <strong><?= count($requests) ?></strong> รายการ</div>
                    </div>
                    <div class="card-body p-3">
                        
                        <!-- Search & Filter Form -->
                        <form action="" method="GET" class="row g-2 mb-3 align-items-end">
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label small fw-bold">กรองตามสถานะ</label>
                                <select class="form-select form-select-sm rounded-3" id="filter_status" name="status">
                                    <option value="">-- แสดงทั้งหมด --</option>
                                    <?php foreach (\App\Config\Config::getStatusList() as $key => $val): ?>
                                        <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= esc($val) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_officer" class="form-label small fw-bold">กรองผู้รับผิดชอบ</label>
                                <select class="form-select form-select-sm rounded-3" id="filter_officer" name="officer_id">
                                    <option value="">-- แสดงทั้งหมด --</option>
                                    <?php foreach ($officers as $off): ?>
                                        <option value="<?= $off['id'] ?>" <?= $filters['officer_id'] == $off['id'] ? 'selected' : '' ?>><?= esc($off['name']) ?> (<?= esc($off['role']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label small fw-bold">คำค้นหา</label>
                                <input type="text" class="form-control form-control-sm rounded-3" id="search" name="search" 
                                       placeholder="เลขคำขอ / ชื่อประชาชน / อีเมล" value="<?= esc($filters['search']) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3 py-2 fw-bold">
                                    <i class="fa-solid fa-filter me-1"></i> กรองคิว
                                </button>
                            </div>
                        </form>

                        <!-- Queue Table -->
                        <div class="table-responsive rounded-3 border text-dark p-3 bg-white">
                            <table id="requestsTable" class="table table-hover align-middle mb-0 w-100">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th scope="col" style="width: 15%;">เลขที่คำขอ</th>
                                        <th scope="col" style="width: 25%;">ประเภทคำขอ</th>
                                        <th scope="col" style="width: 20%;">ผู้ยื่นคำขอ</th>
                                        <th scope="col" style="width: 15%;">วันที่ยื่น</th>
                                        <th scope="col" style="width: 10%;">สถานะ</th>
                                        <th scope="col" style="width: 15%;">ผู้รับผิดชอบ</th>
                                        <th scope="col" style="width: 10%;" class="text-center">ตรวจสอบ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($requests)): ?>
                                        <?php foreach ($requests as $req): ?>
                                            <?php 
                                                $statusList = \App\Config\Config::getStatusList();
                                                $statusName = $statusList[$req['status']] ?? $req['status'];
                                                $badgeClass = 'bg-secondary';
                                                
                                                switch($req['status']) {
                                                    case 'submitted': $badgeClass = 'bg-secondary'; break;
                                                    case 'received': $badgeClass = 'bg-info text-white'; break;
                                                    case 'in_review': $badgeClass = 'bg-warning text-dark'; break;
                                                    case 'need_info': $badgeClass = 'bg-danger text-white'; break;
                                                    case 'pending_approval': $badgeClass = 'bg-primary text-white'; break;
                                                    case 'approved': $badgeClass = 'bg-success text-white'; break;
                                                    case 'completed': $badgeClass = 'bg-success text-white'; break;
                                                    case 'rejected': $badgeClass = 'bg-danger text-white'; break;
                                                }
                                            ?>
                                            <tr>
                                                <td><strong class="text-primary"><?= esc($req['request_no']) ?></strong></td>
                                                <td><span class="small"><?= esc($req['type_name']) ?></span></td>
                                                <td>
                                                    <div class="fw-bold small"><?= esc($req['applicant_name']) ?></div>
                                                </td>
                                                <td><span class="small text-muted"><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></span></td>
                                                <td>
                                                    <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill small"><?= esc($statusName) ?></span>
                                                </td>
                                                <td>
                                                    <span class="small text-secondary"><?= esc($req['officer_name'] ?? 'ยังไม่มอบหมาย') ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/request?id=<?= $req['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
                                                        เปิดตรวจ <i class="fa-solid fa-magnifying-glass ms-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="fa-regular fa-folder-open fs-2 mb-2 d-block opacity-50"></i>
                                                ไม่พบข้อมูลรายการคำขอที่ตรวจค้นในขณะนี้
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

    <!-- Chart rendering javascript logic -->
    <script>
        // Graph 1: Request Types representation (Pie Chart)
        var typeData = <?= json_encode($typeCounts) ?>;
        var typeLabels = typeData.map(item => item.label);
        var typeValues = typeData.map(item => item.value);

        var ctx1 = document.getElementById('typeChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: ['#1e3a8a', '#d97706', '#10b981', '#ef4444', '#0ea5e9', '#8b5cf6'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Sarabun', size: 11 }
                        }
                    }
                }
            }
        });

        // Graph 2: Monthly trend counts (Bar Chart)
        var monthlyData = <?= json_encode($monthlyCounts) ?>;
        var monthLabels = monthlyData.map(item => item.month);
        var monthValues = monthlyData.map(item => item.count);

        var ctx2 = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'จำนวนคำขอ (คำขอ)',
                    data: monthValues,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Sarabun' } }
                    },
                    x: {
                        ticks: { font: { family: 'Sarabun' } }
                    }
                }
            }
        });

        // Graph 3: Request Statuses representation (Polar Area Chart)
        var statusCounts = <?= json_encode($counts) ?>;
        var statusLabels = ['ยื่นคำขอ', 'รับเรื่อง', 'กำลังตรวจ', 'รอข้อมูลเพิ่ม', 'รออนุมัติ', 'อนุมัติ/เสร็จสิ้น', 'ปฏิเสธ'];
        var statusValues = [
            statusCounts.submitted || 0,
            statusCounts.received || 0,
            statusCounts.in_review || 0,
            statusCounts.need_info || 0,
            statusCounts.pending_approval || 0,
            (statusCounts.approved || 0) + (statusCounts.completed || 0),
            statusCounts.rejected || 0
        ];

        var ctx3 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx3, {
            type: 'polarArea',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: [
                        '#94a3b8', // submitted (slate)
                        '#0ea5e9', // received (info)
                        '#f59e0b', // in_review (warning)
                        '#ef4444', // need_info (danger)
                        '#6366f1', // pending_approval (indigo)
                        '#10b981', // approved + completed (success)
                        '#dc2626'  // rejected (red-600)
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Sarabun', size: 10 } }
                    }
                }
            }
        });

        // Graph 4: Officer Performance (Horizontal Bar Chart)
        var perfData = <?= json_encode($kpis['officer_performance']) ?>;
        var perfLabels = perfData.map(item => item.name);
        var perfValues = perfData.map(item => item.count);

        var ctx4 = document.getElementById('officerPerformanceChart').getContext('2d');
        new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: perfLabels,
                datasets: [{
                    label: 'งานที่ทำเสร็จ (คำขอ)',
                    data: perfValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 1.5,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Sarabun' } }
                    },
                    y: {
                        ticks: { font: { family: 'Sarabun', size: 10 } }
                    }
                }
            }
        });
    </script>
    <!-- DataTables JS & Extensions -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    
    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#requestsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                },
                dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-2"></i>ส่งออก Excel',
                        className: 'btn btn-success btn-sm rounded-3 px-3 py-2 fw-bold shadow-sm',
                        title: 'รายการคำขอเอกสารออนไลน์_สพม_นราธิวาส_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    }
                ],
                order: [[3, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 6 }
                ],
                pageLength: 10,
                responsive: true
            });
        });
    </script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Pusher Realtime Notification -->
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Pusher Client
        var pusher = new Pusher('<?= \App\Config\Config::getPusherKey() ?>', {
            cluster: '<?= \App\Config\Config::getPusherCluster() ?>'
        });

        // Listen for new requests
        var channel = pusher.subscribe('admin-channel');
        channel.bind('new-request', function(data) {
            // Play elegant subtle sound
            var audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
            audio.play().catch(function(e) {});

            Swal.fire({
                title: 'มีคำขอใหม่ยื่นเข้ามา!',
                html: `<b>เลขที่คำขอ:</b> ${data.request_no}<br><b>ผู้ยื่น:</b> ${data.applicant_name}<br><small>${data.type_name}</small>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'รีโหลดหน้าใหม่เพื่อตรวจสอบ',
                cancelButtonText: 'ปิด',
                confirmButtonColor: '#1e3a8a',
                cancelButtonColor: '#6c757d',
                toast: true,
                position: 'top-end',
                timer: 15000,
                timerProgressBar: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        });
    </script>
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <h5 class="fw-bold mb-0">กำลังกรองข้อมูลแดชบอร์ด...</h5>
        <span class="small text-white-50 mt-1">กรุณารอสักครู่</span>
    </div>

    <script>
        // Trigger loading overlay on form submissions
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var overlay = document.getElementById('loading-overlay');
                if (overlay) overlay.style.display = 'flex';
            });
        });
    </script>
</body>
</html>
