<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['player']);

$userId = (int)$_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamId = (int)($_POST['team_id'] ?? 0);
    $message = trim((string)($_POST['message'] ?? ''));
    $result = sp_submit_team_join_request($pdo, $userId, $teamId, $message);
    if (!empty($result['ok'])) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$directory = sp_player_team_directory($pdo, $userId);
$roleLabel = 'Player'; $roleSub = 'Player Console'; $sidebarInclude = 'player_sidebar.php';
$pageTitle = 'Team Directory'; $activeNav = 'teams';
include __DIR__ . '/includes/role_header.php'; ?>

<?php if ($success): ?>
  <div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:12px;padding:10px 14px;margin-bottom:12px;color:#15803d;font-size:13px;font-weight:600;">
    <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:10px 14px;margin-bottom:12px;color:#b91c1c;font-size:13px;font-weight:600;">
    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">All Teams</div><div class="sp-card__sub">Browse teams and send join requests</div></div>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($directory['teams'])): ?>
      <p class="sp-muted">No active teams found.</p>
    <?php else: ?>
      <div class="sp-table-wrap sp-table-wrap--light">
        <table class="sp-table sp-table--light">
          <thead><tr><th>Team</th><th>League</th><th>Season</th><th>Coach</th><th>Status</th><th>Request</th></tr></thead>
          <tbody>
            <?php foreach ($directory['teams'] as $team): ?>
              <tr>
                <td style="font-weight:700;"><?php echo htmlspecialchars($team['team_name'] . ' · ' . $team['city']); ?></td>
                <td><?php echo htmlspecialchars($team['league_name'] . ' · ' . $team['sport']); ?></td>
                <td><?php echo htmlspecialchars($team['season']); ?></td>
                <td><?php echo htmlspecialchars($team['coach_name']); ?></td>
                <td>
                  <?php if ($team['is_current']): ?>
                    <span class="sp-pill sp-pill--success">Current team</span>
                  <?php elseif ($team['request_status'] === 'pending'): ?>
                    <span class="sp-pill sp-pill--warning">Pending</span>
                  <?php elseif ($team['request_status'] === 'approved'): ?>
                    <span class="sp-pill sp-pill--success">Approved</span>
                  <?php elseif ($team['request_status'] === 'rejected'): ?>
                    <span class="sp-pill">Rejected</span>
                  <?php else: ?>
                    <span class="sp-pill">No request</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($team['is_current']): ?>
                    <span class="sp-muted">Already assigned</span>
                  <?php elseif ($team['request_status'] === 'pending'): ?>
                    <span class="sp-muted">Waiting for coach</span>
                  <?php else: ?>
                    <form method="post" action="player_teams.php" class="sp-stack sp-stack--sm">
                      <input type="hidden" name="team_id" value="<?php echo (int)$team['team_id']; ?>">
                      <input class="sp-input" type="text" name="message" maxlength="180" placeholder="Optional message to coach">
                      <button class="sp-btn sp-btn--primary" type="submit">Send Request</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/role_footer.php'; ?>
