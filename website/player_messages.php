<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['player']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$player = $demo['player'];
$roleLabel='Player'; $roleSub='Player Console'; $sidebarInclude='player_sidebar.php';
$pageTitle='Coach Messages'; $activeNav='messages';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Coach Messages + Private Chat</div><div class="sp-card__sub">Inbox + direct private conversation (static demo)</div></div>
    <div class="sp-actions">
      <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-lock"></i> Private</span>
      <a class="sp-btn sp-btn--ghost" href="player_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
    </div>
  </div>
  <div class="sp-card__bd">
    <div class="sp-chat-layout sp-chat-layout--single">
      <section class="sp-chat-main">
        <div class="sp-chat-header">
          <div class="sp-chat-thread__avatar large">C</div>
          <div>
            <div class="sp-chat-header__title">Coach Jovan</div>
            <div class="sp-chat-header__sub"><?php echo htmlspecialchars($player['profile']['team']); ?> · Private chat</div>
          </div>
          <span class="sp-pill sp-pill--success" style="margin-left:auto;">Coach online (demo)</span>
        </div>

        <div class="sp-inline-inbox">
          <?php foreach ($player['messages'] as $m): ?>
            <div class="sp-inline-inbox__item">
              <div class="sp-inline-inbox__title"><?php echo htmlspecialchars($m['subject']); ?></div>
              <div class="sp-inline-inbox__body"><?php echo htmlspecialchars($m['body']); ?></div>
              <div class="sp-inline-inbox__meta"><?php echo htmlspecialchars($m['time']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="sp-chat-stream">
          <?php foreach ($player['chat_thread'] as $m): ?>
            <div class="sp-chat-bubble-row <?php echo $m['who'] === 'me' ? 'is-me' : 'is-other'; ?>">
              <div class="sp-chat-bubble">
                <div class="sp-chat-bubble__text"><?php echo htmlspecialchars($m['text']); ?></div>
                <div class="sp-chat-bubble__time"><?php echo htmlspecialchars($m['time']); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="sp-chat-composer">
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-paperclip"></i></button>
          <input type="text" class="sp-input" placeholder="Message coach privately... (static demo)" />
          <button class="sp-btn sp-btn--primary" type="button"><i class="fa-solid fa-paper-plane"></i> Send</button>
        </div>
      </section>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>