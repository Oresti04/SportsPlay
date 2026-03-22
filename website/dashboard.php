<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';

if (empty($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit;
}

$role = sportsplay_session_role();
header('Location: ' . sportsplay_dashboard_path_for_role($role));
exit;
