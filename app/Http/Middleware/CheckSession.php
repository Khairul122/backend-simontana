<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user has active session
        if (!Session::has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak ditemukan atau telah kadaluarsa',
                'code' => 'SESSION_EXPIRED'
            ], 401);
        }

        // Optional: Check if session is still valid (user exists in database)
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

        // Add user info to request for easier access in controllers
        $request->merge([
            'user_id' => $userId,
            'user_role' => Session::get('user_role'),
            'user_name' => Session::get('user_name'),
        ]);

        return $next($request);
    }
}
