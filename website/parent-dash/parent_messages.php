<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['parent']);

$userId = (int)$_SESSION['user_id'];
$userFirst = trim((string)($_SESSION['user_name'] ?? 'Parent'));
$parent = sp_parent_data($pdo, $userId);
$selected = $parent['selected'];

// Thread list: coach + any linked children
$threads = [];
if ($selected['coach'] !== '—') {
    $threads[] = [
        'id'      => 'coach-0',
        'name'    => $selected['coach'],
        'sub'     => 'Head Coach · ' . $selected['team'],
        'initial' => strtoupper(substr($selected['coach'], 0, 1)),
        'color'   => '#fef3c7',
        'tcolor'  => '#b45309',
    ];
}
foreach ($parent['children'] as $i => $c) {
    $threads[] = [
        'id'      => 'child-' . $i,
        'name'    => $c['name'],
        'sub'     => $c['team'] . ' · #' . $c['jersey'],
        'initial' => strtoupper(substr($c['name'], 0, 1)),
        'color'   => '#eef2ff',
        'tcolor'  => '#4338ca',
    ];
}

$activeThread = $threads[0] ?? null;

$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Coach Messages'; $activeNav='messages';
include __DIR__ . '/../includes/role_header.php'; ?>

<div class="sp-chat-layout">
  <div class="sp-chat-sidebar">
    <div class="sp-chat-sidebar__top">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
        <div style="font-weight:800; font-size:15px;">Messages</div>
        <span class="sp-pill"><?php echo count($threads); ?></span>
      </div>
      <input class="sp-input" type="text" placeholder="Search..." style="width:100%; margin-top:10px;" id="chatSearch" />
    </div>
    <div class="sp-chat-threadlist" id="threadList">
      <?php if (empty($threads)): ?>
        <div style="padding:20px; text-align:center;" class="sp-muted">No contacts linked yet</div>
      <?php else: ?>
        <?php foreach ($threads as $i => $t): ?>
          <a href="#" class="sp-chat-thread <?php echo $i === 0 ? 'is-active' : ''; ?>" data-thread="<?php echo htmlspecialchars($t['id']); ?>" data-name="<?php echo htmlspecialchars($t['name']); ?>" data-sub="<?php echo htmlspecialchars($t['sub']); ?>">
            <div class="sp-chat-thread__avatar" style="background:<?php echo $t['color']; ?>; color:<?php echo $t['tcolor']; ?>;">
              <?php echo $t['initial']; ?>
            </div>
            <div class="sp-chat-thread__body">
              <div class="sp-chat-thread__row">
                <strong><?php echo htmlspecialchars($t['name']); ?></strong>
              </div>
              <div class="sp-chat-thread__sub"><?php echo htmlspecialchars($t['sub']); ?></div>
              <div class="sp-chat-thread__last">Click to open chat</div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="sp-chat-main">
    <div class="sp-chat-header">
      <div class="sp-chat-thread__avatar large" style="background:<?php echo $activeThread ? $activeThread['color'] : '#e2e8f0'; ?>; color:<?php echo $activeThread ? $activeThread['tcolor'] : '#64748b'; ?>;" id="chatAvatar">
        <?php echo $activeThread ? $activeThread['initial'] : '?'; ?>
      </div>
      <div>
        <div class="sp-chat-header__title" id="chatName"><?php echo $activeThread ? htmlspecialchars($activeThread['name']) : 'Select a conversation'; ?></div>
        <div class="sp-chat-header__sub" id="chatSub"><?php echo $activeThread ? htmlspecialchars($activeThread['sub']) : ''; ?></div>
      </div>
    </div>

    <div class="sp-chat-stream" id="chatStream">
      <div style="text-align:center; padding:30px 0;">
        <i class="fa-solid fa-shield-halved" style="font-size:26px; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
        <p style="font-size:13px; color:#94a3b8; margin:0;">Private chat with your child's coach.<br>History will load once messaging is fully connected.</p>
      </div>
      <div class="sp-chat-bubble-row is-other">
        <div class="sp-chat-bubble">
          <div class="sp-chat-bubble__text">Hi! Just a reminder — practice tomorrow at 6 PM, RIT Turf Field. Bring both kits for Saturday's match.</div>
          <div class="sp-chat-bubble__time">Yesterday · 7:02 PM</div>
        </div>
      </div>
      <div class="sp-chat-bubble-row is-me">
        <div class="sp-chat-bubble">
          <div class="sp-chat-bubble__text">Thanks coach! Quick question — is it okay if he arrives 10 minutes late? He has a school event.</div>
          <div class="sp-chat-bubble__time">Yesterday · 7:04 PM</div>
        </div>
      </div>
      <div class="sp-chat-bubble-row is-other">
        <div class="sp-chat-bubble">
          <div class="sp-chat-bubble__text">No problem at all. Have him join warm-up when he gets there.</div>
          <div class="sp-chat-bubble__time">Yesterday · 7:05 PM</div>
        </div>
      </div>
    </div>

    <div class="sp-chat-composer">
      <button class="sp-btn sp-btn--ghost" type="button" title="Attach"><i class="fa-solid fa-paperclip"></i></button>
      <input class="sp-input" type="text" placeholder="Type a message..." id="chatInput" />
      <button class="sp-btn sp-btn--primary" type="button" id="chatSend"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
  </div>
</div>

<script>
(function(){
  const threads = document.querySelectorAll('.sp-chat-thread');
  const chatName = document.getElementById('chatName');
  const chatSub = document.getElementById('chatSub');
  const chatAvatar = document.getElementById('chatAvatar');
  const chatInput = document.getElementById('chatInput');
  const chatSend = document.getElementById('chatSend');
  const chatStream = document.getElementById('chatStream');

  threads.forEach(t => {
    t.addEventListener('click', function(e){
      e.preventDefault();
      threads.forEach(x => x.classList.remove('is-active'));
      this.classList.add('is-active');
      chatName.textContent = this.dataset.name;
      chatSub.textContent = this.dataset.sub;
      chatAvatar.textContent = this.dataset.name.charAt(0).toUpperCase();
      chatInput.focus();
    });
  });

  function sendMessage(){
    const text = chatInput.value.trim();
    if (!text) return;
    const row = document.createElement('div');
    row.className = 'sp-chat-bubble-row is-me';
    const time = new Date().toLocaleTimeString([], {hour:'numeric', minute:'2-digit'});
    row.innerHTML = '<div class="sp-chat-bubble"><div class="sp-chat-bubble__text">' +
      text.replace(/</g,'&lt;').replace(/>/g,'&gt;') +
      '</div><div class="sp-chat-bubble__time">' + time + '</div></div>';
    chatStream.appendChild(row);
    chatStream.scrollTop = chatStream.scrollHeight;
    chatInput.value = '';
  }

  if (chatSend) chatSend.addEventListener('click', sendMessage);
  if (chatInput) chatInput.addEventListener('keydown', function(e){ if (e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMessage();} });

  const searchBox = document.getElementById('chatSearch');
  if (searchBox) searchBox.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    threads.forEach(t => { t.style.display = ((t.dataset.name||'').toLowerCase().includes(q)||(t.dataset.sub||'').toLowerCase().includes(q)) ? '' : 'none'; });
  });
})();
</script>
<?php include __DIR__ . '/../includes/role_footer.php'; ?>
