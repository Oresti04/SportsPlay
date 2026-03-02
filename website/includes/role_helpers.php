<?php

if (!function_exists('sportsplay_extract_app_role_from_metadata')) {
    function sportsplay_extract_app_role_from_metadata($metadata): ?string
    {
        if (!is_string($metadata) || trim($metadata) === '') {
            return null;
        }

        $decoded = json_decode($metadata, true);
        if (!is_array($decoded)) {
            return null;
        }

        $role = $decoded['app_role'] ?? $decoded['role'] ?? null;
        if (!is_string($role)) {
            return null;
        }

        $role = strtolower(trim($role));
        return in_array($role, ['admin', 'coach', 'parent', 'player'], true) ? $role : null;
    }
}

if (!function_exists('sportsplay_session_role')) {
    function sportsplay_session_role(): ?string
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        if (!empty($_SESSION['is_admin'])) {
            return 'admin';
        }

        if (!empty($_SESSION['is_coach'])) {
            return 'coach';
        }

        $appRole = strtolower(trim((string)($_SESSION['app_role'] ?? '')));
        if (in_array($appRole, ['parent', 'player'], true)) {
            return $appRole;
        }

        // Safe default for regular users until backend role mapping is finalized.
        return 'parent';
    }
}

if (!function_exists('sportsplay_dashboard_path_for_role')) {
    function sportsplay_dashboard_path_for_role(?string $role): string
    {
        switch ($role) {
            case 'admin':
                return 'admin-dash/admin_dashboard.php';
            case 'coach':
                return 'coach_dashboard.php';
            case 'player':
                return 'player_dashboard.php';
            case 'parent':
            default:
                return 'parent_dashboard.php';
        }
    }
}

if (!function_exists('sportsplay_require_role')) {
    function sportsplay_require_role(array $allowedRoles): void
    {
        $role = sportsplay_session_role();
        if ($role === null) {
            header('Location: auth/login.php');
            exit;
        }

        if (!in_array($role, $allowedRoles, true)) {
            header('Location: dashboard.php');
            exit;
        }
    }
}
