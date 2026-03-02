<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['coach']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$coach = $demo['coach'];
$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='coach_sidebar.php';
$pageTitle='Coach Dashboard'; $activeNav='dashboard';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card sp-page-hero">
    <div class="sp-hero">
      <div>
        <h1>Welcome back, <?php echo htmlspecialchars($userFirst); ?>!</h1>
        <p>Coach dashboard · cleaner overview with separate pages for roster, messages, announcements and settings.</p>
      </div>
      <div class="sp-toolbar">
        <div class="sp-field"><label>Team</label><select class="sp-select"><option><?php echo htmlspecialchars($coach['team']['name']); ?></option></select></div>
        <div class="sp-field"><label>Season</label><select class="sp-select"><option><?php echo htmlspecialchars($coach['team']['season']); ?></option></select></div>
        <a class="sp-btn sp-btn--primary" href="coach_announcements.php"><i class="fa-solid fa-bullhorn"></i> New Announcement</a>
      </div>
    </div>
    <div class="sp-kpis sp-kpis--loose" style="margin-top:20px;">
      <div class="sp-kpi"><div class="label">Players</div><div class="value"><?php echo count($coach['players']); ?></div><div class="meta">Assigned roster</div></div>
      <div class="sp-kpi"><div class="label">Sessions</div><div class="value"><?php echo count($coach['schedule']); ?></div><div class="meta">Upcoming events</div></div>
      <div class="sp-kpi"><div class="label">Parents</div><div class="value"><?php echo count($coach['parents']); ?></div><div class="meta">Contacts</div></div>
      <div class="sp-kpi"><div class="label">Record</div><div class="value"><?php echo htmlspecialchars($coach['team']['record']); ?></div><div class="meta">Current season</div></div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Team Snapshot</div><div class="sp-card__sub">Quick details</div></div>
      <span class="sp-pill" style="background:rgba(255,255,255,.14); color:#fff; border-color:rgba(255,255,255,.2);"><i class="fa-solid fa-shield-halved"></i> Division A</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack sp-stack--sm">
        <div class="sp-statline"><span>League</span><strong><?php echo htmlspecialchars($coach['team']['league']); ?></strong></div>
        <div class="sp-statline"><span>Home Field</span><strong><?php echo htmlspecialchars($coach['team']['home_field']); ?></strong></div>
        <div class="sp-statline"><span>Assistant</span><strong><?php echo htmlspecialchars($coach['team']['assistant']); ?></strong></div>
        <div class="sp-statline"><span>Training Days</span><strong><?php echo htmlspecialchars($coach['team']['training_days']); ?></strong></div>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-stack sp-stack--sm">
        <a class="sp-btn sp-btn--ghost" href="coach_team.php"><i class="fa-solid fa-people-group"></i> Team & Roster</a>
        <a class="sp-btn sp-btn--ghost" href="coach_messages.php"><i class="fa-solid fa-comments"></i> Private Messages</a>
        <a class="sp-btn sp-btn--ghost" href="coach_settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
      </div>
    </div>
  </section>
</section>

<section class="sp-split sp-split--gap-lg">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Upcoming Schedule Preview</div><div class="sp-card__sub">Use Team & Roster page for full list</div></div>
      <a class="sp-btn sp-btn--ghost" href="coach_team.php#schedule"><i class="fa-solid fa-calendar-days"></i> Open Full Schedule</a>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack">
        <?php foreach (array_slice($coach['schedule'], 0, 3) as $s): ?>
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

  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Recent Conversations</div><div class="sp-card__sub">Private chats (preview)</div></div>
      <a class="sp-btn sp-btn--primary" href="coach_messages.php"><i class="fa-solid fa-comments"></i> Open Chat</a>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack">
        <?php foreach (array_slice($coach['conversations'], 0, 4) as $c): ?>
          <div class="sp-list-card">
            <div class="sp-list-card__main">
              <div class="sp-list-card__title"><?php echo htmlspecialchars($c['name']); ?> <span class="sp-muted">· <?php echo htmlspecialchars($c['channel']); ?></span></div>
              <div class="sp-list-card__sub"><?php echo htmlspecialchars($c['last']); ?></div>
            </div>
            <div class="sp-list-card__meta">
              <div><?php echo htmlspecialchars($c['time']); ?></div>
              <?php if (!empty($c['unread'])): ?><span class="sp-pill sp-pill--danger"><?php echo (int)$c['unread']; ?> new</span><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>