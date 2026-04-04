<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Thêm 2 dòng này để xử lý JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $token = $request->bearerToken();

    if (!$token) {
        return response()->json(['message' => 'Thiếu Token nội bộ!'], 400);
    }

    try {
        // Lấy Secret từ .env, nếu không có mới dùng giá trị mặc định
        $secretKey = env('JWT_SECRET', 'oDPu4gHlBSqAUe9Tnfm8W61clkqmGFDJIcNJcTNvTE2');
        
        // Cho phép độ trễ thời gian (leeway) để tránh lỗi Expired khi test
        \Firebase\JWT\JWT::$leeway = 60 * 60 * 24 * 365 * 10;; // Chỉ nên để 60s thay vì 10 năm khi nộp bài nhé

        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        $firebaseUid = $decoded->uid; 

        // Bước 1: Ánh xạ Firebase UID sang ID nội bộ của hệ thống
        $user = DB::table('users')->where('firebase_uid', $firebaseUid)->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Tài khoản chưa được đồng bộ trên hệ thống Backend!',
            ], 404);
        }

        // Bước 2: Tìm thông tin Partner (Công ty) dựa trên ID người dùng
        $partner = DB::table('partners')->where('owner_user_id', $user->id)->first();

        if (!$partner) {
            return response()->json([
                'status' => 404,
                'message' => 'Bạn chưa khởi tạo thông tin tổ chức/đối tác!',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Đăng nhập thành công',
            'data' => $partner
        ], 200);

    } catch (\Firebase\JWT\ExpiredException $e) {
        return response()->json(['status' => 401, 'message' => 'Token đã hết hạn!'], 401);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 401,
            'message' => 'Xác thực không thành công: ' . $e->getMessage(),
        ], 401);
    }
}

   
}