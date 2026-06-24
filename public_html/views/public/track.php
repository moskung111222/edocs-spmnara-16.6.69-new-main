<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามสถานะคำขอ | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= \App\Config\Config::SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= \App\Config\Config::SITE_URL ?>">
                <div class="logo-vector me-3">NWT</div>
                <div>
                    <span class="brand-text">ระบบยื่นเอกสารออนไลน์</span>
                    <span class="brand-subtitle">สพม.นราธิวาส</span>
                </div>
            </a>
            <div class="ms-auto">
                <a href="<?= \App\Config\Config::SITE_URL ?>" class="btn btn-outline-light btn-sm px-3 py-2 rounded-3">
                    <i class="fa-solid fa-house me-2"></i>กลับหน้าหลัก
                </a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        
        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?= esc($_SESSION['flash_success']) ?>
                <?php unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i><strong>ผิดพลาด:</strong> <?= esc($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!$request): ?>
            <!-- CASE 1: SEARCH FORM -->
            <div class="row justify-content-center py-5">
                <div class="col-md-8 col-lg-6">
                    <div class="card card-premium shadow-lg">
                        <div class="card-header-gradient text-center">
                            <div class="mb-2" style="font-size: 2.5rem;">
                                <i class="fa-solid fa-magnifying-glass-location text-warning"></i>
                            </div>
                             <h4 class="mb-1 fw-bold text-dark-green">ค้นหาและติดตามคำขอ</h4>
                             <p class="mb-0 text-muted">ตรวจสอบสถานะการขออนุญาตและการประเมินผลบ้านเรียน (Homeschool)</p>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <form action="" method="GET" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="no" class="form-label fw-bold">เลขที่คำขอ (Request Number)</label>
                                    <input type="text" 
                                           class="form-control form-control-lg py-3 rounded-3 text-center fw-bold" 
                                           id="no" 
                                           name="no" 
                                           required 
                                           placeholder="เช่น NWT-HS-2569-000001"
                                           value="<?= esc($_GET['no'] ?? '') ?>">
                                    <div class="invalid-feedback">กรุณากรอกเลขที่คำขอเพื่อทำการค้นหา</div>
                                    <div class="form-text text-muted text-center mt-2">โปรดใช้เลขคำขอที่ท่านได้รับหลังการส่งแบบฟอร์มสำเร็จ</div>
                                </div>
                                <button type="submit" class="btn btn-premium w-100 py-3 fs-5 fw-bold shadow">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i> ค้นหาข้อมูลคำขอ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif (!$isAuthenticated): ?>
            <!-- CASE 2: PASSWORD SECURITY FOR TRACKING -->
            <div class="row justify-content-center py-5">
                <div class="col-md-8 col-lg-6">
                    <div class="card card-premium shadow-lg border-warning border-top border-4">
                        <div class="card-body p-4 p-md-5 text-center">
                            <div class="text-warning mb-3" style="font-size: 3rem;">
                                <i class="fa-solid fa-user-lock"></i>
                            </div>
                            <h4 class="fw-bold text-primary mb-3">การตรวจสอบความปลอดภัย</h4>
                            <p class="text-muted mb-4">
                                เพื่อปกป้องข้อมูลส่วนบุคคลของท่าน ระบบจำเป็นต้องตรวจสอบสิทธิ์ในการเข้าถึงคำขอหมายเลข <strong><?= esc($request['request_no']) ?></strong>
                            </p>

                            <form action="" method="POST" class="needs-validation" novalidate>
                                <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                <input type="hidden" name="verify_password" value="1">

                                <div class="mb-4 text-start">
                                    <label for="password" class="form-label fw-bold">กรอกรหัสผ่านเข้าใช้งาน (Password)</label>
                                    <input type="password" 
                                           class="form-control py-3 rounded-3" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           placeholder="ระบุรหัสผ่านบัญชีของท่าน">
                                    <div class="invalid-feedback">กรุณากรอกรหัสผ่านบัญชีเพื่อเข้าสู่ระบบติดตามคำขอ</div>
                                    <div class="form-text text-muted mt-2">
                                        <i class="fa-solid fa-circle-info me-1"></i> หากท่านลืมรหัสผ่าน กรุณาติดต่อฝ่ายสนับสนุนหรือเจ้าหน้าที่ สพม.นราธิวาส เพื่อขอรับรหัสผ่านของท่าน
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-premium w-100 py-3 fs-5 fw-bold shadow">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i>ยืนยันรหัสผ่านเพื่อเข้าถึงข้อมูล
                                </button>
                            </form>
                            
                            <div class="mt-4 border-top pt-3">
                                <a href="<?= \App\Config\Config::SITE_URL ?>/request/track" class="text-decoration-none text-muted small">
                                    <i class="fa-solid fa-rotate-left me-1"></i> ย้อนกลับไปค้นหาหมายเลขอื่น
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- CASE 3: DETAILED TRACKING PORTAL (AUTHENTICATED) -->
            <div class="row g-4">
                <!-- Left Column: Request Details & Timeline -->
                <div class="col-lg-8">
                    <!-- Status Badge Resolver -->
                    <?php 
                        $statusList = \App\Config\Config::getStatusList();
                        $statusName = $statusList[$request['status']] ?? $request['status'];
                        $statusBadgeClass = 'bg-secondary';
                        
                        switch($request['status']) {
                            case 'submitted':
                                $statusBadgeClass = 'bg-secondary';
                                break;
                            case 'received':
                                $statusBadgeClass = 'bg-info text-white';
                                break;
                            case 'in_review':
                                $statusBadgeClass = 'bg-warning text-dark';
                                break;
                            case 'need_info':
                                $statusBadgeClass = 'bg-danger text-white animate-pulse';
                                break;
                            case 'pending_approval':
                                $statusBadgeClass = 'bg-primary text-white';
                                break;
                            case 'approved':
                                $statusBadgeClass = 'bg-success text-white';
                                break;
                            case 'completed':
                                $statusBadgeClass = 'bg-success text-white';
                                break;
                            case 'rejected':
                                $statusBadgeClass = 'bg-danger text-white';
                                break;
                        }
                    ?>

                    <div class="card card-premium shadow-md mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4 gap-3">
                                <div>
                                    <span class="text-muted small">เลขที่คำขอ</span>
                                    <h3 class="fw-bold text-primary mb-0"><?= esc($request['request_no']) ?></h3>
                                </div>
                                <div class="text-md-end">
                                    <span class="text-muted small d-block">สถานะรวมระบบ</span>
                                    <span class="badge <?= $statusBadgeClass ?> px-3 py-2 fs-6 fw-bold rounded-pill"><?= esc($statusName) ?></span>
                                </div>
                            </div>

                            <!-- Process 1 & Process 2 status timelines -->
                            <h5 class="fw-bold mb-4"><i class="fa-solid fa-route text-warning me-2"></i>ขั้นตอนการดำเนินงานแยกตามกระบวนการ</h5>
                            <div class="row g-4 mb-5">
                                <!-- Process 1: Permission -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 shadow-sm rounded-3 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center justify-content-between">
                                                <span><i class="fa-solid fa-file-signature text-warning me-2"></i>กระบวนการ 1: การอนุญาตจัดตั้ง</span>
                                                <?php
                                                    $p1Status = $request['process_1_status'] ?? 'submitted';
                                                    $p1Badge = 'bg-secondary';
                                                    $p1Text = 'ยื่นคำขอแล้ว';
                                                    if ($p1Status === 'document_review') { $p1Badge = 'bg-info text-white'; $p1Text = 'อยู่ระหว่างตรวจเอกสาร'; }
                                                    elseif ($p1Status === 'need_revision') { $p1Badge = 'bg-danger text-white animate-pulse'; $p1Text = 'ขอแก้ไขเอกสาร'; }
                                                    elseif ($p1Status === 'waiting_meeting') { $p1Badge = 'bg-warning text-dark'; $p1Text = 'รอประชุมคณะอนุกรรมการ'; }
                                                    elseif ($p1Status === 'meeting_result_received') { $p1Badge = 'bg-primary text-white'; $p1Text = 'ได้รับผลประชุมแล้ว'; }
                                                    elseif ($p1Status === 'result_notified') { $p1Badge = 'bg-info text-white'; $p1Text = 'แจ้งผลมติการจัดตั้ง'; }
                                                    elseif ($p1Status === 'completed') { $p1Badge = 'bg-success text-white'; $p1Text = 'อนุมัติจัดตั้งสำเร็จ'; }
                                                    elseif ($p1Status === 'rejected') { $p1Badge = 'bg-danger text-white'; $p1Text = 'ปฏิเสธการจัดตั้ง'; }
                                                ?>
                                                <span class="badge <?= $p1Badge ?> rounded-pill font-monospace"><?= $p1Text ?></span>
                                            </h6>
                                            
                                            <!-- Steps Timeline for Process 1 -->
                                            <div class="d-flex justify-content-between align-items-center mt-4 position-relative px-2">
                                                <div class="position-absolute start-0 end-0 bg-secondary" style="height: 3px; top: 12px; z-index: 0; left: 15px; right: 15px;"></div>
                                                <?php
                                                    $p1Step = 1;
                                                    if (in_array($p1Status, ['document_review', 'need_revision'])) $p1Step = 2;
                                                    elseif (in_array($p1Status, ['waiting_meeting', 'meeting_result_received'])) $p1Step = 3;
                                                    elseif ($p1Status === 'result_notified') $p1Step = 4;
                                                    elseif (in_array($p1Status, ['completed', 'rejected'])) $p1Step = 5;
                                                    
                                                    $p1Width = (($p1Step - 1) / 4) * 100;
                                                ?>
                                                <div class="position-absolute start-0 bg-primary" style="height: 3px; top: 12px; width: <?= $p1Width ?>%; z-index: 0; left: 15px; transition: width 0.3s;"></div>
                                                
                                                <!-- Step 1 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;"><i class="fa-solid fa-check fs-8"></i></span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;">ยื่นคำขอ</div>
                                                </div>
                                                <!-- Step 2 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <?php $isStep2Done = $p1Step > 2; $isStep2Active = $p1Step === 2; ?>
                                                    <span class="rounded-circle <?= $isStep2Done ? 'bg-primary text-white' : ($isStep2Active ? 'bg-warning text-dark' : 'bg-secondary text-white') ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($isStep2Done): ?><i class="fa-solid fa-check fs-8"></i><?php else: ?>2<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;">ตรวจเอกสาร</div>
                                                </div>
                                                <!-- Step 3 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <?php $isStep3Done = $p1Step > 3; $isStep3Active = $p1Step === 3; ?>
                                                    <span class="rounded-circle <?= $isStep3Done ? 'bg-primary text-white' : ($isStep3Active ? 'bg-warning text-dark' : 'bg-secondary text-white') ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($isStep3Done): ?><i class="fa-solid fa-check fs-8"></i><?php else: ?>3<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;">ประชุมสรุป</div>
                                                </div>
                                                <!-- Step 4 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <?php $isStep4Done = $p1Step > 4; $isStep4Active = $p1Step === 4; ?>
                                                    <span class="rounded-circle <?= $isStep4Done ? 'bg-primary text-white' : ($isStep4Active ? 'bg-warning text-dark' : 'bg-secondary text-white') ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($isStep4Done): ?><i class="fa-solid fa-check fs-8"></i><?php else: ?>4<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;">แจ้งผลมติ</div>
                                                </div>
                                                <!-- Step 5 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <?php $isStep5Completed = ($p1Status === 'completed'); $isStep5Rejected = ($p1Status === 'rejected'); ?>
                                                    <span class="rounded-circle <?= $isStep5Completed ? 'bg-success text-white' : ($isStep5Rejected ? 'bg-danger text-white' : 'bg-secondary text-white') ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($isStep5Completed): ?><i class="fa-solid fa-check fs-8"></i><?php elseif ($isStep5Rejected): ?><i class="fa-solid fa-xmark fs-8"></i><?php else: ?>5<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;"><?= $p1Status === 'rejected' ? 'ปฏิเสธ' : 'จัดตั้งสำเร็จ' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Process 2: Assessment -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 shadow-sm rounded-3 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center justify-content-between">
                                                <span><i class="fa-solid fa-award text-warning me-2"></i>กระบวนการ 2: การประเมินสัมฤทธิผล</span>
                                                <?php
                                                    $p2Status = $request['process_2_status'] ?? 'not_started';
                                                    $p2Badge = 'bg-secondary';
                                                    $p2Text = 'ยังไม่เริ่ม';
                                                    if ($p2Status === 'waiting_report') { $p2Badge = 'bg-info text-white animate-pulse'; $p2Text = 'รอรายงานจากผู้ปกครอง'; }
                                                    elseif ($p2Status === 'report_submitted') { $p2Badge = 'bg-warning text-dark'; $p2Text = 'ส่งรายงานแล้ว'; }
                                                    elseif ($p2Status === 'report_review') { $p2Badge = 'bg-primary text-white'; $p2Text = 'อยู่ระหว่างตรวจสอบรายงาน'; }
                                                    elseif ($p2Status === 'report_completed') { $p2Badge = 'bg-success text-white'; $p2Text = 'เสร็จสิ้นการประเมิน'; }
                                                    elseif ($p2Status === 'rejected') { $p2Badge = 'bg-danger text-white'; $p2Text = 'ไม่ผ่านเกณฑ์'; }
                                                ?>
                                                <span class="badge <?= $p2Badge ?> rounded-pill font-monospace"><?= $p2Text ?></span>
                                            </h6>
                                            
                                            <!-- Steps Timeline for Process 2 -->
                                            <div class="d-flex justify-content-between align-items-center mt-4 position-relative px-2">
                                                <div class="position-absolute start-0 end-0 bg-secondary" style="height: 3px; top: 12px; z-index: 0; left: 15px; right: 15px;"></div>
                                                <?php
                                                    $p2Step = 0;
                                                    if ($p2Status === 'waiting_report') $p2Step = 1;
                                                    elseif (in_array($p2Status, ['report_submitted', 'report_review'])) $p2Step = 2;
                                                    elseif (in_array($p2Status, ['report_completed', 'rejected'])) $p2Step = 3;
                                                    
                                                    $p2Width = ($p2Step > 0) ? (($p2Step - 1) / 2) * 100 : 0;
                                                ?>
                                                <div class="position-absolute start-0 bg-primary" style="height: 3px; top: 12px; width: <?= $p2Width ?>%; z-index: 0; left: 15px; transition: width 0.3s;"></div>
                                                
                                                <!-- Step 1 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <span class="rounded-circle <?= $p2Step >= 1 ? 'bg-primary text-white' : 'bg-secondary text-white' ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($p2Step >= 1): ?><i class="fa-solid fa-check fs-8"></i><?php else: ?>1<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;">รอรายงานปีการศึกษา</div>
                                                </div>
                                                <!-- Step 2 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <?php $isP2Step2Done = $p2Step > 2; $isP2Step2Active = $p2Step === 2; ?>
                                                    <span class="rounded-circle <?= $isP2Step2Done ? 'bg-primary text-white' : ($isP2Step2Active ? 'bg-warning text-dark' : 'bg-secondary text-white') ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($isP2Step2Done): ?><i class="fa-solid fa-check fs-8"></i><?php else: ?>2<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;">ประเมินผลการเรียนรู้</div>
                                                </div>
                                                <!-- Step 3 -->
                                                <div class="text-center position-relative" style="z-index: 1;">
                                                    <?php $isP2Step3Completed = ($p2Status === 'report_completed'); $isP2Step3Rejected = ($p2Status === 'rejected'); ?>
                                                    <span class="rounded-circle <?= $isP2Step3Completed ? 'bg-success text-white' : ($isP2Step3Rejected ? 'bg-danger text-white' : 'bg-secondary text-white') ?> d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                        <?php if ($isP2Step3Completed): ?><i class="fa-solid fa-check fs-8"></i><?php elseif ($isP2Step3Rejected): ?><i class="fa-solid fa-xmark fs-8"></i><?php else: ?>3<?php endif; ?>
                                                    </span>
                                                    <div class="small text-muted mt-1 fw-bold" style="font-size: 0.7rem;"><?= $p2Status === 'rejected' ? 'ไม่ผ่านประเมิน' : 'ประเมินเสร็จสิ้น' ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic form answers -->
                            <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2 text-primary">
                                <i class="fa-solid fa-circle-info me-2 text-warning"></i> รายละเอียดข้อมูลที่ยื่น
                            </h5>
                            <div class="row g-3 bg-light p-3 rounded-3 mb-4">
                                <div class="col-sm-6">
                                    <strong>ประเภทคำขอ:</strong> <?= esc($request['type_name']) ?>
                                </div>
                                <div class="col-sm-6">
                                    <strong>ผู้ยื่นคำขอ:</strong> <?= esc($request['applicant_name']) ?>
                                </div>
                                <div class="col-sm-6">
                                    <strong>เบอร์โทรศัพท์:</strong> <?= esc($request['applicant_phone']) ?>
                                </div>
                                <div class="col-sm-6">
                                    <strong>อีเมลติดต่อ:</strong> <?= esc($request['applicant_email']) ?>
                                </div>
                                
                                <?php 
                                    $formData = json_decode($request['form_data'], true) ?: []; 
                                    foreach ($formData as $label => $value):
                                ?>
                                    <div class="col-sm-6">
                                        <strong><?= esc($label === 'school_name' ? 'โรงเรียนสุดท้ายที่ศึกษา/ชื่อบ้านเรียน' : ($label === 'grad_year' ? 'ปีการศึกษาที่จัดตั้ง' : ($label === 'purpose' ? 'แผนหลักการเรียนรู้' : $label))) ?>:</strong> 
                                        <?= esc($value) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Attachments List divided by uploader -->
                            <?php 
                                $applicantFiles = [];
                                $officerFiles = [];
                                foreach ($attachments as $file) {
                                    if ($file['uploaded_by'] === 'officer') {
                                        $officerFiles[] = $file;
                                    } else {
                                        $applicantFiles[] = $file;
                                    }
                                }
                            ?>

                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-paperclip text-warning me-2"></i> 1) เอกสารหลักฐานที่แนบโดยผู้ปกครอง</h5>
                            <div class="list-group rounded-3 mb-4">
                                <?php if (!empty($applicantFiles)): ?>
                                    <?php foreach ($applicantFiles as $file): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div>
                                                <i class="fa-regular fa-file-pdf text-danger fs-4 me-2 align-middle"></i>
                                                <span class="fw-bold text-secondary align-middle"><?= esc($file['file_name']) ?></span>
                                                <span class="text-muted small ms-3 align-middle">ขนาด: <?= esc(round($file['file_size'] / (1024*1024), 2)) ?> MB</span>
                                                <span class="text-muted small ms-2 align-middle">| เมื่อ: <?= date('d/m/Y H:i', strtotime($file['created_at'])) ?></span>
                                            </div>
                                            <a href="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $file['id'] ?>&source=request_attachment" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                <i class="fa-solid fa-eye me-1"></i> ดูเอกสาร
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center text-muted py-3">ไม่พบเอกสารหลักฐานที่ส่ง</div>
                                <?php endif; ?>
                            </div>

                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-invoice text-success me-2"></i> 2) หนังสือราชการและเอกสารตอบกลับจากเจ้าหน้าที่</h5>
                            <div class="list-group rounded-3 mb-4">
                                <?php if (!empty($officerFiles)): ?>
                                    <?php foreach ($officerFiles as $file): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                                            <div>
                                                <i class="fa-solid fa-file-pdf text-danger fs-4 me-2 align-middle"></i>
                                                <span class="fw-bold text-dark align-middle"><?= esc($file['file_name']) ?></span>
                                                <span class="badge bg-success ms-2 align-middle">
                                                    <?php 
                                                        if ($file['attachment_type'] === 'official_letter') echo 'หนังสือแจ้งทางการ';
                                                        elseif ($file['attachment_type'] === 'approval_document') echo 'ใบอนุญาตจัดตั้ง';
                                                        elseif ($file['attachment_type'] === 'notification_letter') echo 'หนังสือแจ้งผลการประเมิน';
                                                        else echo 'เอกสารราชการอื่นๆ';
                                                    ?>
                                                </span>
                                                <span class="text-muted small ms-3 align-middle">ขนาด: <?= esc(round($file['file_size'] / (1024*1024), 2)) ?> MB</span>
                                            </div>
                                            <a href="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $file['id'] ?>&source=request_attachment" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 text-white">
                                                <i class="fa-solid fa-download me-1"></i> ดาวน์โหลด
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center text-muted py-3">ยังไม่มีหนังสือราชการที่ตอบกลับในขณะนี้</div>
                                <?php endif; ?>
                            </div>

                            <!-- Meeting Results List -->
                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-users text-teal me-2"></i> 3) สรุปรายงานและมติที่ประชุมคณะอนุกรรมการ</h5>
                            <div class="list-group rounded-3 mb-4">
                                <?php if (!empty($meetingResults)): ?>
                                    <?php foreach ($meetingResults as $meeting): ?>
                                        <div class="list-group-item p-4 bg-white border-teal border-start border-3 mb-2 rounded-3 shadow-sm">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-teal"><i class="fa-regular fa-calendar-check me-2"></i>การประชุมเมื่อวันที่: <?= date('d/m/Y', strtotime($meeting['meeting_date'])) ?></span>
                                                <span class="small text-muted">บันทึกเมื่อ: <?= date('d/m/Y H:i', strtotime($meeting['created_at'])) ?></span>
                                            </div>
                                            <p class="mb-3 text-secondary" style="font-size: 0.95rem; line-height: 1.6;"><?= nl2br(esc($meeting['result_summary'])) ?></p>
                                            <?php if (!empty($meeting['file_name'])): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <a href="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $meeting['id'] ?>&source=meeting" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill">
                                                        <i class="fa-solid fa-file-pdf me-1"></i> เปิดดูเอกสารสรุป/มติที่ประชุม (PDF)
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center text-muted py-3">ยังไม่มีการบันทึกรายงานสรุปการประชุมคณะกรรมการ</div>
                                <?php endif; ?>
                            </div>

                            <!-- Upload Additional docs panel (Only in need_info status) -->
                            <?php if ($request['status'] === \App\Config\Config::STATUS_NEED_INFO): ?>
                                <div class="card border-danger bg-danger bg-opacity-10 rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-danger mb-2"><i class="fa-solid fa-upload me-2 animate-bounce"></i>แนบเอกสารเพิ่มเติมเพื่อแก้ไขข้อผิดพลาด</h5>
                                        <p class="text-secondary small mb-3">โปรดอัปโหลดไฟล์ PDF ฉบับแก้ไขหรือเอกสารเพิ่มเติมตามเหตุผลที่ระบุไว้ เจ้าหน้าที่จะได้รับการแจ้งเตือนและประมวลผลต่อทันที</p>
                                        
                                        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                            <input type="hidden" name="action" value="upload_doc">
                                            
                                            <div class="mb-3">
                                                <input type="file" name="additional_doc" accept="application/pdf" class="form-control" required>
                                                <div class="invalid-feedback">กรุณาเลือกไฟล์ PDF หลักฐานก่อนกดยืนยัน</div>
                                            </div>
                                            <button type="submit" class="btn btn-danger py-2 px-4 fw-bold">
                                                <i class="fa-solid fa-cloud-arrow-up me-2"></i>อัปโหลดไฟล์แก้ข้อเสนอ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Upload Process 2 Learning Report panel -->
                            <?php if (in_array($request['process_2_status'], ['waiting_report', 'report_review'])): ?>
                                <div class="card border-primary bg-primary bg-opacity-10 rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-cloud-arrow-up me-2 animate-bounce"></i>อัปโหลดรายงานผลการจัดการศึกษาประจำปี</h5>
                                        <p class="text-secondary small mb-3">กรุณาแนบเอกสารไฟล์ PDF รายงานการประเมินผลการเรียนรู้ของวิชาและกิจกรรมต่างๆ ของผู้เรียน</p>
                                        
                                        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                            <input type="hidden" name="action" value="upload_report">
                                            
                                            <div class="mb-3">
                                                <input type="file" name="report_file" accept="application/pdf" class="form-control" required>
                                                <div class="invalid-feedback">กรุณาเลือกไฟล์ PDF รายงานผลการประเมิน</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary py-2 px-4 fw-bold text-white">
                                                <i class="fa-solid fa-paper-plane me-2"></i>ส่งรายงานการประเมินผลการศึกษา
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Timeline log history -->
                            <h5 class="fw-bold mt-5 mb-3"><i class="fa-solid fa-history text-warning me-2"></i>ประวัติความคืบหน้าการทำงาน</h5>
                            <div class="timeline-vertical mt-4">
                                <?php foreach (array_reverse($history) as $log): ?>
                                    <?php 
                                        $toName = $statusList[$log['to_status']] ?? $log['to_status']; 
                                        $logDate = date('d/m/Y H:i', strtotime($log['created_at']));
                                        $isRejectedClass = $log['to_status'] === 'rejected' ? 'rejected' : ($log['to_status'] === 'completed' || $log['to_status'] === 'approved' ? 'completed' : '');
                                    ?>
                                    <div class="timeline-vertical-item <?= $isRejectedClass ?>">
                                        <div class="fw-bold text-dark"><?= esc($toName) ?></div>
                                        <div class="text-muted small"><?= esc($logDate) ?> น.</div>
                                        <?php if (!empty($log['reason'])): ?>
                                            <div class="text-danger small mt-1 bg-white p-2 border rounded border-left-4">
                                                <strong>บันทึกเจ้าหน้าที่:</strong> <?= esc($log['reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Live Chat with Officer -->
                <div class="col-lg-4">
                    <div class="card card-premium shadow-md border-top border-primary border-4 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-comments text-warning me-2"></i>กล่องข้อความโต้ตอบ</h5>
                            <span class="text-muted small">สนทนากับเจ้าหน้าที่ สพม.นราธิวาส โดยตรง</span>
                        </div>
                        <div class="card-body d-flex flex-column" style="min-height: 450px;">
                            <!-- Chat Bubble Logs -->
                            <div class="chat-box flex-grow-1 mb-3">
                                <?php if (!empty($messages)): ?>
                                    <?php foreach ($messages as $msg): ?>
                                        <?php 
                                            $isOfficer = $msg['sender_type'] === 'officer'; 
                                            $bubbleClass = $isOfficer ? 'officer' : 'applicant';
                                            $senderName = $isOfficer ? 'เจ้าหน้าที่ สพม.นราธิวาส' : 'คุณ (ผู้ยื่นคำขอ)';
                                        ?>
                                        <div class="chat-bubble <?= $bubbleClass ?>">
                                            <div class="fw-bold small mb-1"><?= esc($senderName) ?></div>
                                            <p class="mb-0 text-break"><?= esc($msg['body']) ?></p>
                                            <span class="chat-meta text-end"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5 my-auto">
                                        <i class="fa-regular fa-comment-dots fs-1 mb-2 d-block opacity-50"></i>
                                        ยังไม่มีประวัติการส่งข้อความโต้ตอบ<br>ท่านสามารถเริ่มส่งข้อความทักทายเจ้าหน้าที่ได้
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Form input message -->
                            <form action="" method="POST" class="needs-validation" novalidate>
                                <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                <input type="hidden" name="action" value="post_message">
                                
                                <div class="input-group">
                                    <input type="text" 
                                           name="body" 
                                           class="form-control rounded-start-pill py-2" 
                                           placeholder="พิมพ์ข้อความที่นี่..." 
                                           required 
                                           autocomplete="off">
                                    <button class="btn btn-premium rounded-end-pill px-3" type="submit">
                                        <i class="fa-regular fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto border-top border-warning border-3">
        <div class="container text-center">
            <p class="mb-1">สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส (สพม.นราธิวาส)</p>
            <p class="text-muted small mb-0">ถนนศูนย์ราชการ ตำบลโคกเคียน อำเภอเมือง จังหวัดนราธิวาส 96000 | เบอร์โทรศัพท์: 073-511-182</p>
            <p class="text-muted small mt-2">&copy; <?= date('Y') ?> NWT Document Submission System. All Rights Reserved.</p>
        </div>
    </footer>

    <?php if ($request && $isAuthenticated): ?>
    <!-- Pusher Realtime Notification for citizen tracking -->
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var pusher = new Pusher('<?= \App\Config\Config::getPusherKey() ?>', {
            cluster: '<?= \App\Config\Config::getPusherCluster() ?>'
        });
        
        var channel = pusher.subscribe('request-<?= $request['request_no'] ?>');
        
        // Listen for real-time chat messages
        channel.bind('new-message', function(data) {
            // Citizens only care about public messages from officer
            if (parseInt(data.internal_note) === 1) return; // skip internal notes
            if (data.sender_type === 'applicant') return; // skip applicant messages since they sent it (already in DOM or handled)
            
            var chatBox = document.querySelector(".chat-box");
            if (!chatBox) return;

            // Remove placeholder if no messages
            var emptyPlaceholder = chatBox.querySelector(".fa-comment-dots");
            if (emptyPlaceholder) {
                var parentDiv = emptyPlaceholder.closest("div");
                if (parentDiv) parentDiv.remove();
            }

            var senderName = 'เจ้าหน้าที่ สพม.นราธิวาส';

            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            var bubbleHtml = `
                <div class="chat-bubble officer">
                    <div class="fw-bold small mb-1">${escapeHtml(senderName)}</div>
                    <p class="mb-0 text-break">${escapeHtml(data.body)}</p>
                    <span class="chat-meta text-end">${escapeHtml(data.created_at)}</span>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', bubbleHtml);
            chatBox.scrollTop = chatBox.scrollHeight;
            
            // Play notification sound
            var audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
            audio.play().catch(function(e) {});
        });

        // Listen for status changes
        channel.bind('status-updated', function(data) {
            var audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
            audio.play().catch(function(e) {});
            
            Swal.fire({
                title: 'อัปเดตสถานะเอกสาร!',
                html: `คำขอของท่านได้รับการปรับปรุงเป็นสถานะ:<br><strong class="text-primary fs-5">${data.status_name}</strong>${data.reason ? '<br><br><b>ข้อความจากเจ้าหน้าที่:</b> ' + data.reason : ''}`,
                icon: 'success',
                confirmButtonText: 'ตกลง (รีโหลดหน้าใหม่)',
                confirmButtonColor: '#1e3a8a'
            }).then(() => {
                window.location.reload();
            });
        });
    </script>
    <?php endif; ?>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <h5 class="fw-bold mb-0">กำลังดำเนินการติดต่อเซิร์ฟเวอร์...</h5>
        <span class="small text-white-50 mt-1">กรุณารอสักครู่</span>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form Validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        } else {
                            document.getElementById('loading-overlay').style.display = 'flex';
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Auto-scroll chat box to bottom
        window.onload = function() {
            var chatBox = document.querySelector(".chat-box");
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }
    </script>
</body>
</html>
