<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['coach']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$coach = $demo['coach'];
$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='coach_sidebar.php';
$pageTitle='Coach Team & Roster'; $activeNav='team';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Team Information</div><div class="sp-card__sub">Core team details</div></div>
      <span class="sp-pill sp-pill--success"><i class="fa-solid fa-shield"></i> Active Season</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-grid" style="grid-template-columns:1fr 1fr; gap:18px;">
        <div class="sp-soft-panel">
          <div class="sp-statline"><span>Team</span><strong><?php echo htmlspecialchars($coach['team']['name']); ?></strong></div>
          <div class="sp-statline"><span>Season</span><strong><?php echo htmlspecialchars($coach['team']['season']); ?></strong></div>
          <div class="sp-statline"><span>League</span><strong><?php echo htmlspecialchars($coach['team']['league']); ?></strong></div>
          <div class="sp-statline"><span>Record</span><strong><?php echo htmlspecialchars($coach['team']['record']); ?></strong></div>
        </div>
        <div class="sp-soft-panel">
          <div class="sp-statline"><span>Home Field</span><strong><?php echo htmlspecialchars($coach['team']['home_field']); ?></strong></div>
          <div class="sp-statline"><span>Assistant</span><strong><?php echo htmlspecialchars($coach['team']['assistant']); ?></strong></div>
          <div class="sp-statline"><span>Training Days</span><strong><?php echo htmlspecialchars($coach['team']['training_days']); ?></strong></div>
          <div class="sp-statline"><span>Contact Group</span><strong>Players + Parents</strong></div>
        </div>
      </div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Coach Tools</div><div class="sp-card__sub">Fast navigation</div></div>
      <span class="sp-pill" style="background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.2);"><i class="fa-solid fa-bolt"></i> Quick</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack sp-stack--sm">
        <a class="sp-btn sp-btn--ghost" href="coach_messages.php"><i class="fa-solid fa-comments"></i> Private Messages</a>
        <a class="sp-btn sp-btn--ghost" href="coach_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
        <a class="sp-btn sp-btn--ghost" href="coach_settings.php"><i class="fa-solid fa-gear"></i> Team Settings</a>
      </div>
    </div>
  </section>
</section>

<section class="sp-split sp-split--gap-lg">
  <section class="sp-card" id="players">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Assigned Players</div><div class="sp-card__sub">Roster list (demo)</div></div>
      <span class="sp-pill"><?php echo count($coach['players']); ?> players</span>
    </div>
    <div class="sp-card__bd" style="overflow:auto;">
      <table class="sp-table sp-table--light" style="width:100%; min-width:760px;">
        <thead><tr><th>#</th><th>Player</th><th>Position</th><th>Age</th><th>Attendance</th><th>Parent</th><th>Phone</th></tr></thead>
        <tbody>
          <?php foreach ($coach['players'] as $p): ?>
            <tr>
              <td><?php echo (int)$p['number']; ?></td>
              <td style="font-weight:800;"><?php echo htmlspecialchars($p['name']); ?></td>
              <td><?php echo htmlspecialchars($p['pos']); ?></td>
              <td><?php echo (int)$p['age']; ?></td>
              <td><?php echo htmlspecialchars($p['attendance']); ?></td>
              <td><?php echo htmlspecialchars($p['parent']); ?></td>
              <td><?php echo htmlspecialchars($p['phone']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="sp-card" id="schedule">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Upcoming Schedule</div><div class="sp-card__sub">Trainings + matches</div></div>
      <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-calendar-days"></i> Next 14 days</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack">
        <?php foreach ($coach['schedule'] as $s): ?>
          <div class="sp-list-card">
            <div class="sp-list-card__main">
              <div class="sp-list-card__title"><?php echo htmlspecialchars($s['kind']); ?> · <?php echo htmlspecialchars($s['title']); ?></div>
              <div class="sp-list-card__sub"><?php echo htmlspecialchars($s['date']); ?> · <?php echo htmlspecialchars($s['time']); ?></div>
            </div>
            <div class="sp-list-card__meta"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($s['location']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-card" style="margin:0;">
        <div class="sp-card__hd">
          <div><div class="sp-card__title">Parent Contacts</div><div class="sp-card__sub">Basic quick access</div></div>
          <span class="sp-pill"><?php echo count($coach['parents']); ?> contacts</span>
        </div>
        <div class="sp-card__bd" style="overflow:auto;">
          <table class="sp-table sp-table--light" style="width:100%; min-width:640px;">
            <thead><tr><th>Parent</th><th>Child</th><th>Email</th><th>Phone</th></tr></thead>
            <tbody>
              <?php foreach ($coach['parents'] as $p): ?>
                <tr><td style="font-weight:700;"><?php echo htmlspecialchars($p['parent']); ?></td><td><?php echo htmlspecialchars($p['child']); ?></td><td><?php echo htmlspecialchars($p['email']); ?></td><td><?php echo htmlspecialchars($p['phone']); ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>