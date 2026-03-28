<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['parent']);

$userId = (int)$_SESSION['user_id'];
$userFirst = trim((string)($_SESSION['user_name'] ?? 'Parent'));
$parent = sp_parent_data($pdo, $userId);

$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Parent Dashboard'; $activeNav='dashboard';
include __DIR__ . '/../includes/role_header.php';
$selected = $parent['selected'];
?>
<section class="sp-page-grid">
  <section class="sp-card sp-page-hero">
    <div class="sp-hero">
      <div>
        <h1>Hi <?php echo htmlspecialchars($userFirst); ?>!</h1>
        <p>Parent dashboard &middot; child overview, schedule, and billing summary.</p>
      </div>
      <div class="sp-toolbar">
        <?php if (!empty($parent['children'])): ?>
        <div class="sp-field"><label>Child</label><select class="sp-select">
          <?php foreach ($parent['children'] as $c): ?>
            <option><?php echo htmlspecialchars($c['name']); ?></option>
          <?php endforeach; ?>
        </select></div>
        <?php endif; ?>
        <a class="sp-btn sp-btn--ghost" href="parent_schedule.php"><i class="fa-solid fa-calendar-days"></i> Schedule</a>
        <a class="sp-btn sp-btn--primary" href="parent_payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a>
      </div>
    </div>
    <div class="sp-kpis sp-kpis--loose" style="margin-top:20px;">
      <div class="sp-kpi"><div class="label">Children</div><div class="value"><?php echo count($parent['children']); ?></div><div class="meta">Linked profiles</div></div>
      <div class="sp-kpi"><div class="label">Upcoming</div><div class="value"><?php echo count($parent['schedule']); ?></div><div class="meta">Events</div></div>
      <div class="sp-kpi"><div class="label">Coach</div><div class="value"><?php echo !empty($selected['coach']) && $selected['coach'] !== '—' ? '1' : '0'; ?></div><div class="meta"><?php echo htmlspecialchars($selected['coach']); ?></div></div>
      <div class="sp-kpi"><div class="label">Payments</div><div class="value"><?php echo count($parent['payments']); ?></div><div class="meta">Total records</div></div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Selected Child</div><div class="sp-card__sub">Quick profile snapshot</div></div>
      <span class="sp-pill" style="background:rgba(255,255,255,.14); color:#fff; border-color:rgba(255,255,255,.2);"><i class="fa-solid fa-user"></i> Active</span>
    </div>
    <div class="sp-card__bd">
      <?php if (empty($parent['children'])): ?>
        <p class="sp-muted">No children linked to your account yet. Please contact the admin to link a player profile.</p>
      <?php else: ?>
        <div class="sp-stack sp-stack--sm">
          <div class="sp-statline"><span>Name</span><strong><?php echo htmlspecialchars($selected['name']); ?></strong></div>
          <div class="sp-statline"><span>Team</span><strong><?php echo htmlspecialchars($selected['team']); ?></strong></div>
          <div class="sp-statline"><span>Position</span><strong><?php echo htmlspecialchars($selected['position']); ?></strong></div>
          <div class="sp-statline"><span>Coach</span><strong><?php echo htmlspecialchars($selected['coach']); ?></strong></div>
        </div>
        <div class="sp-sep"></div>
        <div class="sp-actions">
          <a class="sp-btn sp-btn--ghost" href="parent_schedule.php"><i class="fa-solid fa-calendar-days"></i> Full Schedule</a>
          <a class="sp-btn sp-btn--ghost" href="parent_messages.php"><i class="fa-solid fa-comments"></i> Message Coach</a>
          <a class="sp-btn sp-btn--ghost" href="parent_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
        </div>
      <?php endif; ?>
    </div>
  </section>
</section>

<section class="sp-split sp-split--gap-lg">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Schedule Preview</div><div class="sp-card__sub">Upcoming sessions</div></div>
    </div>
    <div class="sp-card__bd">
      <?php if (empty($parent['schedule'])): ?>
        <p class="sp-muted">No upcoming events.</p>
      <?php else: ?>
        <div class="sp-stack">
          <?php foreach (array_slice($parent['schedule'], 0, 5) as $s): ?>
            <div class="sp-list-card">
              <div class="sp-list-card__main">
                <div class="sp-list-card__title"><?php echo htmlspecialchars($s['kind']); ?> &middot; <?php echo htmlspecialchars($s['title']); ?></div>
                <div class="sp-list-card__sub"><?php echo htmlspecialchars($s['date']); ?> &middot; <?php echo htmlspecialchars($s['time']); ?></div>
              </div>
              <div class="sp-list-card__meta"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($s['location']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Announcements</div><div class="sp-card__sub">From the coach</div></div>
    </div>
    <div class="sp-card__bd">
      <?php if (empty($parent['announcements'])): ?>
        <p class="sp-muted">No announcements yet.</p>
      <?php else: ?>
        <div class="sp-stack">
          <?php foreach ($parent['announcements'] as $a): ?>
            <article class="sp-announcement-card">
              <h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <div class="sp-announcement-card__sub"><?php echo htmlspecialchars($a['time']); ?></div>
              <p><?php echo htmlspecialchars($a['body']); ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</section>
<?php include __DIR__ . '/../includes/role_footer.php'; ?>
