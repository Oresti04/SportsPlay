<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Players';
$activeNav = 'players';

/**
 * Helpers
 */
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function calc_age(?string $dob): string {
  if (!$dob) return 'â€”';
  try {
    $d = new DateTime($dob);
    $now = new DateTime();
    return (string)$now->diff($d)->y;
  } catch (Exception $e) {
    return 'â€”';
  }
}

$flash = null;

/**
 * CREATE player
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_player') {
  $userId = (int)($_POST['user_id'] ?? 0);
  $teamId = (int)($_POST['team_id'] ?? 0);

  $dob = trim($_POST['dob'] ?? '');
  $dob = $dob !== '' ? $dob : null;

  $jersey = trim($_POST['jersey_number'] ?? '');
  $jersey = $jersey !== '' ? (int)$jersey : null;

  $position = trim($_POST['position'] ?? '');
  $position = $position !== '' ? $position : null;

  $guardianName = trim($_POST['guardian_name'] ?? '');
  $guardianName = $guardianName !== '' ? $guardianName : null;

  $guardianPhone = trim($_POST['guardian_phone'] ?? '');
  $guardianPhone = $guardianPhone !== '' ? $guardianPhone : null;

  $medical = trim($_POST['medical_notes'] ?? '');
  $medical = $medical !== '' ? $medical : null;

  if ($userId <= 0 || $teamId <= 0) {
    $flash = ['type' => 'error', 'msg' => 'User and team are required.'];
  } else {
    try {
      // prevent duplicate player profile for same user
      $chk = $pdo->prepare("SELECT player_id FROM players WHERE user_id = :uid LIMIT 1");
      $chk->execute([':uid' => $userId]);
      if ($chk->fetch()) {
        $flash = ['type' => 'error', 'msg' => 'This user already has a player profile.'];
      } else {
        $ins = $pdo->prepare("
          INSERT INTO players (user_id, team_id, jersey_number, position, dob, guardian_name, guardian_phone, medical_notes)
          VALUES (:user_id, :team_id, :jersey_number, :position, :dob, :guardian_name, :guardian_phone, :medical_notes)
        ");
        $ins->execute([
          ':user_id' => $userId,
          ':team_id' => $teamId,
          ':jersey_number' => $jersey,
          ':position' => $position,
          ':dob' => $dob,
          ':guardian_name' => $guardianName,
          ':guardian_phone' => $guardianPhone,
          ':medical_notes' => $medical,
        ]);

        header("Location: admin_players.php?created=1");
        exit;
      }
    } catch (PDOException $e) {
      $flash = ['type' => 'error', 'msg' => 'DB error: ' . $e->getMessage()];
    }
  }
}

/**
 * FILTERS
 */
$q = trim($_GET['q'] ?? '');
$filterTeamId = (int)($_GET['team_id'] ?? 0);
$filterLeagueId = (int)($_GET['league_id'] ?? 0);

/**
 * Dropdown data
 */
$teams = $pdo->query("
  SELECT t.team_id, t.name AS team_name, l.league_id, l.name AS league_name
  FROM teams t
  JOIN leagues l ON l.league_id = t.league_id
  ORDER BY l.name ASC, t.name ASC
")->fetchAll();

$leagues = $pdo->query("
  SELECT league_id, name
  FROM leagues
  ORDER BY name ASC
")->fetchAll();

// users that don't already have a player profile
$availableUsers = $pdo->query("
  SELECT u.user_id, u.email, u.first_name, u.last_name
  FROM users u
  LEFT JOIN players p ON p.user_id = u.user_id
  WHERE p.user_id IS NULL
  ORDER BY u.last_name ASC, u.first_name ASC
  LIMIT 500
")->fetchAll();

/**
 * LIST players (JOIN)
 */
$sql = "
  SELECT
    p.player_id,
    p.dob,
    p.jersey_number,
    p.position,
    p.guardian_name,
    p.guardian_phone,
    u.user_id,
    u.email,
    u.first_name,
    u.last_name,
    t.team_id,
    t.name AS team_name,
    l.league_id,
    l.name AS league_name
  FROM players p
  JOIN users u ON u.user_id = p.user_id
  JOIN teams t ON t.team_id = p.team_id
  JOIN leagues l ON l.league_id = t.league_id
  WHERE 1=1
";

$params = [];

if ($q !== '') {
  $sql .= " AND (
    u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q
    OR t.name LIKE :q OR l.name LIKE :q
  )";
  $params[':q'] = "%{$q}%";
}

if ($filterTeamId > 0) {
  $sql .= " AND t.team_id = :team_id";
  $params[':team_id'] = $filterTeamId;
}

if ($filterLeagueId > 0) {
  $sql .= " AND l.league_id = :league_id";
  $params[':league_id'] = $filterLeagueId;
}

$sql .= " ORDER BY l.name ASC, t.name ASC, u.last_name ASC, u.first_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$players = $stmt->fetchAll();

$totalPlayers = count($players);

include __DIR__ . '/../includes/admin_header.php';
?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div>
      <div class="sp-card__title">Players</div>
      <div class="sp-card__sub">Live roster from DB (users + players + teams + leagues).</div>
    </div>

    <div class="sp-actions">
      <button class="sp-btn sp-btn--ghost" type="button" data-dialog-open="#dlgPlayerCreate">
        <i class="fa-solid fa-plus"></i>&nbsp; Add Player
      </button>
    </div>
  </div>

  <div class="sp-card__bd">

    <?php if (isset($_GET['created'])): ?>
      <div class="sp-alert sp-alert--success" style="margin-bottom:12px;">
        Player created successfully.
      </div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="sp-alert <?php echo $flash['type'] === 'error' ? 'sp-alert--danger' : 'sp-alert--success'; ?>" style="margin-bottom:12px;">
        <?php echo h($flash['msg']); ?>
      </div>
    <?php endif; ?>

    <div class="sp-kpis" style="margin-bottom:14px;">
      <div class="sp-kpi"><div class="label">Total Players</div><div class="value"><?php echo (int)$totalPlayers; ?></div><div class="meta">current filters</div></div>
      <div class="sp-kpi"><div class="label">Teams</div><div class="value"><?php echo (int)count($teams); ?></div><div class="meta">in DB</div></div>
      <div class="sp-kpi"><div class="label">Leagues</div><div class="value"><?php echo (int)count($leagues); ?></div><div class="meta">in DB</div></div>
      <div class="sp-kpi"><div class="label">Available Users</div><div class="value"><?php echo (int)count($availableUsers); ?></div><div class="meta">no player profile yet</div></div>
    </div>

    <form class="sp-filterbar" method="GET" action="admin_players.php">
      <div class="sp-filterbar__left">
        <div class="sp-search">
          <i class="fa-solid fa-magnifying-glass icon"></i>
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search player, team, league, emailâ€¦" />
        </div>

        <select class="sp-select" name="team_id">
          <option value="0">All teams</option>
          <?php foreach ($teams as $t): ?>
            <option value="<?php echo (int)$t['team_id']; ?>" <?php echo $filterTeamId === (int)$t['team_id'] ? 'selected' : ''; ?>>
              <?php echo h($t['team_name']); ?> (<?php echo h($t['league_name']); ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <select class="sp-select" name="league_id">
          <option value="0">All leagues</option>
          <?php foreach ($leagues as $l): ?>
            <option value="<?php echo (int)$l['league_id']; ?>" <?php echo $filterLeagueId === (int)$l['league_id'] ? 'selected' : ''; ?>>
              <?php echo h($l['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button class="sp-btn sp-btn--ghost" type="submit">
          <i class="fa-solid fa-filter"></i>&nbsp; Apply
        </button>
        <a class="sp-btn sp-btn--ghost" href="admin_players.php">
          <i class="fa-solid fa-rotate-left"></i>&nbsp; Reset
        </a>
      </div>
    </form>

    <div style="height:12px"></div>

    <div class="sp-table-wrap" style="max-height: 540px; border:1px solid var(--line)">
      <table id="tblPlayersAdmin" class="sp-table sp-table--light">
        <thead>
          <tr>
            <th style="width:44px">#</th>
            <th>Player</th>
            <th style="width:92px">Age</th>
            <th>Team</th>
            <th style="width:140px">League</th>
            <th>Contact</th>
            <th style="width:180px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($players as $p): ?>
            <tr>
              <td><?php echo (int)$p['player_id']; ?></td>
              <td>
                <strong><?php echo h($p['first_name'] . ' ' . $p['last_name']); ?></strong>
                <div class="sp-card__sub">
                  <?php echo $p['dob'] ? 'DOB: ' . h($p['dob']) : 'DOB: â€”'; ?>
                  <?php if ($p['jersey_number'] !== null): ?> Â· #<?php echo (int)$p['jersey_number']; ?><?php endif; ?>
                  <?php if ($p['position']): ?> Â· <?php echo h($p['position']); ?><?php endif; ?>
                </div>
              </td>
              <td><?php echo h(calc_age($p['dob'])); ?></td>
              <td><?php echo h($p['team_name']); ?></td>
              <td><?php echo h($p['league_name']); ?></td>
              <td>
                <div><span class="sp-card__sub">User:</span> <?php echo h($p['email']); ?></div>
                <div class="sp-card__sub">
                  <?php echo $p['guardian_name'] ? 'Guardian: ' . h($p['guardian_name']) : 'Guardian: â€”'; ?>
                  <?php if ($p['guardian_phone']): ?> Â· <?php echo h($p['guardian_phone']); ?><?php endif; ?>
                </div>
              </td>
              <td>
                <div class="sp-actions">
                  <button class="sp-btn-tag primary" type="button">View</button>
                  <button class="sp-btn-tag" type="button">Edit</button>
                  <button class="sp-btn-tag danger" type="button">Disable</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($players)): ?>
            <tr><td colspan="7" class="sp-card__sub">No players found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<dialog id="dlgPlayerCreate" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Add Player</div>
    <div class="sp-card__sub">Creates a player profile by assigning an existing user to a team.</div>
  </div>

  <div class="sp-dialog__bd">
    <form method="POST" action="admin_players.php" id="createPlayerForm">
      <input type="hidden" name="action" value="create_player" />
      <input type="hidden" name="user_id" id="selectedUserId" value="" />

      <div class="sp-form-grid">
        <div class="sp-col-12">
          <label class="sp-card__sub">User (account)</label>
          <input type="text" id="userFilterInput" class="sp-input" style="width:100%; margin-bottom:8px;" placeholder="Search user by name or email...">
          <div class="sp-user-picker" id="userPickerList">
            <?php foreach ($availableUsers as $u): $display = trim(($u['first_name'] . ' ' . $u['last_name'])); ?>
              <button
                type="button"
                class="sp-user-option"
                data-user-id="<?php echo (int)$u['user_id']; ?>"
                data-search="<?php echo h(strtolower(($display !== '' ? $display : $u['email']) . ' ' . $u['email'])); ?>">
                <div>
                  <strong><?php echo h($display !== '' ? $display : $u['email']); ?></strong>
                  <div class="meta"><?php echo h($u['email']); ?> · ID <?php echo (int)$u['user_id']; ?></div>
                </div>
                <span class="sp-checkmark"><i class="fa-solid fa-check"></i></span>
              </button>
            <?php endforeach; ?>
          </div>
          <div id="selectedUserLabel" class="sp-card__sub" style="margin-top:6px;">No user selected.</div>
          <?php if (empty($availableUsers)): ?>
            <div class="sp-card__sub" style="margin-top:6px;">No available users. Create users first (signup) or delete player profiles.</div>
          <?php endif; ?>
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Team</label>
          <select class="sp-select" style="width:100%" name="team_id" required>
            <option value="">Select a team…</option>
            <?php foreach ($teams as $t): ?>
              <option value="<?php echo (int)$t['team_id']; ?>">
                <?php echo h($t['team_name']); ?> · <?php echo h($t['league_name']); ?> (Team ID <?php echo (int)$t['team_id']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">DOB</label>
          <input class="sp-input" style="width:100%" name="dob" type="date" />
        </div>

        <div class="sp-col-3">
          <label class="sp-card__sub">Jersey #</label>
          <input class="sp-input" style="width:100%" name="jersey_number" type="number" min="0" />
        </div>

        <div class="sp-col-3">
          <label class="sp-card__sub">Position</label>
          <input class="sp-input" style="width:100%" name="position" placeholder="Midfielder" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Guardian name</label>
          <input class="sp-input" style="width:100%" name="guardian_name" placeholder="Parent / Guardian" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Guardian phone</label>
          <input class="sp-input" style="width:100%" name="guardian_phone" placeholder="585-..." />
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Medical notes</label>
          <textarea class="sp-input" style="width:100%; min-height:80px" name="medical_notes" placeholder="Allergies, asthma, etc."></textarea>
        </div>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-close>Cancel</button>
        <button class="sp-btn sp-btn--pill" type="submit">Create Player</button>
      </div>
    </form>
  </div>
</dialog>

<script>
(function(){
  const input = document.getElementById('userFilterInput');
  const picker = document.getElementById('userPickerList');
  const selectedUserId = document.getElementById('selectedUserId');
  const selectedUserLabel = document.getElementById('selectedUserLabel');
  const createForm = document.getElementById('createPlayerForm');
  if (!input || !picker || !selectedUserId || !selectedUserLabel || !createForm) return;

  const options = Array.from(picker.querySelectorAll('.sp-user-option'));

  function selectUser(opt){
    options.forEach(function(x){ x.classList.remove('is-selected'); });
    opt.classList.add('is-selected');
    selectedUserId.value = opt.getAttribute('data-user-id') || '';
    const strong = opt.querySelector('strong');
    const name = ((strong ? strong.textContent : '') || '').trim();
    selectedUserLabel.textContent = name ? ('Selected: ' + name) : 'No user selected.';
  }

  options.forEach(function(opt){
    opt.addEventListener('click', function(){ selectUser(opt); });
  });

  input.addEventListener('input', function(){
    const q = this.value.toLowerCase().trim();
    options.forEach(function(opt){
      const text = (opt.getAttribute('data-search') || '').toLowerCase();
      opt.style.display = (q !== '' && !text.includes(q)) ? 'none' : '';
    });
  });

  createForm.addEventListener('submit', function(e){
    if (!selectedUserId.value) {
      e.preventDefault();
      selectedUserLabel.textContent = 'Please select a user before creating a player.';
      selectedUserLabel.style.color = '#b91c1c';
    }
  });
})();
</script>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>


