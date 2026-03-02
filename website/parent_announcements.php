<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['parent']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$parent = $demo['parent'];
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Coach Announcements'; $activeNav='announcements';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Coach Announcements</div><div class="sp-card__sub">Broadcast updates from coach to parents/players</div></div>
      <span class="sp-pill sp-pill--success"><i class="fa-solid fa-bullhorn"></i> Read-only feed</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack">
        <?php foreach ($parent['announcements'] as $a): ?>
          <article class="sp-announcement-card">
            <div class="sp-announcement-card__hd">
              <div>
                <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                <div class="sp-announcement-card__sub"><?php echo htmlspecialchars($a['time']); ?> · Coach Broadcast</div>
              </div>
              <span class="sp-pill">Coach</span>
            </div>
            <p><?php echo htmlspecialchars($a['body']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd"><div><div class="sp-card__title">Quick Contact</div><div class="sp-card__sub">Private issues → use chat</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-stack sp-stack--sm">
        <div class="sp-statline"><span>Coach</span><strong><?php echo htmlspecialchars($parent['selected']['coach']); ?></strong></div>
        <div class="sp-statline"><span>Email</span><strong><?php echo htmlspecialchars($parent['selected']['coach_email']); ?></strong></div>
        <div class="sp-statline"><span>Phone</span><strong><?php echo htmlspecialchars($parent['selected']['coach_phone']); ?></strong></div>
      </div>
      <div class="sp-sep"></div>
      <a class="sp-btn sp-btn--ghost" href="parent_messages.php"><i class="fa-solid fa-comments"></i> Open Private Chat</a>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>