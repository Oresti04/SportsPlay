<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['parent']);

$userId = (int)$_SESSION['user_id'];
$parent = sp_parent_data($pdo, $userId);
$children = $parent['children'] ?? [];

$selectedPlayerId = (int)($_GET['player_id'] ?? 0);
$selectedChild = null;
if (!empty($children)) {
    foreach ($children as $child) {
        if ((int)$child['player_id'] === $selectedPlayerId) {
            $selectedChild = $child;
            break;
        }
    }
    if (!$selectedChild) {
        $selectedChild = $children[0];
    }
}

$events = [];
if ($selectedChild && !empty($selectedChild['team_id'])) {
    $events = sp_team_schedule($pdo, (int)$selectedChild['team_id']);
}

$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Schedule'; $activeNav='schedule';
include __DIR__ . '/../includes/role_header.php'; ?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Family Schedule</div><div class="sp-card__sub">Trainings and matches published by coach</div></div>
    <span class="sp-pill sp-pill--warning"><?php echo count($events); ?> events</span>
  </div>
  <div class="sp-card__bd">
    <?php if (!empty($children)): ?>
      <form method="get" action="parent_schedule.php" class="sp-actions" style="margin-bottom:12px;">
        <label class="sp-form-label" style="margin:0;">Child</label>
        <select class="sp-select" name="player_id">
          <?php foreach ($children as $child): ?>
            <option value="<?php echo (int)$child['player_id']; ?>" <?php echo $selectedChild && (int)$selectedChild['player_id'] === (int)$child['player_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($child['name'] . ' · ' . $child['team']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="sp-btn sp-btn--ghost" type="submit">Load</button>
      </form>
    <?php endif; ?>

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
<?php include __DIR__ . '/../includes/role_footer.php'; ?>
