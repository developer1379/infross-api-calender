<?php
namespace App\Controllers;

use App\Database;
use PDO;
use Exception;

class CourseController extends BaseController {
    private PDO $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getConnection();
    }

    public function index(): void {
        try {
            $stmt = $this->db->query("SELECT * FROM courses");
            $courses = $stmt->fetchAll();
            $this->sendJson($courses);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function create(): void {
        try {
            $data = $this->getRequestBody();
            
            if (empty($data['code']) || empty($data['name'])) {
                $this->sendError("Missing required fields: code and name are mandatory.");
            }

            $id = $data['id'] ?? 'course_' . uniqid();
            $code = $data['code'];
            $name = $data['name'];
            $department = $data['department'] ?? '';
            $enrolledStudents = (int)($data['enrolledStudents'] ?? 0);

            $stmt = $this->db->prepare("INSERT INTO courses (id, code, name, department, enrolledStudents) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $code, $name, $department, $enrolledStudents]);

            $this->sendJson([
                'success' => true,
                'id' => $id,
                'message' => 'Course created successfully'
            ], 211);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function update(string $id): void {
        try {
            $data = $this->getRequestBody();
            
            // Check if course exists
            $check = $this->db->prepare("SELECT id FROM courses WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Course not found", 404);
            }

            if (empty($data['code']) || empty($data['name'])) {
                $this->sendError("Missing required fields: code and name are mandatory.");
            }

            $code = $data['code'];
            $name = $data['name'];
            $department = $data['department'] ?? '';
            $enrolledStudents = (int)($data['enrolledStudents'] ?? 0);

            $stmt = $this->db->prepare("UPDATE courses SET code = ?, name = ?, department = ?, enrolledStudents = ? WHERE id = ?");
            $stmt->execute([$code, $name, $department, $enrolledStudents, $id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Course updated successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function delete(string $id): void {
        try {
            // Check if course exists
            $check = $this->db->prepare("SELECT id FROM courses WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Course not found", 404);
            }

            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Course deleted successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
