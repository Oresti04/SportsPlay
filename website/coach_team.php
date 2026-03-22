<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['coach']);

$userId = (int)$_SESSION['user_id'];
$userFirst = trim((string)($_SESSION['user_name'] ?? 'Coach'));
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'create_team') {
        $leagueId = (int)($_POST['league_id'] ?? 0);
        $teamName = trim((string)($_POST['team_name'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $result = sp_create_team_for_coach($pdo, $userId, $leagueId, $teamName, $city);
        if (!empty($result['ok'])) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'review_request') {
        $requestId = (int)($_POST['request_id'] ?? 0);
        $decision = trim((string)($_POST['decision'] ?? ''));
        $result = sp_review_join_request($pdo, $userId, $requestId, $decision);
        if (!empty($result['ok'])) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$coach = sp_coach_data($pdo, $userId);
$joinRequests = sp_get_coach_join_requests($pdo, $userId);
$leagues = sp_get_leagues($pdo);

$roleLabel = 'Coach'; $roleSub = 'Coach Console'; $sidebarInclude = 'coach_sidebar.php';
$pageTitle = 'Coach Team & Roster'; $activeNav = 'team';
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

<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Team Information</div><div class="sp-card__sub">Core team details</div></div>
      <span class="sp-pill sp-pill--success"><i class="fa-solid fa-shield"></i> Active Season</span>
    </div>
    <div class="sp-card__bd">
      <div class="sp-team-info-grid">
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
          <div class="sp-statline"><span>Sport</span><strong><?php echo htmlspecialchars($coach['team']['sport']); ?></strong></div>
        </div>
      </div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Create Team</div><div class="sp-card__sub">Add a new team and assign yourself as head coach</div></div>
    </div>
    <div class="sp-card__bd">
      <form method="post" action="coach_team.php" class="sp-stack sp-stack--sm">
        <input type="hidden" name="action" value="create_team">
        <label class="sp-form-label">League</label>
        <select class="sp-select" name="league_id" style="width:100%;" required>
          <option value="">Choose league</option>
          <?php foreach ($leagues as $league): ?>
            <option value="<?php echo (int)$league['league_id']; ?>">
              <?php echo htmlspecialchars($league['name'] . ' · ' . $league['season'] . ' · ' . $league['sport']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <label class="sp-form-label">Team name</label>
        <input class="sp-input" type="text" name="team_name" maxlength="100" placeholder="Team name" required>
        <label class="sp-form-label">City</label>
        <input class="sp-input" type="text" name="city" maxlength="100" placeholder="City (optional)">
        <button class="sp-btn sp-btn--primary" type="submit"><i class="fa-solid fa-plus"></i> Create Team</button>
      </form>
    </div>
  </section>
</section>

<section class="sp-card" id="requests">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Join Requests</div><div class="sp-card__sub">Players asking to join your team</div></div>
    <span class="sp-pill"><?php echo count($joinRequests); ?> pending</span>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($joinRequests)): ?>
      <p class="sp-muted">No pending join requests.</p>
    <?php else: ?>
      <div class="sp-table-wrap sp-table-wrap--light">
        <table class="sp-table sp-table--light">
          <thead><tr><th>Player</th><th>Team</th><th>Email</th><th>Message</th><th>Requested</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($joinRequests as $r): ?>
              <tr>
                <td style="font-weight:700;"><?php echo htmlspecialchars(trim($r['first_name'] . ' ' . $r['last_name'])); ?></td>
                <td><?php echo htmlspecialchars($r['team_name']); ?></td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo htmlspecialchars($r['message'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($r['created_at']))); ?></td>
                <td>
                  <div class="sp-actions">
                    <form method="post" action="coach_team.php" style="display:inline;">
                      <input type="hidden" name="action" value="review_request">
                      <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                      <input type="hidden" name="decision" value="approved">
                      <button class="sp-btn sp-btn--primary" type="submit">Approve</button>
                    </form>
                    <form method="post" action="coach_team.php" style="display:inline;">
                      <input type="hidden" name="action" value="review_request">
                      <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                      <input type="hidden" name="decision" value="rejected">
                      <button class="sp-btn sp-btn--ghost" type="submit">Reject</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="sp-card" id="players">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Assigned Players</div><div class="sp-card__sub">Live roster from database</div></div>
    <span class="sp-pill"><?php echo count($coach['players']); ?> players</span>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($coach['players'])): ?>
      <p class="sp-muted">No players assigned to this team yet.</p>
    <?php else: ?>
      <div class="sp-table-wrap sp-table-wrap--light">
        <table class="sp-table sp-table--light">
        <thead><tr><th>#</th><th>Player</th><th>Position</th><th>Age</th><th>Guardian</th><th>Phone</th></tr></thead>
        <tbody>
          <?php foreach ($coach['players'] as $p): ?>
            <tr>
              <td><?php echo (int)$p['number']; ?></td>
              <td style="font-weight:800;"><?php echo htmlspecialchars($p['name']); ?></td>
              <td><?php echo htmlspecialchars($p['pos']); ?></td>
              <td><?php echo is_numeric($p['age']) ? (int)$p['age'] : '-'; ?></td>
              <td><?php echo htmlspecialchars($p['parent']); ?></td>
              <td><?php echo htmlspecialchars($p['phone']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/role_footer.php'; ?>
