<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['player']);

$userId    = (int)$_SESSION['user_id'];
$userFirst = trim((string)($_SESSION['user_name'] ?? 'Player'));
$player    = sp_player_data($pdo, $userId);

$roleLabel='Player'; $roleSub='Player Console'; $sidebarInclude='player_sidebar.php';
$pageTitle='Player Dashboard'; $activeNav='dashboard';
include __DIR__ . '/../includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card sp-page-hero">
    <div class="sp-hero">
      <div>
        <h1>Hey <?php echo htmlspecialchars($userFirst); ?>!</h1>
        <p>Player dashboard &middot; stats, schedule, standings and payments.</p>
      </div>
      <div class="sp-toolbar">
        <div class="sp-field"><label>Season</label><select class="sp-select"><option><?php echo htmlspecialchars($player['profile']['season']); ?></option></select></div>
        <a class="sp-btn sp-btn--ghost" href="player_teams.php"><i class="fa-solid fa-people-group"></i> Teams</a>
        <a class="sp-btn sp-btn--ghost" href="player_schedule.php"><i class="fa-solid fa-calendar-days"></i> Schedule</a>
        <a class="sp-btn sp-btn--ghost" href="player_messages.php"><i class="fa-solid fa-comments"></i> Coach Chat</a>
        <a class="sp-btn sp-btn--primary" href="player_payments.php"><i class="fa-solid fa-credit-card"></i> Payments</a>
      </div>
    </div>
    <div class="sp-kpis sp-kpis--loose" style="margin-top:20px;">
      <div class="sp-kpi"><div class="label">Matches</div><div class="value"><?php echo (int)$player['stats']['matches']; ?></div><div class="meta">This season</div></div>
      <div class="sp-kpi"><div class="label">Goals</div><div class="value"><?php echo (int)$player['stats']['goals']; ?></div><div class="meta">This season</div></div>
      <div class="sp-kpi"><div class="label">Assists</div><div class="value"><?php echo (int)$player['stats']['assists']; ?></div><div class="meta">This season</div></div>
      <div class="sp-kpi"><div class="label">Attendance</div><div class="value"><?php echo htmlspecialchars($player['stats']['attendance']); ?></div><div class="meta">Last 30 days</div></div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Player Overview</div><div class="sp-card__sub">Profile snapshot</div></div>
      <span class="sp-pill" style="background:rgba(255,255,255,.14); color:#fff; border-color:rgba(255,255,255,.2);"><i class="fa-solid fa-shirt"></i> #<?php echo (int)$player['profile']['number']; ?></span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-stack sp-stack--sm">
        <div class="sp-statline"><span>Name</span><strong><?php echo htmlspecialchars($player['profile']['name']); ?></strong></div>
        <div class="sp-statline"><span>Team</span><strong><?php echo htmlspecialchars($player['profile']['team']); ?></strong></div>
        <div class="sp-statline"><span>Position</span><strong><?php echo htmlspecialchars($player['profile']['position']); ?></strong></div>
        <div class="sp-statline"><span>Season</span><strong><?php echo htmlspecialchars($player['profile']['season']); ?></strong></div>
      </div>
      <div class="sp-sep"></div>
      <div class="sp-actions">
        <a class="sp-btn sp-btn--ghost" href="player_teams.php"><i class="fa-solid fa-people-group"></i> Browse Teams</a>
        <a class="sp-btn sp-btn--ghost" href="player_schedule.php"><i class="fa-solid fa-calendar-days"></i> Full Schedule</a>
        <a class="sp-btn sp-btn--ghost" href="player_messages.php"><i class="fa-solid fa-comments"></i> Private Chat</a>
        <a class="sp-btn sp-btn--ghost" href="player_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
      </div>
    </div>
  </section>
</section>

<section class="sp-split sp-split--gap-lg">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Schedule Preview</div><div class="sp-card__sub">Next training and match</div></div>
      <span class="sp-pill sp-pill--warning">Upcoming</span>
    </div>
    <div class="sp-card__bd">
      <?php if (empty($player['schedule'])): ?>
        <p class="sp-muted">No upcoming events.</p>
      <?php else: ?>
        <div class="sp-stack">
          <?php foreach (array_slice($player['schedule'], 0, 4) as $s): ?>
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
      <div><div class="sp-card__title">League Standings</div><div class="sp-card__sub">Teams in your division</div></div>
      <span class="sp-pill sp-pill--success"><i class="fa-solid fa-trophy"></i> Live</span>
    </div>
    <div class="sp-card__bd">
      <?php if (empty($player['standings'])): ?>
        <p class="sp-muted">No standings data available yet.</p>
      <?php else: ?>
        <div class="sp-table-wrap sp-table-wrap--light">
          <table class="sp-table sp-table--light">
            <thead><tr><th>Team</th><th>Pts</th><th>W</th><th>D</th><th>L</th></tr></thead>
            <tbody>
              <?php foreach ($player['standings'] as $row): ?>
                <tr>
                  <td style="font-weight:800;"><?php echo htmlspecialchars($row['team']); ?></td>
                  <td><?php echo (int)$row['pts']; ?></td>
                  <td><?php echo (int)$row['w']; ?></td>
                  <td><?php echo (int)$row['d']; ?></td>
                  <td><?php echo (int)$row['l']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <div class="sp-sep"></div>
      <div class="sp-actions">
        <a class="sp-btn sp-btn--ghost" href="player_messages.php"><i class="fa-solid fa-comments"></i> Message Coach</a>
        <a class="sp-btn sp-btn--accent" href="player_payments.php"><i class="fa-solid fa-credit-card"></i> View Payments</a>
      </div>
    </div>
  </section>
</section>
<?php include __DIR__ . '/../includes/role_footer.php'; ?>
