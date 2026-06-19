<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Request {
    /**
     * Create a new request.
     * @param string $requestNo
     * @param int $typeId
     * @param int $applicantId
     * @param array $formData
     * @return int Inserted ID
     * @throws Exception
     */
    public static function create($requestNo, $typeId, $applicantId, array $formData) {
        $db = Database::getConnection();
        $formDataJson = json_encode($formData, JSON_UNESCAPED_UNICODE);
        
        $stmt = $db->prepare("INSERT INTO requests (request_no, type_id, applicant_id, status, form_data) VALUES (?, ?, ?, 'submitted', ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        
        $stmt->bind_param("siis", $requestNo, $typeId, $applicantId, $formDataJson);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Find request by ID, joining with request type and applicant info.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, 
                   rt.code AS type_code, rt.name_th AS type_name, rt.doc_checklist,
                   a.full_name AS applicant_name, a.email AS applicant_email, a.phone AS applicant_phone,
                   o.name AS officer_name, o.role AS officer_role
            FROM requests r
            JOIN request_types rt ON r.type_id = rt.id
            JOIN applicants a ON r.applicant_id = a.id
            LEFT JOIN officers o ON r.assigned_officer_id = o.id
            WHERE r.id = ? LIMIT 1
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Find request by Request Number.
     * @param string $requestNo
     * @return array|null
     * @throws Exception
     */
    public static function findByRequestNo($requestNo) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, 
                   rt.code AS type_code, rt.name_th AS type_name,
                   a.full_name AS applicant_name, a.email AS applicant_email, a.phone AS applicant_phone
            FROM requests r
            JOIN request_types rt ON r.type_id = rt.id
            JOIN applicants a ON r.applicant_id = a.id
            WHERE r.request_no = ? LIMIT 1
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $requestNo);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Get all requests matching criteria for administrative views.
     * @param array $filters
     * @return array
     * @throws Exception
     */
    public static function getAll(array $filters = []) {
        $db = Database::getConnection();
        $sql = "
            SELECT r.*, rt.name_th AS type_name, a.full_name AS applicant_name, o.name AS officer_name
            FROM requests r
            JOIN request_types rt ON r.type_id = rt.id
            JOIN applicants a ON r.applicant_id = a.id
            LEFT JOIN officers o ON r.assigned_officer_id = o.id
            WHERE 1=1
        ";
        
        $params = [];
        $types = "";

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        if (!empty($filters['officer_id'])) {
            $sql .= " AND r.assigned_officer_id = ?";
            $params[] = $filters['officer_id'];
            $types .= "i";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (r.request_no LIKE ? OR a.full_name LIKE ? OR a.email LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
        return $requests;
    }

    /**
     * Update request status.
     * @param int $requestId
     * @param string $status
     * @return bool
     * @throws Exception
     */
    public static function updateStatus($requestId, $status) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("si", $status, $requestId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Assign request to an officer.
     * @param int $requestId
     * @param int|null $officerId
     * @return bool
     * @throws Exception
     */
    public static function assignOfficer($requestId, $officerId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE requests SET assigned_officer_id = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ii", $officerId, $requestId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    // ==========================================
    // STATUS HISTORY
    // ==========================================

    /**
     * Log status transition in status_history table.
     * @param int $requestId
     * @param string $fromStatus
     * @param string $toStatus
     * @param string|null $reason
     * @param int|null $officerId
     * @return int
     * @throws Exception
     */
    public static function logStatusHistory($requestId, $fromStatus, $toStatus, $reason = null, $officerId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO status_history (request_id, from_status, to_status, reason, officer_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("isssi", $requestId, $fromStatus, $toStatus, $reason, $officerId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    /**
     * Get status history timeline for a request.
     * @param int $requestId
     * @return array
     * @throws Exception
     */
    public static function getStatusHistory($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT sh.*, o.name AS officer_name 
            FROM status_history sh
            LEFT JOIN officers o ON sh.officer_id = o.id
            WHERE sh.request_id = ?
            ORDER BY sh.created_at ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }

    // ==========================================
    // MESSAGES / CHAT SYSTEM
    // ==========================================

    /**
     * Post a new message.
     * @param int $requestId
     * @param string $senderType 'applicant' or 'officer'
     * @param string $body
     * @param int $internalNote 0 or 1
     * @return int Inserted ID
     * @throws Exception
     */
    public static function postMessage($requestId, $senderType, $body, $internalNote = 0) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO messages (request_id, sender_type, body, internal_note) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("issi", $requestId, $senderType, $body, $internalNote);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    /**
     * Retrieve chat messages for a request.
     * @param int $requestId
     * @param bool $includeInternal Whether to fetch private internal notes
     * @return array
     * @throws Exception
     */
    public static function getMessages($requestId, $includeInternal = false) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM messages WHERE request_id = ?";
        if (!$includeInternal) {
            $sql .= " AND internal_note = 0";
        }
        $sql .= " ORDER BY created_at ASC";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
        return $messages;
    }

    // ==========================================
    // DASHBOARD STATS AGGREGATION
    // ==========================================

    /**
     * Get aggregate counts of requests grouped by status.
     * @return array
     * @throws Exception
     */
    public static function getStatusCounts() {
        $db = Database::getConnection();
        $sql = "SELECT status, COUNT(*) as count FROM requests GROUP BY status";
        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $counts = [
            'total' => 0,
            'submitted' => 0,
            'received' => 0,
            'in_review' => 0,
            'need_info' => 0,
            'pending_approval' => 0,
            'approved' => 0,
            'completed' => 0,
            'rejected' => 0
        ];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        return $counts;
    }

    /**
     * Get count of requests grouped by request type.
     * @return array
     * @throws Exception
     */
    public static function getTypeCounts() {
        $db = Database::getConnection();
        $sql = "
            SELECT rt.name_th, COUNT(r.id) as count 
            FROM request_types rt
            LEFT JOIN requests r ON r.type_id = rt.id
            GROUP BY rt.id
        ";
        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = [
                'label' => $row['name_th'],
                'value' => (int)$row['count']
            ];
        }
        return $stats;
    }

    /**
     * Get monthly request counts for the current year.
     * @return array
     * @throws Exception
     */
    public static function getMonthlyCounts() {
        $db = Database::getConnection();
        // Returns counts for the last 12 months
        $sql = "
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
            FROM requests 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $months = [];
        while ($row = $result->fetch_assoc()) {
            $months[] = [
                'month' => $row['month'],
                'count' => (int)$row['count']
            ];
        }
        return $months;
    }

    /**
     * Get executive KPIs and officer performance data.
     * @return array
     * @throws Exception
     */
    public static function getExecutiveKPIs() {
        $db = Database::getConnection();
        
        // 1. Avg Processing Time (in hours)
        $sqlAvg = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours FROM requests WHERE status IN ('completed', 'approved')";
        $resAvg = $db->query($sqlAvg);
        $avgHours = 0;
        if ($resAvg && $row = $resAvg->fetch_assoc()) {
            $avgHours = $row['avg_hours'] !== null ? round((float)$row['avg_hours'], 1) : 0;
        }
        
        // 2. SLA Compliance (SLA = within 72 hours)
        $sqlSLA = "SELECT 
                    COUNT(*) as total_done,
                    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, updated_at) <= 72 THEN 1 ELSE 0 END) as met_sla
                   FROM requests 
                   WHERE status IN ('completed', 'approved')";
        $resSLA = $db->query($sqlSLA);
        $slaPercent = 100;
        if ($resSLA && $row = $resSLA->fetch_assoc()) {
            if ($row['total_done'] > 0) {
                $slaPercent = round(((int)$row['met_sla'] / (int)$row['total_done']) * 100, 1);
            }
        }
        
        // 3. Success Rate (completed/approved vs rejected)
        $sqlSuccess = "SELECT 
                        SUM(CASE WHEN status IN ('completed', 'approved') THEN 1 ELSE 0 END) as success,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                       FROM requests 
                       WHERE status IN ('completed', 'approved', 'rejected')";
        $resSuccess = $db->query($sqlSuccess);
        $successRate = 100;
        if ($resSuccess && $row = $resSuccess->fetch_assoc()) {
            $totalEnded = (int)$row['success'] + (int)$row['rejected'];
            if ($totalEnded > 0) {
                $successRate = round(((int)$row['success'] / $totalEnded) * 100, 1);
            }
        }
        
        // 4. Officer Performance (completed requests count)
        $sqlPerformance = "
            SELECT o.name as officer_name, COUNT(r.id) as completed_count
            FROM officers o
            LEFT JOIN requests r ON r.assigned_officer_id = o.id AND r.status IN ('completed', 'approved')
            GROUP BY o.id
            ORDER BY completed_count DESC
        ";
        $resPerf = $db->query($sqlPerformance);
        $officerPerformance = [];
        if ($resPerf) {
            while ($row = $resPerf->fetch_assoc()) {
                $officerPerformance[] = [
                    'name' => $row['officer_name'],
                    'count' => (int)$row['completed_count']
                ];
            }
        }
        
        return [
            'avg_hours' => $avgHours,
            'sla_percent' => $slaPercent,
            'success_rate' => $successRate,
            'officer_performance' => $officerPerformance
        ];
    }

    /**
     * Retrieve all active request types.
     * @return array
     * @throws Exception
     */
    public static function getRequestTypes() {
        $db = Database::getConnection();
        $sql = "SELECT * FROM request_types WHERE active = 1 ORDER BY id ASC";
        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $types = [];
        while ($row = $result->fetch_assoc()) {
            $row['doc_checklist'] = json_decode($row['doc_checklist'], true);
            $types[] = $row;
        }
        return $types;
    }
}
