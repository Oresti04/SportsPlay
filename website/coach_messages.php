<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['coach']);

$userId = (int)$_SESSION['user_id'];
$threadsRaw = sp_get_coach_player_threads($pdo, $userId);
$threads = [];
foreach ($threadsRaw as $row) {
    $tid = (int)$row['user_id'];
    $threads[$tid] = $row;
}

$activeUserId = (int)($_GET['user'] ?? 0);
if ($activeUserId <= 0 || !isset($threads[$activeUserId])) {
    $first = array_key_first($threads);
    $activeUserId = $first ? (int)$first : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientId = (int)($_POST['recipient_id'] ?? 0);
    $message = trim((string)($_POST['message'] ?? ''));
    if ($recipientId > 0 && isset($threads[$recipientId])) {
        sp_send_direct_message($pdo, $userId, $recipientId, (int)$threads[$recipientId]['team_id'], $message);
        header('Location: coach_messages.php?user=' . $recipientId);
        exit;
    }
}

$unreadCounts = sp_unread_counts_for_user($pdo, $userId);
$messages = [];
$activeThread = null;
if ($activeUserId > 0 && isset($threads[$activeUserId])) {
    sp_mark_thread_as_read($pdo, $userId, $activeUserId);
    $unreadCounts[$activeUserId] = 0;
    $activeThread = $threads[$activeUserId];
    $messages = sp_get_direct_messages($pdo, $userId, $activeUserId);
}

$roleLabel = 'Coach'; $roleSub = 'Coach Console'; $sidebarInclude = 'coach_sidebar.php';
$pageTitle = 'Private Messages'; $activeNav = 'messages';
include __DIR__ . '/includes/role_header.php'; ?>

<div class="sp-chat-layout">
  <div class="sp-chat-sidebar">
    <div class="sp-chat-sidebar__top">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
        <div style="font-weight:800; font-size:15px;">Players</div>
        <span class="sp-pill"><?php echo count($threads); ?></span>
      </div>
    </div>
    <div class="sp-chat-threadlist">
      <?php if (empty($threads)): ?>
        <div style="padding:20px; text-align:center;" class="sp-muted">No players available.</div>
      <?php else: ?>
        <?php foreach ($threads as $pid => $t): $name = trim($t['first_name'] . ' ' . $t['last_name']); ?>
          <a href="coach_messages.php?user=<?php echo (int)$pid; ?>" class="sp-chat-thread <?php echo $activeUserId === (int)$pid ? 'is-active' : ''; ?>">
            <div class="sp-chat-thread__avatar" style="background:#eef2ff; color:#4338ca;">
              <?php if (!empty($t['profile_image'])): ?>
                <img class="sp-avatar__img" src="<?php echo htmlspecialchars($t['profile_image']); ?>" alt="Profile picture" />
              <?php else: ?>
                <?php echo htmlspecialchars(strtoupper(substr($name, 0, 1))); ?>
              <?php endif; ?>
            </div>
            <div class="sp-chat-thread__body">
              <div class="sp-chat-thread__row"><strong><?php echo htmlspecialchars($name); ?></strong></div>
              <div class="sp-chat-thread__sub">
                #<?php echo (int)$t['jersey_number']; ?> · <?php echo htmlspecialchars($t['position'] ?: 'Player'); ?> · <?php echo htmlspecialchars($t['team_name']); ?>
              </div>
              <?php if (!empty($unreadCounts[(int)$pid])): ?>
                <div class="sp-chat-thread__last" style="color:#dc2626;font-weight:700;">
                  <?php echo (int)$unreadCounts[(int)$pid]; ?> unread
                </div>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="sp-chat-main">
    <div class="sp-chat-header">
      <div class="sp-chat-thread__avatar large" style="background:#dbeafe; color:#1d4ed8;">
        <?php if ($activeThread && !empty($activeThread['profile_image'])): ?>
          <img class="sp-avatar__img" src="<?php echo htmlspecialchars($activeThread['profile_image']); ?>" alt="Profile picture" />
        <?php else: ?>
          <?php echo $activeThread ? htmlspecialchars(strtoupper(substr(trim($activeThread['first_name'] . ' ' . $activeThread['last_name']), 0, 1))) : '?'; ?>
        <?php endif; ?>
      </div>
      <div>
        <div class="sp-chat-header__title">
          <?php echo $activeThread ? htmlspecialchars(trim($activeThread['first_name'] . ' ' . $activeThread['last_name'])) : 'Select a player'; ?>
        </div>
        <div class="sp-chat-header__sub"><?php echo $activeThread ? htmlspecialchars($activeThread['team_name']) : ''; ?></div>
      </div>
    </div>

    <div class="sp-chat-stream" id="chatStream">
      <?php if (empty($activeThread)): ?>
        <div style="text-align:center; padding:40px 0;" class="sp-muted">Choose a player from the left to start messaging.</div>
      <?php elseif (empty($messages)): ?>
        <div style="text-align:center; padding:40px 0;" class="sp-muted">No messages yet. Send the first message.</div>
      <?php else: ?>
        <?php foreach ($messages as $m): $mine = (int)$m['sender_user_id'] === $userId; ?>
          <div class="sp-chat-bubble-row <?php echo $mine ? 'is-me' : 'is-other'; ?>" data-mid="<?php echo (int)$m['message_id']; ?>">
            <div class="sp-chat-bubble">
              <div class="sp-chat-bubble__text"><?php echo nl2br(htmlspecialchars($m['body'])); ?></div>
              <div class="sp-chat-bubble__time"><?php echo htmlspecialchars(date('M j, g:i A', strtotime($m['created_at']))); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($activeThread): ?>
      <form class="sp-chat-composer" method="post" action="coach_messages.php?user=<?php echo (int)$activeUserId; ?>">
        <input type="hidden" name="recipient_id" value="<?php echo (int)$activeUserId; ?>">
        <input class="sp-input" type="text" name="message" placeholder="Type a message..." required />
        <button class="sp-btn sp-btn--primary" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($activeThread): ?>
<script>
(function(){
  const stream = document.getElementById('chatStream');
  if (!stream) return;
  let lastId = 0;
  stream.querySelectorAll('[data-mid]').forEach(function(el){
    const id = parseInt(el.getAttribute('data-mid') || '0', 10);
    if (id > lastId) lastId = id;
  });

  function esc(text){
    return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function appendMessage(msg){
    const mine = Number(msg.sender_user_id) === <?php echo (int)$userId; ?>;
    const row = document.createElement('div');
    row.className = 'sp-chat-bubble-row ' + (mine ? 'is-me' : 'is-other');
    row.setAttribute('data-mid', String(msg.message_id));
    row.innerHTML = '<div class="sp-chat-bubble"><div class="sp-chat-bubble__text">' + esc(msg.body) + '</div><div class="sp-chat-bubble__time">' + esc(msg.time_label) + '</div></div>';
    stream.appendChild(row);
    stream.scrollTop = stream.scrollHeight;
  }

  async function poll(){
    try {
      const res = await fetch('chat_poll.php?user=<?php echo (int)$activeUserId; ?>&since=' + lastId, {credentials: 'same-origin'});
      if (!res.ok) return;
      const data = await res.json();
      if (!data || !Array.isArray(data.messages)) return;
      data.messages.forEach(function(m){
        appendMessage(m);
        if (Number(m.message_id) > lastId) lastId = Number(m.message_id);
      });
    } catch (e) {}
  }
  setInterval(poll, 5000);
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/role_footer.php'; ?>
