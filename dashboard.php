<?php
require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit;
}

if (!empty($_SESSION['is_admin'])) {
  header('Location: admin_dashboard.php');
  exit;
}

if (!empty($_SESSION['is_coach'])) {
  header('Location: coach_dashboard.php');
  exit;
}

// Regular user (parent)
header('Location: index.php');
exit;
