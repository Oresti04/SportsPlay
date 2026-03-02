<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['coach']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$coach = $demo['coach'];
$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='coach_sidebar.php';
$pageTitle='Coach Messages'; $activeNav='messages';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Private Messages</div><div class="sp-card__sub">Coach ↔ parent / player direct chat (static UI, backend-ready layout)</div></div>
    <div class="sp-actions">
      <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-lock"></i> Private</span>
      <a class="sp-btn sp-btn--ghost" href="coach_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
    </div>
  </div>
  <div class="sp-card__bd">
    <div class="sp-chat-layout">
      <aside class="sp-chat-sidebar">
        <div class="sp-chat-sidebar__top">
          <div class="sp-search" style="width:100%;">
            <i class="fa-solid fa-magnifying-glass icon"></i>
            <input type="text" placeholder="Search chats (UI only)" style="width:100%;max-width:none;" />
          </div>
        </div>
        <div class="sp-chat-threadlist">
          <?php foreach ($coach['conversations'] as $i => $c): ?>
            <a href="#" class="sp-chat-thread <?php echo $i === 0 ? 'is-active' : ''; ?>">
              <div class="sp-chat-thread__avatar"><?php echo htmlspecialchars(strtoupper(substr($c['name'], 0, 1))); ?></div>
              <div class="sp-chat-thread__body">
                <div class="sp-chat-thread__row">
                  <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                  <span><?php echo htmlspecialchars($c['time']); ?></span>
                </div>
                <div class="sp-chat-thread__sub"><?php echo htmlspecialchars($c['channel']); ?> · <?php echo htmlspecialchars($c['child']); ?></div>
                <div class="sp-chat-thread__last"><?php echo htmlspecialchars($c['last']); ?></div>
              </div>
              <?php if (!empty($c['unread'])): ?><span class="sp-chat-thread__badge"><?php echo (int)$c['unread']; ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </aside>

      <section class="sp-chat-main">
        <div class="sp-chat-header">
          <div class="sp-chat-thread__avatar large">A</div>
          <div>
            <div class="sp-chat-header__title">Ana Petrovic</div>
            <div class="sp-chat-header__sub">Parent · Luka Petrovic · Private channel</div>
          </div>
          <div class="sp-actions" style="margin-left:auto;">
            <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-phone"></i> Call</button>
            <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-circle-info"></i> Info</button>
          </div>
        </div>

        <div class="sp-chat-stream">
          <?php foreach ($coach['chat_thread'] as $m): ?>
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
          <input type="text" class="sp-input" placeholder="Write a private message to parent/player... (static demo)" />
          <button class="sp-btn sp-btn--primary" type="button"><i class="fa-solid fa-paper-plane"></i> Send</button>
        </div>
        <div class="sp-muted" style="margin-top:10px; font-size:12px;">UI is static only — backend team can connect websocket/realtime + message persistence later.</div>
      </section>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>