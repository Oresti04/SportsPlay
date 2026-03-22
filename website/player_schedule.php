<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['player']);

$userId = (int)$_SESSION['user_id'];
$player = sp_player_data($pdo, $userId);
$events = $player['schedule'] ?? [];

$roleLabel='Player'; $roleSub='Player Console'; $sidebarInclude='player_sidebar.php';
$pageTitle='Schedule'; $activeNav='schedule';
include __DIR__ . '/includes/role_header.php'; ?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">My Calendar</div><div class="sp-card__sub">Trainings and matches from coach updates</div></div>
    <span class="sp-pill sp-pill--warning"><?php echo count($events); ?> events</span>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($events)): ?>
      <p class="sp-muted">No upcoming events yet.</p>
    <?php else: ?>
      <div class="sp-stack">
        <?php foreach ($events as $e): ?>
          <article class="sp-list-card">
            <div class="sp-list-card__main">
              <div class="sp-list-card__title"><?php echo htmlspecialchars($e['kind']); ?> · <?php echo htmlspecialchars($e['title']); ?></div>
              <div class="sp-list-card__sub"><?php echo htmlspecialchars($e['date']); ?> · <?php echo htmlspecialchars($e['time']); ?></div>
            </div>
            <div class="sp-list-card__meta"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($e['location']); ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>
