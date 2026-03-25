<?php
// Thiết lập header mặc định trả về JSON
header('Content-Type: application/json');

// 1. Xử lý Health Check cho method HEAD (Yêu cầu bắt buộc)
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    // Trả về status 200 OK và dừng chạy code ngay lập tức (không trả body)
    http_response_code(200);
    exit;
}

// 2. Khởi tạo source CURL cơ bản (Dùng method GET để test)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // URL dùng để test cURL (sau này bạn thay bằng API thật)
    $targetUrl = "https://jsonplaceholder.typicode.com/todos/1"; 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Trả kết quả về biến thay vì in ra luôn
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Tối ưu: Set timeout để tránh treo RAM nếu API đích bị lỗi
    
    // Thực thi cURL
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Xử lý lỗi cURL nếu có
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['error' => curl_error($ch)]);
        curl_close($ch);
        exit;
    }
    
    curl_close($ch);

    // Trả về kết quả từ API đích
    http_response_code($httpCode);
    echo $response;
    exit;
}

// Trả về 404 cho các method khác không được hỗ trợ
http_response_code(404);
echo json_encode(['message' => 'Method Not Allowed']);