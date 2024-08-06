<?php

namespace Lib\Middleware;

use Lib\Auth\Auth;
use Lib\Auth\AuthConfig;

class AuthMiddleware
{
    public static function handle($requestUri)
    {
        $requestUri = trim($requestUri);
        if (!self::matches($requestUri)) {
            return;
        }

        // Check if the user is authorized to access the route or redirect to login
        if (!self::isAuthorized()) {
            redirect('/auth/login');
            exit;
        }

        // Check if the user has the required role to access the route or redirect to denied
        if (AuthConfig::IS_ROLE_BASE && !self::hasRequiredRole($requestUri)) {
            redirect('/denied');
            exit;
        }
    }

    protected static function matches($requestUri)
    {
        foreach (AuthConfig::$privateRoutes ?? [] as $pattern) {
            if (self::getUriRegex($pattern, $requestUri)) {
                return true;
            }
        }
        return false;
    }

    protected static function isAuthorized(): bool
    {
        $auth = new Auth();
        $cookieName = Auth::COOKIE_NAME;
        if (!isset($_COOKIE[$cookieName])) {
            unset($_SESSION[Auth::PAYLOAD]);
            return false;
        }

        $jwt = $_COOKIE[$cookieName];

        if (AuthConfig::IS_TOKEN_AUTO_REFRESH) {
            $jwt = $auth->refreshToken($jwt);
            $verifyToken = $auth->verifyToken($jwt);
        }

        $verifyToken = $auth->verifyToken($jwt);
        if ($verifyToken === false) {
            return false;
        }

        // Access the PAYLOAD_NAME property using the -> operator instead of array syntax
        if (isset($verifyToken->{Auth::PAYLOAD_NAME})) {
            return true;
        }

        return false;
    }

    protected static function hasRequiredRole($requestUri): bool
    {
        $auth = new Auth();
        $roleBasedRoutes = AuthConfig::$roleBasedRoutes ?? [];
        foreach ($roleBasedRoutes as $pattern => $data) {
            if (self::getUriRegex($pattern, $requestUri)) {
                $userRole = Auth::ROLE_NAME ? $auth->getPayload()[Auth::ROLE_NAME] : $auth->getPayload();
                if ($userRole !== null && AuthConfig::checkAuthRole($userRole, $data[AuthConfig::ROLE_IDENTIFIER])) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function getUriRegex($pattern, $requestUri)
    {
        $pattern = strtolower($pattern);
        $requestUri = strtolower(trim($requestUri));

        // Handle the case where the requestUri is empty, which means home or "/"
        if (empty($requestUri) || $requestUri === '/') {
            $requestUri = '/';
        }

        $regex = "#^/?" . preg_quote($pattern, '#') . "(/.*)?$#";
        return preg_match($regex, $requestUri);
    }
}
