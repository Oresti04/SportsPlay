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

        $userId = (int)$_SESSION['user_id'];

        // Authoritative role source: DB mapping by user_id.
        // This prevents stale/mixed session flags from bouncing users to the wrong dashboard.
        try {
            $pdo = $GLOBALS['pdo'] ?? null;
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare("SELECT 1 FROM team_coaches WHERE user_id = :uid LIMIT 1");
                $stmt->execute(['uid' => $userId]);
                if ($stmt->fetch()) {
                    $_SESSION['app_role'] = 'coach';
                    $_SESSION['is_coach'] = 1;
                    return 'coach';
                }

                $stmt = $pdo->prepare("SELECT 1 FROM players WHERE user_id = :uid LIMIT 1");
                $stmt->execute(['uid' => $userId]);
                if ($stmt->fetch()) {
                    $_SESSION['app_role'] = 'player';
                    $_SESSION['is_coach'] = 0;
                    return 'player';
                }

                $stmt = $pdo->prepare("SELECT 1 FROM parents WHERE user_id = :uid LIMIT 1");
                $stmt->execute(['uid' => $userId]);
                if ($stmt->fetch()) {
                    $_SESSION['app_role'] = 'parent';
                    $_SESSION['is_coach'] = 0;
                    return 'parent';
                }
            }
        } catch (Throwable $e) {
            // Fall back to session metadata if DB-based detection fails.
        }

        $appRole = strtolower(trim((string)($_SESSION['app_role'] ?? '')));
        if (in_array($appRole, ['coach', 'parent', 'player'], true)) {
            return $appRole;
        }

        if (!empty($_SESSION['is_coach'])) {
            return 'coach';
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
                return 'coach-dash/coach_dashboard.php';
            case 'player':
                return 'player-dash/player_dashboard.php';
            case 'parent':
            default:
                return 'parent-dash/parent_dashboard.php';
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
            // Keep session intact and route user to the dashboard for their active account role.
            header('Location: ' . sportsplay_dashboard_path_for_role($role));
            exit;
        }
    }
}
