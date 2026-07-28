<?php
namespace App;

use PDO;
use Exception;

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $db   = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $user, $pass, $options);
                self::ensureSchemaInitialized();
            } catch (Exception $e) {
                throw new Exception("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    private static function ensureSchemaInitialized(): void {
        $pdo = self::$connection;
        try {
            // Check if tables already exist by querying information_schema or running a fast query
            $stmt = $pdo->query("SHOW TABLES LIKE 'teachers'");
            if ($stmt->rowCount() === 0) {
                // Read and execute schema sql
                $sqlPath = dirname(__DIR__) . '/db.sql';
                if (file_exists($sqlPath)) {
                    $sql = file_get_contents($sqlPath);
                    $pdo->exec($sql);
                    
                    // Seed mock data
                    self::seedMockData($pdo);
                }
            }
        } catch (Exception $e) {
            // Log or handle initialisation error
        }
    }

    private static function seedMockData(PDO $pdo): void {
        // 1. Seed Teachers
        $teachers = [
            ['t1', 'Dr. Sarah Connor', 's.connor@university.edu', 'Computer Science', '#1a73e8', json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']), 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=150'],
            ['t2', 'Prof. Alan Turing', 'a.turing@university.edu', 'Mathematics', '#0f9d58', json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday']), 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150'],
            ['t3', 'Dr. Marie Curie', 'm.curie@university.edu', 'Physics', '#f4b400', json_encode(['Monday', 'Wednesday', 'Friday']), 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&q=80&w=150'],
            ['t4', 'Prof. Richard Feynman', 'r.feynman@university.edu', 'Physics', '#db4437', json_encode(['Tuesday', 'Thursday', 'Friday']), 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150']
        ];
        $stmt = $pdo->prepare("INSERT INTO teachers (id, name, email, department, color, availability, avatarUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($teachers as $t) {
            $stmt->execute($t);
        }

        // 2. Seed Courses
        $courses = [
            ['c1', 'CS-101', 'Introduction to Programming', 'Computer Science', 130],
            ['c2', 'MATH-201', 'Linear Algebra', 'Mathematics', 25],
            ['c3', 'PHY-301', 'Quantum Mechanics', 'Physics', 15],
            ['c4', 'CS-202', 'Data Structures & Algorithms', 'Computer Science', 40]
        ];
        $stmt = $pdo->prepare("INSERT INTO courses (id, code, name, department, enrolledStudents) VALUES (?, ?, ?, ?, ?)");
        foreach ($courses as $c) {
            $stmt->execute($c);
        }

        // 3. Seed Rooms
        $rooms = [
            ['r1', 'Lecture Hall 101', 120, 'Lecture Hall'],
            ['r2', 'Seminar Room 202', 30, 'Seminar Room'],
            ['r3', 'Computing Lab A', 45, 'Lab']
        ];
        $stmt = $pdo->prepare("INSERT INTO rooms (id, name, capacity, type) VALUES (?, ?, ?, ?)");
        foreach ($rooms as $r) {
            $stmt->execute($r);
        }

        // 4. Seed Sessions
        $sessions = [
            ['s_1', 't1', 'c1', 'r1', 'Monday', '09:00', '10:30'],
            ['s_2', 't2', 'c2', 'r2', 'Tuesday', '11:00', '12:30'],
            ['s_3', 't4', 'c4', 'r3', 'Tuesday', '14:00', '15:30'],
            ['s_4', 't3', 'c3', 'r1', 'Monday', '09:30', '11:00'],
            ['s_5', 't2', 'c1', 'r3', 'Tuesday', '11:30', '13:00'],
            ['d_1', 't1', 'c4', null, null, null, null],
            ['d_2', 't3', 'c3', null, null, null, null]
        ];
        $stmt = $pdo->prepare("INSERT INTO sessions (id, teacherId, courseId, roomId, day, startTime, endTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($sessions as $s) {
            $stmt->execute($s);
        }
    }
}
