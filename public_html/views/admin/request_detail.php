<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำขอ <?= esc($request['request_no']) ?> | สพม.นราธิวาส</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
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
                
                <!-- Back Link -->
                <div class="mb-3">
                    <a href="<?= \App\Config\Config::SITE_URL ?>/admin/dashboard" class="text-primary text-decoration-none fw-bold">
                        <i class="fa-solid fa-chevron-left me-1"></i> กลับไปคิวแดชบอร์ด
                    </a>
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
                        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>เกิดข้อผิดพลาด:</strong> <?= esc($errorMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Top Header Row -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <span class="text-muted small">รหัสอ้างอิงคำขอ</span>
                        <h2 class="fw-bold mb-0 text-primary"><?= esc($request['request_no']) ?></h2>
                    </div>
                    <?php 
                        $statusList = \App\Config\Config::getStatusList();
                        $statusName = $statusList[$request['status']] ?? $request['status'];
                    ?>
                    <div>
                        <span class="badge bg-primary px-3 py-2 fs-6 fw-bold rounded-pill shadow-sm"><?= esc($statusName) ?></span>
                    </div>
                </div>

                <!-- Three Panels Column Layout -->
                <div class="row g-4">
                    
                    <!-- LEFT COLUMN: PDF View and dynamic preview -->
                    <div class="col-lg-6">
                        <div class="card card-premium shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-primary"><i class="fa-regular fa-file-pdf text-danger me-2"></i>ตรวจสอบเอกสาร PDF</h5>
                                
                                <!-- Attachment Select dropdown -->
                                <?php if (!empty($attachments)): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="attachment-selector" class="form-select form-select-sm w-auto">
                                            <?php foreach ($attachments as $att): ?>
                                                <option value="<?= \App\Config\Config::SITE_URL ?>/download?id=<?= $att['id'] ?>">
                                                    <?= esc($att['file_name']) ?> (v<?= $att['version'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <a id="download-att-btn" href="#" target="_blank" class="btn btn-outline-secondary btn-sm rounded-3 py-1 px-2" title="ดาวน์โหลด / เปิดในแท็บใหม่">
                                            <i class="fa-solid fa-up-right-from-square fs-7"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-danger small">ไม่พบไฟล์หลักฐานแนบ</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3 text-center">
                                <?php if (!empty($attachments)): ?>
                                    <!-- PDF.js Canvas Frame Viewer -->
                                    <div class="pdf-preview-container d-flex flex-column align-items-center justify-content-between py-2">
                                        <div class="d-flex justify-content-center align-items-center w-100 flex-grow-1" style="overflow: auto;">
                                            <canvas id="pdf-canvas" class="border shadow bg-white" style="max-width: 100%; height: auto;"></canvas>
                                        </div>
                                        
                                        <!-- PDF Control Toolbar -->
                                        <div class="bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill d-flex gap-3 align-items-center">
                                            <button class="btn btn-dark btn-sm rounded-circle" id="prev-page-btn"><i class="fa-solid fa-chevron-left"></i></button>
                                            <span class="small">หน้า <span id="page_num">1</span> จาก <span id="page_count">-</span></span>
                                            <button class="btn btn-dark btn-sm rounded-circle" id="next-page-btn"><i class="fa-solid fa-chevron-right"></i></button>
                                            <div class="vr"></div>
                                            <button class="btn btn-dark btn-sm rounded-circle" id="zoom-out-btn"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                                            <button class="btn btn-dark btn-sm rounded-circle" id="zoom-in-btn"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="py-5 text-muted">
                                        <i class="fa-regular fa-file-pdf fs-1 mb-3 opacity-25"></i>
                                        <p>คำขอนี้ไม่มีการแนบไฟล์เอกสารใดๆ</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Applicant & Education Info Card -->
                        <div class="card card-premium shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-circle-info text-warning me-2"></i> ข้อมูลคำขอและการศึกษา</h5>
                            </div>
                            <div class="card-body p-4">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="fw-bold text-secondary" style="width: 35%;">ชื่อ-นามสกุลจริง:</td>
                                        <td><strong><?= esc($request['applicant_name']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-secondary">อีเมลติดต่อ:</td>
                                        <td><a href="mailto:<?= esc($request['applicant_email']) ?>"><?= esc($request['applicant_email']) ?></a></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-secondary">เบอร์โทรศัพท์:</td>
                                        <td><?= esc($request['applicant_phone']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-secondary">ประเภทคำขอ:</td>
                                        <td><span class="badge bg-secondary"><?= esc($request['type_code']) ?></span> <?= esc($request['type_name']) ?></td>
                                    </tr>
                                    
                                    <?php 
                                        $formData = json_decode($request['form_data'], true) ?: [];
                                        foreach ($formData as $label => $value):
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-secondary">
                                                <?= esc($label === 'school_name' ? 'โรงเรียนสุดท้ายที่ศึกษา' : ($label === 'grad_year' ? 'ปีการศึกษาที่จบ' : ($label === 'purpose' ? 'วัตถุประสงค์' : $label))) ?>:
                                            </td>
                                            <td><?= esc($value) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <tr>
                                        <td class="fw-bold text-secondary">ผู้ตรวจคิวปัจจุบัน:</td>
                                        <td><span class="text-primary fw-bold"><?= esc($request['officer_name'] ?? 'ไม่มีผู้รับมอบหมาย') ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- MIDDLE COLUMN: Administrative Action forms -->
                    <div class="col-lg-3">
                        <!-- 1. Change Status Card -->
                        <div class="card card-premium shadow-sm mb-4 border-warning border-top border-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold mb-0 text-warning"><i class="fa-solid fa-list-check me-2"></i>จัดการผลตรวจสอบ</h5>
                            </div>
                            <div class="card-body p-3">
                                <form action="" method="POST" class="needs-validation" novalidate>
                                    <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                    <input type="hidden" name="action" value="change_status">
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label small fw-bold">เลือกสถานะใหม่ <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" id="status" name="status" required>
                                            <option value="" disabled selected>-- เลือกสถานะ --</option>
                                            <?php foreach (\App\Config\Config::getStatusList() as $key => $val): ?>
                                                <!-- If role is staff, disable approved -->
                                                <?php 
                                                    $disabled = ($key === 'approved' && !in_array($_SESSION['officer_role'], ['admin', 'head'])) ? 'disabled' : ''; 
                                                ?>
                                                <option value="<?= $key ?>" <?= $request['status'] === $key ? 'selected' : '' ?> <?= $disabled ?>>
                                                    <?= esc($val) ?> <?= $disabled ? ' (สิทธิ์ไม่ถึง)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกสถานะสำหรับการปรับปรุง</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reason" class="form-label small fw-bold">เหตุผล/คำชี้แจง (ส่งไปยังผู้ยื่นคำขอ) <span class="text-danger" id="reason-required" style="display: none;">*</span></label>
                                        <textarea class="form-control form-control-sm" id="reason" name="reason" rows="4" 
                                                  placeholder="รายละเอียดคำชี้แจงแก้ไขเอกสาร หรือปฏิเสธคำขอ"></textarea>
                                        <div class="form-text text-muted small">หากเลือกต้องการแก้ไขเอกสาร (need_info) หรือปฏิเสธคำขอ (rejected) ต้องกรอกข้อมูลส่วนนี้</div>
                                    </div>

                                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold btn-sm py-2">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>บันทึกสถานะใหม่
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- 2. Assign Officer Card -->
                        <?php if (in_array($_SESSION['officer_role'], ['admin', 'head'])): ?>
                            <div class="card card-premium shadow-sm border-primary border-top border-3">
                                <div class="card-header bg-white py-3">
                                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-user-tag me-2"></i> มอบหมายผู้ดูแลคิว</h5>
                                </div>
                                <div class="card-body p-3">
                                    <form action="" method="POST">
                                        <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                        <input type="hidden" name="action" value="assign_officer">
                                        
                                        <div class="mb-3">
                                            <label for="officer_id" class="form-label small fw-bold">เลือกเจ้าหน้าที่เจ้าของสำนวน</label>
                                            <select class="form-select form-select-sm" id="officer_id" name="officer_id">
                                                <option value="">-- ยกเลิกการมอบหมาย --</option>
                                                <?php foreach ($officers as $off): ?>
                                                    <option value="<?= $off['id'] ?>" <?= $request['assigned_officer_id'] == $off['id'] ? 'selected' : '' ?>>
                                                        <?= esc($off['name']) ?> (<?= esc($off['role']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 fw-bold btn-sm py-2">
                                            <i class="fa-solid fa-user-plus me-1"></i>บันทึกการมอบหมาย
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- RIGHT COLUMN: Messaging & Internal Notes panel -->
                    <div class="col-lg-3">
                        <div class="card card-premium shadow-sm border-top border-primary border-3 h-100 d-flex flex-column" style="min-height: 480px;">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-comments me-2"></i>ระบบสื่อสาร/จดบันทึก</h5>
                                <span class="text-muted small">สนทนากับประชาชน หรือบันทึกข้อความภายใน</span>
                            </div>
                            <div class="card-body d-flex flex-column p-2">
                                <!-- Chat list -->
                                <div class="chat-box flex-grow-1 mb-3" style="max-height: 380px;">
                                    <?php if (!empty($messages)): ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <?php 
                                                $isOfficer = $msg['sender_type'] === 'officer';
                                                $isInternal = (int)$msg['internal_note'] === 1;
                                                
                                                if ($isInternal) {
                                                    $bubbleClass = 'internal';
                                                    $senderName = 'บันทึกข้อความภายในกลุ่มงาน';
                                                } elseif ($isOfficer) {
                                                    $bubbleClass = 'officer';
                                                    $senderName = 'เจ้าหน้าที่ สพม.นราธิวาส (ตอบกลับ)';
                                                } else {
                                                    $bubbleClass = 'applicant';
                                                    $senderName = 'ประชาชนผู้ยื่นคำขอ';
                                                }
                                            ?>
                                            <div class="chat-bubble <?= $bubbleClass ?>">
                                                <div class="fw-bold small mb-1"><?= esc($senderName) ?></div>
                                                <p class="mb-0 text-break"><?= esc($msg['body']) ?></p>
                                                <span class="chat-meta text-end"><?= date('d/M H:i', strtotime($msg['created_at'])) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-5 my-auto">
                                            <i class="fa-regular fa-comment-dots fs-1 mb-2 d-block opacity-50"></i>
                                            ยังไม่มีการสนทนาในคำขอนี้
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Form send -->
                                <form action="" method="POST" class="needs-validation px-2 pb-2" novalidate>
                                    <?= \App\Middleware\CsrfMiddleware::getHtmlField() ?>
                                    <input type="hidden" name="action" value="post_message">
                                    
                                    <div class="mb-2">
                                        <input type="text" name="body" class="form-control form-control-sm" placeholder="พิมพ์ข้อความที่นี่..." required autocomplete="off">
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2 small text-start">
                                        <input class="form-check-input" type="checkbox" role="switch" id="internal_note" name="internal_note" value="1">
                                        <label class="form-check-input-label text-warning fw-bold" for="internal_note">
                                            <i class="fa-solid fa-lock me-1"></i>จดบันทึกภายในกลุ่มงาน (ประชาชนไม่เห็น)
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-premium w-100 btn-sm">
                                        <i class="fa-regular fa-paper-plane me-1"></i>ส่งข้อความ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- PDFjs controls script -->
    <?php if (!empty($attachments)): ?>
        <script>
            // Initialize PDF.js
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

            let pdfDoc = null,
                pageNum = 1,
                pageRendering = false,
                pageNumPending = null,
                scale = 1.0,
                canvas = document.getElementById('pdf-canvas'),
                ctx = canvas.getContext('2d');

            function renderPage(num) {
                pageRendering = true;
                pdfDoc.getPage(num).then(function(page) {
                    let viewport = page.getViewport({scale: scale});
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    let renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    let renderTask = page.render(renderContext);

                    renderTask.promise.then(function() {
                        pageRendering = false;
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });

                document.getElementById('page_num').textContent = num;
            }

            function queueRenderPage(num) {
                if (pageRendering) {
                    pageNumPending = num;
                } else {
                    renderPage(num);
                }
            }

            function onPrevPage() {
                if (pageNum <= 1) return;
                pageNum--;
                queueRenderPage(pageNum);
            }

            function onNextPage() {
                if (pageNum >= pdfDoc.numPages) return;
                pageNum++;
                queueRenderPage(pageNum);
            }

            function zoomOut() {
                if (scale <= 0.5) return;
                scale -= 0.1;
                queueRenderPage(pageNum);
            }

            function zoomIn() {
                if (scale >= 3.0) return;
                scale += 0.1;
                queueRenderPage(pageNum);
            }

            function loadPDF(url) {
                pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    document.getElementById('page_count').textContent = pdfDoc.numPages;
                    pageNum = 1;
                    renderPage(pageNum);
                }).catch(function(error) {
                    console.error("PDF.js load error: ", error);
                });
            }

            // Bind triggers
            document.getElementById('prev-page-btn').addEventListener('click', onPrevPage);
            document.getElementById('next-page-btn').addEventListener('click', onNextPage);
            document.getElementById('zoom-out-btn').addEventListener('click', zoomOut);
            document.getElementById('zoom-in-btn').addEventListener('click', zoomIn);

            // Document selector trigger
            const selector = document.getElementById('attachment-selector');
            const downloadBtn = document.getElementById('download-att-btn');
            const forceDownloadBtn = document.getElementById('force-download-att-btn');
            if (selector) {
                selector.addEventListener('change', function() {
                    loadPDF(this.value);
                    if (downloadBtn) downloadBtn.href = this.value;
                    if (forceDownloadBtn) forceDownloadBtn.href = this.value + '&download=1';
                });
                
                // Initial load
                loadPDF(selector.value);
                if (downloadBtn) downloadBtn.href = selector.value;
                if (forceDownloadBtn) forceDownloadBtn.href = selector.value + '&download=1';
            }
        </script>
    <?php endif; ?>

    <script>
        // Enforce validation rule on status change form
        const statusSelect = document.getElementById('status');
        const reasonRequiredText = document.getElementById('reason-required');
        const reasonInput = document.getElementById('reason');

        if (statusSelect && reasonRequiredText && reasonInput) {
            statusSelect.addEventListener('change', function() {
                const val = this.value;
                if (val === 'need_info' || val === 'rejected') {
                    reasonRequiredText.style.display = 'inline';
                    reasonInput.setAttribute('required', 'required');
                } else {
                    reasonRequiredText.style.display = 'none';
                    reasonInput.removeAttribute('required');
                }
            });
        }

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
                            // Show loading spinner
                            var overlay = document.getElementById('loading-overlay');
                            if (overlay) overlay.style.display = 'flex';
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
    <!-- Pusher Realtime Notification -->
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script>
        var pusher = new Pusher('<?= \App\Config\Config::getPusherKey() ?>', {
            cluster: '<?= \App\Config\Config::getPusherCluster() ?>'
        });
        
        var channel = pusher.subscribe('request-<?= $request['request_no'] ?>');
        
        // Listen for real-time chat messages
        channel.bind('new-message', function(data) {
            var chatBox = document.querySelector(".chat-box");
            if (!chatBox) return;

            // Remove placeholder if no messages
            var emptyPlaceholder = chatBox.querySelector(".fa-comment-dots");
            if (emptyPlaceholder) {
                // Find parent of emptyPlaceholder which is the div inside chat-box
                var parentDiv = emptyPlaceholder.closest("div");
                if (parentDiv) parentDiv.remove();
            }

            var bubbleClass = '';
            var senderName = '';
            if (parseInt(data.internal_note) === 1) {
                bubbleClass = 'internal';
                senderName = 'บันทึกข้อความภายในกลุ่มงาน';
            } else if (data.sender_type === 'officer') {
                bubbleClass = 'officer';
                senderName = 'เจ้าหน้าที่ สพม.นราธิวาส (ตอบกลับ)';
            } else {
                bubbleClass = 'applicant';
                senderName = 'ประชาชนผู้ยื่นคำขอ';
            }

            // Simple HTML escape function for dynamic appending
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
                <div class="chat-bubble ${bubbleClass}">
                    <div class="fw-bold small mb-1">${escapeHtml(senderName)}</div>
                    <p class="mb-0 text-break">${escapeHtml(data.body)}</p>
                    <span class="chat-meta text-end">${escapeHtml(data.created_at)}</span>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', bubbleHtml);
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        // Listen for status changes
        channel.bind('status-updated', function(data) {
            // Play notification sound
            var audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
            audio.play().catch(function(e) {});
            
            // Reload page to reflect new status
            window.location.reload();
        });
    </script>
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <h5 class="fw-bold mb-0">กำลังดำเนินการบันทึกข้อมูล...</h5>
        <span class="small text-white-50 mt-1">กรุณารอสักครู่</span>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
