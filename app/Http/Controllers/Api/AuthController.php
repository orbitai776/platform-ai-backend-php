<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Thêm 2 dòng này để xử lý JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// gọi API để lấy key
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $idToken = $request->input('idToken');

        if (!$idToken) {
            return response()->json(['message' => 'Thiếu idToken!'], 400);
        }

        try {
            
            $publicKeys = $this->getGooglePublicKeys();

            $keys = [];
            foreach ($publicKeys as $kid => $cert) {
                $keys[$kid] = new Key($cert, 'RS256'); 
            }

            $decoded = JWT::decode($idToken, $keys);

            $firebaseProjectId = env('FIREBASE_PROJECT_ID', 'orbitai-dev-46245');
            if ($decoded->aud !== $firebaseProjectId) {
                return response()->json(['message' => 'Token không thuộc về dự án này!'], 401);
            }

            $emailFromGoogle = $decoded->email;
            $user = DB::table('users')->where('email', $emailFromGoogle)->first();

            if (!$user) {
                return response()->json(['message' => 'Tài khoản chưa đồng bộ!'], 404);
            }

            // 👉 LOGIC PHÂN LUỒNG QUYỀN (ROLES)
            // Kiểm tra xem ID của user có nằm trong bảng partners không
            $isPartner = DB::table('partners')->where('owner_user_id', $user->id)->exists();
            
            // Nếu có -> partner, Nếu không -> user mặc định
            $assignedRoles = $isPartner ? ['partner'] : ['user'];

            $secretKey = env('JWT_SECRET', 'oDPu4gHlBSq...');
            $payload = [
                'uid' => $user->firebase_uid,
                'email' => $user->email,
                'roles' => $assignedRoles, // Gán mảng quyền động vào Token
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24)
            ];

            $systemToken = JWT::encode($payload, $secretKey, 'HS256');

            return response()->json([
                'status' => 200,
                'accessToken' => $systemToken,
                'data' => $user,
                'role_detected' => $assignedRoles // In thêm ra ngoài cho Frontend dễ check
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Xác thực Google thất bại: ' . $e->getMessage()
            ], 401);
        }
    }

    private function getGooglePublicKeys()
    {
        $url = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
        $response = Http::get($url);
        return $response->json();
    }
}