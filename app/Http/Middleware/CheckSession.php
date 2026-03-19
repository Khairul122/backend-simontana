<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    
    public function handle(Request $request, Closure $next): Response
    {
        
        if (!Session::has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak ditemukan atau telah kadaluarsa',
                'code' => 'SESSION_EXPIRED'
            ], 401);
        }

        
        $userId = Session::get('user_id');
        $user = \App\Models\Pengguna::find($userId);

        if (!$user) {
            Session::flush();
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan',
                'code' => 'USER_NOT_FOUND'
            ], 401);
        }

        
        $request->merge([
            'user_id' => $userId,
            'user_role' => Session::get('user_role'),
            'user_name' => Session::get('user_name'),
        ]);

        return $next($request);
    }
}
