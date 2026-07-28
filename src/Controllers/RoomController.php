<?php
namespace App\Controllers;

use App\Database;
use PDO;
use Exception;

class RoomController extends BaseController {
    private PDO $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getConnection();
    }

    public function index(): void {
        try {
            $stmt = $this->db->query("SELECT * FROM rooms");
            $rooms = $stmt->fetchAll();
            $this->sendJson($rooms);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function create(): void {
        try {
            $data = $this->getRequestBody();
            
            if (empty($data['name']) || empty($data['capacity']) || empty($data['type'])) {
                $this->sendError("Missing required fields: name, capacity, and type are mandatory.");
            }

            $id = $data['id'] ?? 'room_' . uniqid();
            $name = $data['name'];
            $capacity = (int)$data['capacity'];
            $type = $data['type'];

            $stmt = $this->db->prepare("INSERT INTO rooms (id, name, capacity, type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $name, $capacity, $type]);

            $this->sendJson([
                'success' => true,
                'id' => $id,
                'message' => 'Room created successfully'
            ], 211);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function update(string $id): void {
        try {
            $data = $this->getRequestBody();
            
            // Check if room exists
            $check = $this->db->prepare("SELECT id FROM rooms WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Room not found", 404);
            }

            if (empty($data['name']) || empty($data['capacity']) || empty($data['type'])) {
                $this->sendError("Missing required fields: name, capacity, and type are mandatory.");
            }

            $name = $data['name'];
            $capacity = (int)$data['capacity'];
            $type = $data['type'];

            $stmt = $this->db->prepare("UPDATE rooms SET name = ?, capacity = ?, type = ? WHERE id = ?");
            $stmt->execute([$name, $capacity, $type, $id]);

            $this->sendJson([
                'success' => true,
                'message' => 'Room updated successfully'
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    public function delete(string $id): void {
        try {
            // Check if room exists
            $check = $this->db->prepare("SELECT id FROM rooms WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                $this->sendError("Room not found", 404);
            }

            $this->db->beginTransaction();

            // When a room is deleted, sessions scheduled in that room should have roomId, day, startTime, endTime set to null.
            // (Database cascade set null handles roomId = null, but we also want day, startTime, endTime set to null to make them drafts)
            $updateSessions = $this->db->prepare("UPDATE sessions SET roomId = NULL, day = NULL, startTime = NULL, endTime = NULL WHERE roomId = ?");
            $updateSessions->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            $this->sendJson([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->sendError($e->getMessage(), 500);
        }
    }
}
