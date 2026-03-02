<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['parent']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$parent = $demo['parent'];
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Parent Dashboard'; $activeNav='dashboard';
include __DIR__ . '/includes/role_header.php';
$selected = $parent['selected'];
?>
<section class="sp-page-grid">
  <section class="sp-card sp-page-hero">
    <div class="sp-hero">
      <div>
        <h1>Hi <?php echo htmlspecialchars($userFirst); ?>!</h1>
        <p>Parent dashboard · child overview, schedule preview, notifications and billing summary.</p>
      </div>
      <div class="sp-toolbar">
        <div class="sp-field"><label>Child</label><select class="sp-select"><option><?php echo htmlspecialchars($selected['name']); ?></option></select></div>
        <a class="sp-btn sp-btn--primary" href="parent_payments.php"><i class="fa-solid fa-credit-card"></i> Open Payments</a>
      </div>
    </div>
    <div class="sp-kpis sp-kpis--loose" style="margin-top:20px;">
      <div class="sp-kpi"><div class="label">Children</div><div class="value"><?php echo count($parent['children']); ?></div><div class="meta">Linked profiles</div></div>
      <div class="sp-kpi"><div class="label">Upcoming Events</div><div class="value"><?php echo count($parent['schedule']); ?></div><div class="meta">Trainings + matches</div></div>
      <div class="sp-kpi"><div class="label">Coach</div><div class="value">1</div><div class="meta"><?php echo htmlspecialchars($selected['coach']); ?></div></div>
      <div class="sp-kpi"><div class="label">Payment Alerts</div><div class="value">1</div><div class="meta">Uniform kit pending</div></div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Selected Child</div><div class="sp-card__sub">Quick profile snapshot</div></div>
      <span class="sp-pill" style="background:rgba(255,255,255,.14); color:#fff; border-color:rgba(255,255,255,.2);"><i class="fa-solid fa-user"></i> Active</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack sp-stack--sm">
        <div class="sp-statline"><span>Name</span><strong><?php echo htmlspecialchars($selected['name']); ?></strong></div>
        <div class="sp-statline"><span>Team</span><strong><?php echo htmlspecialchars($selected['team']); ?></strong></div>
        <div class="sp-statline"><span>Position</span><strong><?php echo htmlspecialchars($selected['position']); ?></strong></div>
        <div class="sp-statline"><span>Coach</span><strong><?php echo htmlspecialchars($selected['coach']); ?></strong></div>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-actions">
        <a class="sp-btn sp-btn--ghost" href="parent_messages.php"><i class="fa-solid fa-comments"></i> Private Chat</a>
        <a class="sp-btn sp-btn--ghost" href="parent_announcements.php"><i class="fa-solid fa-bullhorn"></i> Coach Announcements</a>
      </div>
    </div>
  </section>
</section>

<section class="sp-split sp-split--gap-lg">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Notifications</div><div class="sp-card__sub">Latest updates from the team</div></div>
      <span class="sp-pill sp-pill--warning">Feed</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack">
        <?php foreach ($parent['notifications'] as $n): ?>
          <?php $pill = 'sp-pill'; if ($n['type']==='warning') $pill='sp-pill sp-pill--warning'; if ($n['type']==='success') $pill='sp-pill sp-pill--success'; ?>
          <div class="sp-list-card">
            <div class="sp-list-card__main">
              <div class="sp-list-card__title"><span class="<?php echo $pill; ?>"><?php echo strtoupper(htmlspecialchars($n['type'])); ?></span></div>
              <div class="sp-list-card__sub"><?php echo htmlspecialchars($n['text']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Schedule Preview</div><div class="sp-card__sub">Upcoming sessions</div></div>
      <a class="sp-btn sp-btn--ghost" href="parent_announcements.php"><i class="fa-solid fa-calendar-days"></i> Team Updates</a>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack">
        <?php foreach ($parent['schedule'] as $s): ?>
          <div class="sp-list-card">
            <div class="sp-list-card__main">
              <div class="sp-list-card__title"><?php echo htmlspecialchars($s['kind']); ?> · <?php echo htmlspecialchars($s['title']); ?></div>
              <div class="sp-list-card__sub"><?php echo htmlspecialchars($s['date']); ?> · <?php echo htmlspecialchars($s['time']); ?></div>
            </div>
            <div class="sp-list-card__meta"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($s['location']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>