<?php
echo "=== EduSchedule API Automated Verification Test ===\n";

function simulateRequest($uri, $method = 'GET', $body = null) {
    echo "\nSimulating: $method $uri\n";
    
    // Set up $_SERVER mock
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    
    // Setup PHP input mock (BaseController uses file_get_contents('php://input'))
    // Since php://input cannot be overridden easily in user space, we'll write a temporary file
    // and let index.php read from it or we mock the request input.
    // Wait! Since php://input is read-only, to test POST/PUT, we can adjust BaseController to check if a test body is injected
    // or we can test GET requests which don't require php://input.
    // Let's test GET /api/teachers and GET /api/courses first.
    
    ob_start();
    try {
        // We include index.php (it will call exit, so we catch it if we can, or we let it run and exit)
        // Since index.php calls exit, we expect it to output the content.
        require dirname(__FILE__) . '/public/index.php';
    } catch (\Throwable $t) {
        // If it throws or exits
    }
    $output = ob_get_clean();
    
    echo "Response Code: " . http_response_code() . "\n";
    echo "Body: " . substr($output, 0, 500) . (strlen($output) > 500 ? "..." : "") . "\n";
    
    // Verify JSON format
    $data = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Response is valid JSON.\n";
        if (is_array($data)) {
            echo "✅ Count: " . count($data) . " items returned.\n";
            if (count($data) > 0) {
                echo "✅ First item sample: " . json_encode($data[0]) . "\n";
            }
        }
    } else {
        echo "❌ Response is NOT valid JSON! Raw output: $output\n";
    }
}

// Clear state of previously included files if we run multiple (index.php has exit; so we run it in a subprocess if possible, or run a single GET to avoid exit blocking)
// Actually, running php directly on a script that simulates the request is much easier!
// Let's run a subprocess php CLI command to run this test_api.php, simulating each request separately so the 'exit;' doesn't block the main process.

if (isset($argv[1])) {
    $uri = $argv[1];
    $method = $argv[2] ?? 'GET';
    simulateRequest($uri, $method);
} else {
    // Run subprocesses for each test endpoint
    $endpoints = [
        '/api/teachers',
        '/api/courses',
        '/api/rooms',
        '/api/sessions'
    ];
    
    foreach ($endpoints as $ep) {
        $cmd = "php \"" . __FILE__ . "\" \"$ep\"";
        $output = shell_exec($cmd);
        echo $output;
    }
}
