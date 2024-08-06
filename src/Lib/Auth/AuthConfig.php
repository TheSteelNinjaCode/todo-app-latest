<?php

namespace Lib\Auth;

enum AuthRole: string
{
    case Admin = 'Admin';
    case User = 'User';

    public function equals($role)
    {
        return $this->value === $role;
    }
}

class AuthConfig
{
    public const ROLE_IDENTIFIER = 'role';
    public const IS_ROLE_BASE = false;
    public const IS_TOKEN_AUTO_REFRESH = false;

    // An array listing the public routes that do not require authentication.
    // Example: public static $publicRoutes = ['/'];
    public static $publicRoutes = ['/'];

    // An array of private routes that are accessible to all authenticated users
    // without specific role-based access control. Routes should be listed as string paths.
    // Example: public static $privateRoutes = ['profile', 'dashboard/settings'];
    public static $privateRoutes = [];

    // An associative array mapping specific routes to required user roles for access control.
    // Each route is a key with an array of roles that are allowed access.
    // Format:
    // 'route_path' => [self::ROLE_IDENTIFIER => [AuthRole::Role1, AuthRole::Role2, ...]],
    // Example:
    // public static $roleBasedRoutes = [
    //     'dashboard' => [self::ROLE_IDENTIFIER => [AuthRole::Admin, AuthRole::User]],
    //     'dashboard/users' => [self::ROLE_IDENTIFIER => [AuthRole::Admin]],
    //     'sales' => [self::ROLE_IDENTIFIER => [AuthRole::Admin, AuthRole::User]]
    // ];
    public static $roleBasedRoutes = [];

    /**
     * Checks if the given user role is authorized to access a set of roles.
     * 
     * @param string $userRole The role of the user attempting to access the route.
     * @param array $roles An array of AuthRole instances specifying allowed roles.
     * @return bool Returns true if the user role is in the allowed roles, false otherwise.
     */
    public static function checkAuthRole($userRole, $roles)
    {
        foreach ($roles as $role) {
            if ($userRole === $role->value) {
                return true;
            }
        }
        return false;
    }
}
