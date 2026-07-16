<?php

namespace App\Http\Middleware;

use App\Models\UserLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    private const EXCLUDED_ROUTES = [
        'login', 'logout', 'register',
        'user.logs.store', 'notificaciones.poll',
        'citas.estados.poll', 'dashboard.citas.check',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        if ($routeName && in_array($routeName, self::EXCLUDED_ROUTES, true)) {
            return $next($request);
        }

        if ($request->isMethod('get')) {
            $ua = $request->userAgent();
            UserLog::create([
                'user_id'    => Auth::id(),
                'ip'         => $request->ip(),
                'user_agent' => $ua,
                'so'         => self::detectOS($ua),
                'navegador'  => self::detectBrowser($ua),
                'url'        => $request->fullUrl(),
                'route_name' => $routeName,
                'method'     => $request->method(),
                'accion'     => 'visita',
            ]);
        }

        return $next($request);
    }

    public static function detectOS(?string $ua): string
    {
        if (!$ua) return 'Desconocido';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac OS X') || str_contains($ua, 'macOS')) return 'macOS';
        if (str_contains($ua, 'Linux') && !str_contains($ua, 'Android')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iOS') || str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        if (str_contains($ua, 'Chrome OS')) return 'Chrome OS';
        return 'Otro';
    }

    public static function detectBrowser(?string $ua): string
    {
        if (!$ua) return 'Desconocido';
        if (str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/')) return 'Edge';
        if (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) return 'Opera';
        if (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Edg/')) return 'Chrome';
        if (str_contains($ua, 'Firefox/')) return 'Firefox';
        if (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) return 'Safari';
        if (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident/')) return 'Internet Explorer';
        return 'Otro';
    }
}
