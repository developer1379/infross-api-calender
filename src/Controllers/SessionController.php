<?php
namespace App\Controllers;

use App\Database;
use PDO;
use Exception;

class SessionController extends BaseController {
    private PDO $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getConnection();
    }

    public function index(): void {
        try {
            $stmt = $this->db->query("SELECT * FROM sessions");
            $sessions = $stmt->fetchAll();
            
            // Format types for correct type-casting in json (like null instead of empty string)
            foreach ($sessions as &$s) {
                $s['roomId'] = $s['roomId'] ?? null;
                $s['day'] = $s['day'] ?? null;
                $s['startTime'] = $s['startTime'] ?? null;
                $s['endTime'] = $s['endTime'] ?? null;
            }

            $this->sendJson($sessions);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function create(): void {
        try {
            $data = $this->getRequestBody();
            
            if (empty($data['teacherId']) || empty($data['courseId'])) {
                $this->sendError("Missing required fields: teacherId and courseId are mandatory.");
            }

            $id = $data['id'] ?? 'session_' . uniqid();
            $teacherId = $data['teacherId'];
            $courseId = $data['courseId'];
            $roomId = !empty($data['roomId']) ? $data['roomId'] : null;
            $day = !empty($data['day']) ? $data['day'] : null;
            $startTime = !empty($data['startTime']) ? $data['startTime'] : null;
            $endTime = !empty($data['endTime']) ? $data['endTime'] : null;

            $stmt = $this->db->prepare("INSERT INTO sessions (id, teacherId, courseId, roomId, day, startTime, endTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $teacherId, $courseId, $roomId, $day, $startTime, $endTime]);

            $this->sendJson([
                'success' => true,
                'id' => $id,
                'message' => 'Session created successfully'
            ], 211);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function update(string $id): void {
        try {
            $data = $this->getRequestBody();
            
            // Check if session exists
            $check = $this->db->prepare("SELECT id FROM sessions WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Session not found", 404);
            }

            if (empty($data['teacherId']) || empty($data['courseId'])) {
                $this->sendError("Missing required fields: teacherId and courseId are mandatory.");
            }

            $teacherId = $data['teacherId'];
            $courseId = $data['courseId'];
            $roomId = !empty($data['roomId']) ? $data['roomId'] : null;
            $day = !empty($data['day']) ? $data['day'] : null;
            $startTime = !empty($data['startTime']) ? $data['startTime'] : null;
            $endTime = !empty($data['endTime']) ? $data['endTime'] : null;

            $stmt = $this->db->prepare("UPDATE sessions SET teacherId = ?, courseId = ?, roomId = ?, day = ?, startTime = ?, endTime = ? WHERE id = ?");
            $stmt->execute([$teacherId, $courseId, $roomId, $day, $startTime, $endTime, $id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Session updated successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function delete(string $id): void {
        try {
            // Check if session exists
            $check = $this->db->prepare("SELECT id FROM sessions WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Session not found", 404);
            }

            $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
            $stmt->execute([$id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Session deleted successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
