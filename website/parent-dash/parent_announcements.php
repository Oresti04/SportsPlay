<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['parent']);
$userId = (int)$_SESSION['user_id'];
$parent = sp_parent_data($pdo, $userId);
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Announcements'; $activeNav='announcements';
include __DIR__ . '/../includes/role_header.php'; ?>
<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Coach Announcements</div><div class="sp-card__sub">Team broadcasts from the coaching staff</div></div>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($parent['announcements'])): ?>
      <p class="sp-muted">No announcements from the coach yet.</p>
    <?php else: ?>
      <div class="sp-stack">
        <?php foreach ($parent['announcements'] as $a): ?>
          <article class="sp-announcement-card">
            <div class="sp-announcement-card__hd">
              <div><h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <div class="sp-announcement-card__sub"><?php echo htmlspecialchars($a['time']); ?></div></div>
            </div>
            <p><?php echo htmlspecialchars($a['body']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/../includes/role_footer.php'; ?>
