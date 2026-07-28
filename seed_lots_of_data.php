<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database;

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

try {
    $pdo = Database::getConnection();
    echo "Connected to remote database successfully!\n";

    echo "Clearing existing data...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE sessions;");
    $pdo->exec("TRUNCATE TABLE rooms;");
    $pdo->exec("TRUNCATE TABLE courses;");
    $pdo->exec("TRUNCATE TABLE teachers;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Database cleared.\n";

    // 1. Generate Teachers (16 total)
    echo "Seeding teachers...\n";
    $departments = ['Computer Science', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'Business & Economics', 'Humanities'];
    $colors = ['#1a73e8', '#0f9d58', '#f4b400', '#db4437', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#10b981', '#6366f1'];
    
    $teacherTemplates = [
        ['Dr. Sarah Connor', 's.connor@university.edu', 'Computer Science', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Alan Turing', 'a.turing@university.edu', 'Mathematics', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150'],
        ['Dr. Marie Curie', 'm.curie@university.edu', 'Physics', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Richard Feynman', 'r.feynman@university.edu', 'Physics', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150'],
        ['Dr. Ada Lovelace', 'a.lovelace@university.edu', 'Computer Science', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Grace Hopper', 'g.hopper@university.edu', 'Computer Science', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150'],
        ['Dr. Albert Einstein', 'a.einstein@university.edu', 'Physics', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Katherine Johnson', 'k.johnson@university.edu', 'Mathematics', 'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&q=80&w=150'],
        ['Dr. Rosalind Franklin', 'r.franklin@university.edu', 'Biology', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Charles Darwin', 'c.darwin@university.edu', 'Biology', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=150'],
        ['Dr. Gregor Mendel', 'g.mendel@university.edu', 'Biology', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Adam Smith', 'a.smith@university.edu', 'Business & Economics', 'https://images.unsplash.com/photo-1500048993953-d23a436266cf?auto=format&fit=crop&q=80&w=150'],
        ['Dr. John Maynard Keynes', 'j.keynes@university.edu', 'Business & Economics', 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Socrates', 'socrates@university.edu', 'Humanities', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=150'],
        ['Dr. Hannah Arendt', 'h.arendt@university.edu', 'Humanities', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&q=80&w=150'],
        ['Prof. Jane Austen', 'j.austen@university.edu', 'Humanities', 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&q=80&w=150']
    ];

    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    $teacherStmt = $pdo->prepare("INSERT INTO teachers (id, name, email, department, color, availability, avatarUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($teacherTemplates as $index => $t) {
        $id = "teacher_" . ($index + 1);
        $name = $t[0];
        $email = $t[1];
        $dept = $t[2];
        $color = $colors[$index % count($colors)];
        
        // Random availability: select 3 to 5 random days
        shuffle($daysOfWeek);
        $numDays = rand(3, 5);
        $avail = array_slice($daysOfWeek, 0, $numDays);
        sort($avail); // Sort days monday-friday
        $availJson = json_encode($avail);
        $avatar = $t[3];

        $teacherStmt->execute([$id, $name, $email, $dept, $color, $availJson, $avatar]);
    }
    echo "16 teachers seeded.\n";

    // 2. Generate Courses (20 total)
    echo "Seeding courses...\n";
    $courseTemplates = [
        ['CS-101', 'Introduction to Programming', 'Computer Science', 125],
        ['CS-202', 'Data Structures & Algorithms', 'Computer Science', 45],
        ['CS-303', 'Database Management Systems', 'Computer Science', 35],
        ['CS-404', 'Artificial Intelligence', 'Computer Science', 60],
        ['CS-450', 'Computer Networks', 'Computer Science', 30],
        
        ['MATH-101', 'Calculus I', 'Mathematics', 140],
        ['MATH-201', 'Linear Algebra', 'Mathematics', 40],
        ['MATH-302', 'Probability & Statistics', 'Mathematics', 55],
        ['MATH-401', 'Abstract Algebra', 'Mathematics', 20],
        
        ['PHY-101', 'General Physics I', 'Physics', 110],
        ['PHY-301', 'Quantum Mechanics', 'Physics', 18],
        ['PHY-350', 'Thermodynamics', 'Physics', 15],
        
        ['CHEM-101', 'General Chemistry I', 'Chemistry', 95],
        ['CHEM-202', 'Organic Chemistry', 'Chemistry', 28],
        
        ['BIO-101', 'General Biology I', 'Biology', 150],
        ['BIO-205', 'Genetics', 'Biology', 35],
        
        ['ECON-101', 'Principles of Microeconomics', 'Business & Economics', 160],
        ['ECON-301', 'Macroeconomic Theory', 'Business & Economics', 45],
        
        ['PHIL-101', 'Introduction to Philosophy', 'Humanities', 80],
        ['LIT-201', 'Creative Writing', 'Humanities', 25]
    ];

    $courseStmt = $pdo->prepare("INSERT INTO courses (id, code, name, department, enrolledStudents) VALUES (?, ?, ?, ?, ?)");
    foreach ($courseTemplates as $index => $c) {
        $id = "course_" . ($index + 1);
        $code = $c[0];
        $name = $c[1];
        $dept = $c[2];
        $students = $c[3];
        $courseStmt->execute([$id, $code, $name, $dept, $students]);
    }
    echo "20 courses seeded.\n";

    // 3. Generate Rooms (10 total)
    echo "Seeding classrooms...\n";
    $roomTemplates = [
        ['Lecture Hall 101', 120, 'Lecture Hall'],
        ['Lecture Hall 102', 150, 'Lecture Hall'],
        ['Auditorium Maxima', 200, 'Lecture Hall'],
        ['Computing Lab A', 45, 'Lab'],
        ['Computing Lab B', 35, 'Lab'],
        ['Physics Lab 304', 25, 'Lab'],
        ['Chemistry Lab 208', 30, 'Lab'],
        ['Seminar Room A', 30, 'Seminar Room'],
        ['Seminar Room B', 25, 'Seminar Room'],
        ['Boardroom 402', 15, 'Seminar Room']
    ];

    $roomStmt = $pdo->prepare("INSERT INTO rooms (id, name, capacity, type) VALUES (?, ?, ?, ?)");
    foreach ($roomTemplates as $index => $r) {
        $id = "room_" . ($index + 1);
        $name = $r[0];
        $capacity = $r[1];
        $type = $r[2];
        $roomStmt->execute([$id, $name, $capacity, $type]);
    }
    echo "10 classrooms seeded.\n";

    // 4. Generate Sessions (30 total - 22 scheduled, 8 drafts)
    echo "Seeding schedule sessions...\n";
    
    // Day options
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    // Time slots (startTime, endTime)
    $slots = [
        ['08:00', '09:30'],
        ['09:30', '11:00'],
        ['11:00', '12:30'],
        ['13:00', '14:30'],
        ['14:30', '16:00'],
        ['16:00', '17:30']
    ];

    $sessionStmt = $pdo->prepare("INSERT INTO sessions (id, teacherId, courseId, roomId, day, startTime, endTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Add 22 scheduled sessions
    $scheduledCount = 22;
    for ($i = 1; $i <= $scheduledCount; $i++) {
        $id = "session_" . $i;
        
        // Randomly pick a teacher, course, and room
        $tNum = rand(1, 16);
        $cNum = rand(1, 20);
        $rNum = rand(1, 10);
        
        $teacherId = "teacher_" . $tNum;
        $courseId = "course_" . $cNum;
        $roomId = "room_" . $rNum;
        
        // Random day and slot
        $day = $days[rand(0, count($days) - 1)];
        $slot = $slots[rand(0, count($slots) - 1)];
        $startTime = $slot[0];
        $endTime = $slot[1];

        $sessionStmt->execute([$id, $teacherId, $courseId, $roomId, $day, $startTime, $endTime]);
    }

    // Add 8 drafts (roomId, day, startTime, endTime are null)
    $draftCount = 8;
    for ($i = 1; $i <= $draftCount; $i++) {
        $id = "session_" . ($scheduledCount + $i);
        
        $tNum = rand(1, 16);
        $cNum = rand(1, 20);
        
        $teacherId = "teacher_" . $tNum;
        $courseId = "course_" . $cNum;

        $sessionStmt->execute([$id, $teacherId, $courseId, null, null, null, null]);
    }
    echo "30 sessions seeded successfully (22 scheduled, 8 drafts).\n";
    
    echo "\n🎉 Database seeding completed successfully with lots of test data!\n";
} catch (\Exception $e) {
    echo "Error seeding database: " . $e->getMessage() . "\n";
}
