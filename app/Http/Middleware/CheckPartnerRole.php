<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CheckPartnerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
       // 1. Lấy Token từ Header (Bearer Token)
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thực hiện hành động này!'], 401);
        }

        try {
            $secretKey = env('JWT_SECRET', 'oDPu4gHlBSqAUe9Tnfm8W61clkqmGFDJIcNJcTNvTE2');
            \Firebase\JWT\JWT::$leeway = 60 * 60 * 24 * 365 * 10;
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

   
    if (isset($decoded->roles) && in_array('partner', $decoded->roles)) {
        
        // 2. Tìm User trong DB
        $user = DB::table('users')->where('firebase_uid', $decoded->uid)->first();

        if ($user) {
            // 3. Check xem có trong bảng partners không (owner_user_id)
            $isPartner = DB::table('partners')->where('owner_user_id', $user->id)->exists();
            
            if ($isPartner) {
                // $request->merge(['auth_user' => $user]);
                $request->attributes->add(['auth_user' => $user]);
                return $next($request);
            }
        }
    }

    // Nếu không thỏa mãn các điều kiện trên thì trả về 403
    return response()->json([
        'status' => 403,
        'message' => 'Tài khoản của bạn không có quyền Đối tác (Partner)!'
    ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Token không hợp lệ hoặc đã hết hạn!'
            ], 401);

    //         return response()->json([
    //     'status' => 401,
    //     'message' => 'Lỗi cụ thể: ' . $e->getMessage(), // Nó sẽ báo "Signature verification failed" hoặc "Expired token"
    //     'debug_token' => $token // Để mình check xem token có bị lấy thiếu không
    // ], 401);
        }
        
    }
}
