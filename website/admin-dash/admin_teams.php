<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Teams';
$activeNav = 'teams';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$flash = null;

/**
 * Detect optional columns in teams table (season/sport/city/is_active)
 */
$teamCols = $pdo->query("SHOW COLUMNS FROM teams")->fetchAll();
$colNames = array_map(fn($r) => $r['Field'], $teamCols);

$hasSeason = in_array('season', $colNames, true);
$hasSport  = in_array('sport', $colNames, true);
$hasCity   = in_array('city', $colNames, true);
$hasActive = in_array('is_active', $colNames, true);

/**
 * CREATE team
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_team') {
  $leagueId = (int)($_POST['league_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');

  $season = trim($_POST['season'] ?? '');
  $sport = trim($_POST['sport'] ?? '');
  $city = trim($_POST['city'] ?? '');

  $isActive = isset($_POST['is_active']) ? 1 : 0;

  if ($leagueId <= 0 || $name === '') {
    $flash = ['type' => 'error', 'msg' => 'League and team name are required.'];
  } else {
    try {
      // Build insert dynamically based on existing columns
      $fields = ['league_id', 'name'];
      $params = [':league_id' => $leagueId, ':name' => $name];

      if ($hasSeason) { $fields[] = 'season'; $params[':season'] = ($season !== '' ? $season : null); }
      if ($hasSport)  { $fields[] = 'sport';  $params[':sport']  = ($sport !== '' ? $sport : null); }
      if ($hasCity)   { $fields[] = 'city';   $params[':city']   = ($city !== '' ? $city : null); }
      if ($hasActive) { $fields[] = 'is_active'; $params[':is_active'] = $isActive; }

      $placeholders = array_map(fn($f) => ':' . $f, $fields);
      // NOTE: for league_id/name we already set :league_id/:name etc, so make placeholders match
      $placeholders = [];
      foreach ($fields as $f) $placeholders[] = ':' . $f;

      $sql = "INSERT INTO teams (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
      $stmt = $pdo->prepare($sql);

      // ensure params include keys for all fields
      if (!isset($params[':league_id'])) $params[':league_id'] = $leagueId;
      if (!isset($params[':name'])) $params[':name'] = $name;
      if ($hasSeason && !isset($params[':season'])) $params[':season'] = null;
      if ($hasSport && !isset($params[':sport'])) $params[':sport'] = null;
      if ($hasCity && !isset($params[':city'])) $params[':city'] = null;
      if ($hasActive && !isset($params[':is_active'])) $params[':is_active'] = 1;

      $stmt->execute($params);

      header("Location: admin_teams.php?created=1");
      exit;
    } catch (PDOException $e) {
      $flash = ['type' => 'error', 'msg' => 'DB error: ' . $e->getMessage()];
    }
  }
}

/**
 * FILTERS
 */
$q = trim($_GET['q'] ?? '');
$filterLeagueId = (int)($_GET['league_id'] ?? 0);

/**
 * Dropdown data: leagues
 */
$leagues = $pdo->query("
  SELECT league_id, season, sport, name, status
  FROM leagues
  ORDER BY season DESC, sport ASC, name ASC
")->fetchAll();

/**
 * LIST teams (JOIN leagues)
 */
$sql = "
  SELECT
    t.team_id,
    t.name AS team_name,
    t.league_id,
    l.name AS league_name,
    l.season,
    l.sport,
    l.status
    " . ($hasCity ? ", t.city" : "") . "
    " . ($hasSeason ? ", t.season AS team_season" : "") . "
    " . ($hasSport ? ", t.sport AS team_sport" : "") . "
    " . ($hasActive ? ", t.is_active" : "") . "
  FROM teams t
  JOIN leagues l ON l.league_id = t.league_id
  WHERE 1=1
";

$params = [];

if ($q !== '') {
  $sql .= " AND (t.name LIKE :q OR l.name LIKE :q OR l.season LIKE :q OR l.sport LIKE :q)";
  $params[':q'] = "%{$q}%";
}

if ($filterLeagueId > 0) {
  $sql .= " AND l.league_id = :league_id";
  $params[':league_id'] = $filterLeagueId;
}

$sql .= " ORDER BY l.season DESC, l.sport ASC, l.name ASC, t.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$teams = $stmt->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div>
      <div class="sp-card__title">Teams</div>
      <div class="sp-card__sub">Live teams linked to leagues.</div>
    </div>

    <div class="sp-actions">
      <button class="sp-btn sp-btn--pill" type="button" data-dialog-open="#dlgTeamCreate">
        <i class="fa-solid fa-plus"></i>&nbsp; Add Team
      </button>
    </div>
  </div>

  <div class="sp-card__bd">

    <?php if (isset($_GET['created'])): ?>
      <div class="sp-alert sp-alert--success" style="margin-bottom:12px;">Team created successfully.</div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="sp-alert <?php echo $flash['type'] === 'error' ? 'sp-alert--danger' : 'sp-alert--success'; ?>" style="margin-bottom:12px;">
        <?php echo h($flash['msg']); ?>
      </div>
    <?php endif; ?>

    <form class="sp-filterbar" method="GET" action="admin_teams.php">
      <div class="sp-filterbar__left">
        <div class="sp-search">
          <i class="fa-solid fa-magnifying-glass icon"></i>
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search teams or leagues…" />
        </div>

        <select class="sp-select" name="league_id">
          <option value="0">All leagues</option>
          <?php foreach ($leagues as $l): ?>
            <option value="<?php echo (int)$l['league_id']; ?>" <?php echo $filterLeagueId === (int)$l['league_id'] ? 'selected' : ''; ?>>
              <?php echo h($l['season'] . ' · ' . $l['sport'] . ' · ' . $l['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button class="sp-btn sp-btn--ghost" type="submit"><i class="fa-solid fa-filter"></i>&nbsp; Apply</button>
        <a class="sp-btn sp-btn--ghost" href="admin_teams.php"><i class="fa-solid fa-rotate-left"></i>&nbsp; Reset</a>
      </div>
    </form>

    <div style="height:12px"></div>

    <div class="sp-table-wrap" style="max-height: 540px; border:1px solid var(--line)">
      <table id="tblTeams" class="sp-table sp-table--light">
        <thead>
          <tr>
            <th style="width:70px">ID</th>
            <th>Team</th>
            <th>League</th>
            <th style="width:120px">Season</th>
            <th style="width:120px">Sport</th>
            <?php if ($hasCity): ?><th style="width:140px">City</th><?php endif; ?>
            <?php if ($hasActive): ?><th style="width:120px">Status</th><?php endif; ?>
            <th style="width:200px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($teams as $t): ?>
            <tr>
              <td><?php echo (int)$t['team_id']; ?></td>
              <td>
                <strong><?php echo h($t['team_name']); ?></strong>
                <div class="sp-card__sub">League ID: <?php echo (int)$t['league_id']; ?></div>
              </td>
              <td><?php echo h($t['league_name']); ?></td>
              <td><?php echo h($t['season']); ?></td>
              <td><?php echo h($t['sport']); ?></td>
              <?php if ($hasCity): ?><td><?php echo $t['city'] ? h($t['city']) : '—'; ?></td><?php endif; ?>
              <?php if ($hasActive): ?>
                <td>
                  <?php $active = (int)$t['is_active'] === 1; ?>
                  <span class="sp-pill <?php echo $active ? 'sp-pill--success' : ''; ?>"><?php echo $active ? 'Active' : 'Disabled'; ?></span>
                </td>
              <?php endif; ?>
              <td>
                <div class="sp-actions">
                  <button class="sp-btn-tag primary" type="button">View</button>
                  <button class="sp-btn-tag" type="button">Edit</button>
                  <button class="sp-btn-tag danger" type="button">Disable</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($teams)): ?>
            <tr><td colspan="<?php echo 7 + ($hasCity ? 1 : 0) + ($hasActive ? 1 : 0); ?>" class="sp-card__sub">No teams found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<dialog id="dlgTeamCreate" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Add Team</div>
    <div class="sp-card__sub">Team must belong to a league.</div>
  </div>

  <div class="sp-dialog__bd">
    <form method="POST" action="admin_teams.php">
      <input type="hidden" name="action" value="create_team" />

      <div class="sp-form-grid">
        <div class="sp-col-12">
          <label class="sp-card__sub">League</label>
          <select class="sp-select" style="width:100%" name="league_id" required>
            <option value="">Select a league…</option>
            <?php foreach ($leagues as $l): ?>
              <option value="<?php echo (int)$l['league_id']; ?>">
                <?php echo h($l['season'] . ' · ' . $l['sport'] . ' · ' . $l['name']); ?> (ID <?php echo (int)$l['league_id']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Team name</label>
          <input class="sp-input" style="width:100%" name="name" placeholder="RIT Tigers U14" required />
        </div>

        <?php if ($hasSeason): ?>
          <div class="sp-col-6">
            <label class="sp-card__sub">Season (optional)</label>
            <input class="sp-input" style="width:100%" name="season" placeholder="2026 Spring" />
          </div>
        <?php endif; ?>

        <?php if ($hasSport): ?>
          <div class="sp-col-6">
            <label class="sp-card__sub">Sport (optional)</label>
            <input class="sp-input" style="width:100%" name="sport" placeholder="Soccer" />
          </div>
        <?php endif; ?>

        <?php if ($hasCity): ?>
          <div class="sp-col-12">
            <label class="sp-card__sub">City (optional)</label>
            <input class="sp-input" style="width:100%" name="city" placeholder="Rochester" />
          </div>
        <?php endif; ?>

        <?php if ($hasActive): ?>
          <div class="sp-col-12" style="display:flex; align-items:center; gap:10px; padding-top:10px;">
            <label class="sp-card__sub" style="display:flex; align-items:center; gap:10px;">
              <input type="checkbox" name="is_active" checked />
              Active
            </label>
          </div>
        <?php endif; ?>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-close>Cancel</button>
        <button class="sp-btn sp-btn--pill" type="submit">Create Team</button>
      </div>
    </form>
  </div>
</dialog>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>