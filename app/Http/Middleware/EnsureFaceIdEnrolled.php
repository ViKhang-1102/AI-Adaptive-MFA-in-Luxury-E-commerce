<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureFaceIdEnrolled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If user is authenticated but hasn't enrolled FaceID, redirect to enrollment page.
        // Skip for the enrollment routes themselves to avoid infinite loop.
        if ($user && !$user->identity_image) {
            $allowedRoutes = [
                'face.enrollment.show',
                'face.enrollment.submit',
                'logout',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => 'FaceID enrollment required.'], 403);
                }
                return redirect()->route('face.enrollment.show')
                    ->with('error', 'Please enroll your FaceID to continue using your account.');
            }
        }

        return $next($request);
    }
}
