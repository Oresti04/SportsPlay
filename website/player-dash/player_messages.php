<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['player']);

$userId = (int)$_SESSION['user_id'];
$threadsRaw = sp_get_player_coach_threads($pdo, $userId);
$threads = [];
foreach ($threadsRaw as $row) {
    $cid = (int)$row['user_id'];
    $threads[$cid] = $row;
}

$activeCoachId = (int)($_GET['user'] ?? 0);
if ($activeCoachId <= 0 || !isset($threads[$activeCoachId])) {
    $first = array_key_first($threads);
    $activeCoachId = $first ? (int)$first : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientId = (int)($_POST['recipient_id'] ?? 0);
    $message = trim((string)($_POST['message'] ?? ''));
    if ($recipientId > 0 && isset($threads[$recipientId])) {
        $teamId = (int)($threads[$recipientId]['team_id'] ?? 0);
        sp_send_direct_message($pdo, $userId, $recipientId, $teamId, $message);
        header('Location: player_messages.php?user=' . $recipientId);
        exit;
    }
}

$unreadCounts = sp_unread_counts_for_user($pdo, $userId);
$messages = [];
$activeThread = null;
if ($activeCoachId > 0 && isset($threads[$activeCoachId])) {
    sp_mark_thread_as_read($pdo, $userId, $activeCoachId);
    $unreadCounts[$activeCoachId] = 0;
    $activeThread = $threads[$activeCoachId];
    $messages = sp_get_direct_messages($pdo, $userId, $activeCoachId);
}

$roleLabel = 'Player'; $roleSub = 'Player Console'; $sidebarInclude = 'player_sidebar.php';
$pageTitle = 'Coach Messages'; $activeNav = 'messages';
include __DIR__ . '/../includes/role_header.php'; ?>

<div class="sp-chat-layout">
  <div class="sp-chat-sidebar">
    <div class="sp-chat-sidebar__top">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
        <div style="font-weight:800; font-size:15px;">Coaches</div>
        <span class="sp-pill"><?php echo count($threads); ?></span>
      </div>
    </div>
    <div class="sp-chat-threadlist">
      <?php if (empty($threads)): ?>
        <div style="padding:20px; text-align:center;" class="sp-muted">
          You don't have a coach chat yet. Send a team request first.
        </div>
      <?php else: ?>
        <?php foreach ($threads as $cid => $t): $name = trim($t['first_name'] . ' ' . $t['last_name']); ?>
          <a href="player_messages.php?user=<?php echo (int)$cid; ?>" class="sp-chat-thread <?php echo $activeCoachId === (int)$cid ? 'is-active' : ''; ?>">
            <div class="sp-chat-thread__avatar" style="background:#fef3c7; color:#b45309;">
              <?php if (!empty($t['profile_image'])): ?>
                <img class="sp-avatar__img" src="<?php echo htmlspecialchars($t['profile_image']); ?>" alt="Profile picture" />
              <?php else: ?>
                <?php echo htmlspecialchars(strtoupper(substr($name, 0, 1))); ?>
              <?php endif; ?>
            </div>
            <div class="sp-chat-thread__body">
              <div class="sp-chat-thread__row"><strong><?php echo htmlspecialchars($name); ?></strong></div>
              <div class="sp-chat-thread__sub"><?php echo htmlspecialchars(ucfirst($t['role']) . ' coach · ' . $t['team_name']); ?></div>
              <?php if (!empty($unreadCounts[(int)$cid])): ?>
                <div class="sp-chat-thread__last" style="color:#dc2626;font-weight:700;">
                  <?php echo (int)$unreadCounts[(int)$cid]; ?> unread
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
      <div class="sp-chat-thread__avatar large" style="background:#fef3c7; color:#b45309;">
        <?php if ($activeThread && !empty($activeThread['profile_image'])): ?>
          <img class="sp-avatar__img" src="<?php echo htmlspecialchars($activeThread['profile_image']); ?>" alt="Profile picture" />
        <?php else: ?>
          <?php echo $activeThread ? htmlspecialchars(strtoupper(substr(trim($activeThread['first_name'] . ' ' . $activeThread['last_name']), 0, 1))) : '?'; ?>
        <?php endif; ?>
      </div>
      <div>
        <div class="sp-chat-header__title">
          <?php echo $activeThread ? htmlspecialchars(trim($activeThread['first_name'] . ' ' . $activeThread['last_name'])) : 'Select a coach'; ?>
        </div>
        <div class="sp-chat-header__sub"><?php echo $activeThread ? htmlspecialchars($activeThread['team_name']) : ''; ?></div>
      </div>
    </div>

    <div class="sp-chat-stream" id="chatStream">
      <?php if (empty($activeThread)): ?>
        <div style="text-align:center; padding:40px 0;" class="sp-muted">No active coach conversation.</div>
      <?php elseif (empty($messages)): ?>
        <div style="text-align:center; padding:40px 0;" class="sp-muted">No messages yet. Say hi to your coach.</div>
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
      <form class="sp-chat-composer" method="post" action="player_messages.php?user=<?php echo (int)$activeCoachId; ?>">
        <input type="hidden" name="recipient_id" value="<?php echo (int)$activeCoachId; ?>">
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
      const res = await fetch('chat_poll.php?user=<?php echo (int)$activeCoachId; ?>&since=' + lastId, {credentials: 'same-origin'});
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

<?php include __DIR__ . '/../includes/role_footer.php'; ?>
