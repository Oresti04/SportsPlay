<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';

header('Content-Type: application/json; charset=utf-8');

$role = sportsplay_session_role();
if (!in_array($role, ['coach', 'player'], true) || empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];
$otherUserId = (int)($_GET['user'] ?? 0);
$sinceId = (int)($_GET['since'] ?? 0);

if ($otherUserId <= 0 || !sp_can_user_chat_with($pdo, $currentUserId, $otherUserId, $role)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Forbidden']);
    exit;
}

sp_mark_thread_as_read($pdo, $currentUserId, $otherUserId);
$messages = sp_get_direct_messages_since($pdo, $currentUserId, $otherUserId, $sinceId);
$formatted = [];
foreach ($messages as $m) {
    $formatted[] = [
        'message_id' => (int)$m['message_id'],
        'sender_user_id' => (int)$m['sender_user_id'],
        'recipient_user_id' => (int)$m['recipient_user_id'],
        'body' => (string)$m['body'],
        'time_label' => date('M j, g:i A', strtotime((string)$m['created_at'])),
    ];
}

echo json_encode(['ok' => true, 'messages' => $formatted], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
