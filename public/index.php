<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Dotenv\Dotenv;
// Load environment variables from .env
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

// Compute routing path relative to root/subfolder
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
$basePath = str_replace('\\', '/', $basePath); // Normalize Windows paths
$basePath = rtrim($basePath, '/');

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if ($basePath !== '' && strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}
$requestPath = '/' . ltrim($requestPath, '/');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Handle OPTIONS preflight requests globally
if ($method === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("HTTP/1.1 200 OK");
    exit;
}

// Swagger documentation redirection
if ($requestPath === '/' || $requestPath === '/swagger' || $requestPath === '/swagger/') {
    // Redirect to the swagger-ui index.html
    $redirectUrl = rtrim($basePath, '/') . '/swagger/index.html';
    header("Location: " . $redirectUrl);
    exit;
}

try {
    // Router logic matching endpoints
    
    // 1. TEACHERS
    if ($requestPath === '/api/teachers') {
        $controller = new \App\Controllers\TeacherController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
    } elseif (preg_match('#^/api/teachers/([^/]+)$#', $requestPath, $matches)) {
        $controller = new \App\Controllers\TeacherController();
        $id = $matches[1];
        if ($method === 'PUT') {
            $controller->update($id);
        } elseif ($method === 'DELETE') {
            $controller->delete($id);
        }
    }
    
    // 2. COURSES
    elseif ($requestPath === '/api/courses') {
        $controller = new \App\Controllers\CourseController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
    } elseif (preg_match('#^/api/courses/([^/]+)$#', $requestPath, $matches)) {
        $controller = new \App\Controllers\CourseController();
        $id = $matches[1];
        if ($method === 'PUT') {
            $controller->update($id);
        } elseif ($method === 'DELETE') {
            $controller->delete($id);
        }
    }
    
    // 3. ROOMS
    elseif ($requestPath === '/api/rooms') {
        $controller = new \App\Controllers\RoomController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
    } elseif (preg_match('#^/api/rooms/([^/]+)$#', $requestPath, $matches)) {
        $controller = new \App\Controllers\RoomController();
        $id = $matches[1];
        if ($method === 'PUT') {
            $controller->update($id);
        } elseif ($method === 'DELETE') {
            $controller->delete($id);
        }
    }
    
    // 4. SESSIONS
    elseif ($requestPath === '/api/sessions') {
        $controller = new \App\Controllers\SessionController();
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->create();
        }
    } elseif (preg_match('#^/api/sessions/([^/]+)$#', $requestPath, $matches)) {
        $controller = new \App\Controllers\SessionController();
        $id = $matches[1];
        if ($method === 'PUT') {
            $controller->update($id);
        } elseif ($method === 'DELETE') {
            $controller->delete($id);
        }
    }

    // Route not found
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => "Route not found: $method $requestPath"
    ]);
} catch (Exception $e) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
