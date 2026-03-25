<?php
header('Content-Type: application/json');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 1. Health Check
if ($method === 'HEAD') {
    http_response_code(200);
    exit;
}

if ($method === 'GET') {
    
    // 2. Endpoint mới
    if ($requestUri === '/env-check') {
        $envData = [
            'APP_NAME' => getenv('APP_NAME') ?: 'Chưa được cấu hình (Not Set)',
            'APP_ENV' => getenv('APP_ENV') ?: 'Chưa được cấu hình (Not Set)',
            'TARGET_API_URL' => getenv('TARGET_API_URL') ?: 'Chưa được cấu hình (Not Set)'
        ];
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Environment variables loaded successfully',
            'data' => $envData
        ]);
        exit;
    }

    // 3. Endpoint mặc định
    
    $targetUrl = getenv('TARGET_API_URL') ?: "https://jsonplaceholder.typicode.com/todos/1"; 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['error' => curl_error($ch)]);
        curl_close($ch);
        exit;
    }
    
    curl_close($ch);
    http_response_code($httpCode);
    echo $response;
    exit;
}

http_response_code(404);
echo json_encode(['message' => 'Not Found']);