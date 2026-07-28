<?php
namespace App\Controllers;

use App\Database;
use PDO;
use Exception;

class TeacherController extends BaseController {
    private PDO $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getConnection();
    }

    public function index(): void {
        try {
            $stmt = $this->db->query("SELECT * FROM teachers");
            $teachers = $stmt->fetchAll();
            
            // Format availability back to array from JSON string
            foreach ($teachers as &$t) {
                $t['availability'] = json_decode($t['availability'] ?? '[]', true);
            }
            
            $this->sendJson($teachers);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function create(): void {
        try {
            $data = $this->getRequestBody();
            
            if (empty($data['name']) || empty($data['email'])) {
                $this->sendError("Missing required fields: name and email are mandatory.");
            }

            $id = $data['id'] ?? 'teacher_' . uniqid();
            $name = $data['name'];
            $email = $data['email'];
            $department = $data['department'] ?? '';
            $color = $data['color'] ?? '#3B82F6';
            $availability = json_encode($data['availability'] ?? []);
            $avatarUrl = $data['avatarUrl'] ?? null;

            $stmt = $this->db->prepare("INSERT INTO teachers (id, name, email, department, color, availability, avatarUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $name, $email, $department, $color, $availability, $avatarUrl]);

            $this->sendJson([
                'success' => true,
                'id' => $id,
                'message' => 'Teacher created successfully'
            ], 211);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function update(string $id): void {
        try {
            $data = $this->getRequestBody();
            
            // Check if teacher exists
            $check = $this->db->prepare("SELECT id FROM teachers WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Teacher not found", 404);
            }

            if (empty($data['name']) || empty($data['email'])) {
                $this->sendError("Missing required fields: name and email are mandatory.");
            }

            $name = $data['name'];
            $email = $data['email'];
            $department = $data['department'] ?? '';
            $color = $data['color'] ?? '#3B82F6';
            $availability = json_encode($data['availability'] ?? []);
            $avatarUrl = $data['avatarUrl'] ?? null;

            $stmt = $this->db->prepare("UPDATE teachers SET name = ?, email = ?, department = ?, color = ?, availability = ?, avatarUrl = ? WHERE id = ?");
            $stmt->execute([$name, $email, $department, $color, $availability, $avatarUrl, $id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Teacher updated successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function delete(string $id): void {
        try {
            // Check if teacher exists
            $check = $this->db->prepare("SELECT id FROM teachers WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Teacher not found", 404);
            }

            $stmt = $this->db->prepare("DELETE FROM teachers WHERE id = ?");
            $stmt->execute([$id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Teacher deleted successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
