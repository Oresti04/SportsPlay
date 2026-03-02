<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['parent']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$parent = $demo['parent'];
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Parent Messages'; $activeNav='messages';
include __DIR__ . '/includes/role_header.php'; $selected = $parent['selected']; ?>
<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Coach Messages (Private)</div><div class="sp-card__sub">Direct coach-parent chat · ChatGPT-like style UI (static for now)</div></div>
    <div class="sp-actions">
      <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-lock"></i> Private Channel</span>
      <a class="sp-btn sp-btn--ghost" href="parent_announcements.php"><i class="fa-solid fa-bullhorn"></i> Team Announcements</a>
    </div>
  </div>
  <div class="sp-card__bd">
    <div class="sp-chat-layout sp-chat-layout--single">
      <section class="sp-chat-main">
        <div class="sp-chat-header">
          <div class="sp-chat-thread__avatar large">C</div>
          <div>
            <div class="sp-chat-header__title"><?php echo htmlspecialchars($selected['coach']); ?></div>
            <div class="sp-chat-header__sub"><?php echo htmlspecialchars($selected['team']); ?> · Private chat</div>
          </div>
          <span class="sp-pill sp-pill--success" style="margin-left:auto;">Online demo</span>
        </div>

        <div class="sp-chat-stream">
          <?php foreach ($parent['chat_thread'] as $m): ?>
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