<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Matches';
$activeNav = 'matches';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$flash = null;

/**
 * CREATE match
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_match') {
  $leagueId = (int)($_POST['league_id'] ?? 0);
  $homeTeamId = (int)($_POST['home_team_id'] ?? 0);
  $awayTeamId = (int)($_POST['away_team_id'] ?? 0);

  $dt = trim($_POST['match_datetime'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $location = $location !== '' ? $location : null;

  $status = $_POST['status'] ?? 'scheduled';
  if (!in_array($status, ['scheduled','completed','canceled'], true)) $status = 'scheduled';

  $homeScore = ($_POST['home_score'] ?? '') !== '' ? (int)$_POST['home_score'] : null;
  $awayScore = ($_POST['away_score'] ?? '') !== '' ? (int)$_POST['away_score'] : null;

  $notes = trim($_POST['notes'] ?? '');
  $notes = $notes !== '' ? $notes : null;

  if ($leagueId <= 0 || $homeTeamId <= 0 || $awayTeamId <= 0 || $dt === '') {
    $flash = ['type' => 'error', 'msg' => 'League, home team, away team, and date/time are required.'];
  } elseif ($homeTeamId === $awayTeamId) {
    $flash = ['type' => 'error', 'msg' => 'Home team and away team cannot be the same.'];
  } else {
    try {
      // basic check: both teams belong to the league
      $chk = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE league_id = :lid AND team_id IN (:h, :a)");
      // MySQL can't bind IN with two params this way reliably in all configs, do explicit:
     $chk = $pdo->prepare("
        SELECT
          SUM(CASE WHEN team_id = :h1 THEN 1 ELSE 0 END) AS has_home,
          SUM(CASE WHEN team_id = :a1 THEN 1 ELSE 0 END) AS has_away
        FROM teams
        WHERE league_id = :lid
          AND (team_id = :h2 OR team_id = :a2)
      ");
      $chk->execute([
        ':lid' => $leagueId,
        ':h1'  => $homeTeamId,
        ':h2'  => $homeTeamId,
        ':a1'  => $awayTeamId,
        ':a2'  => $awayTeamId,
      ]);
      $row = $chk->fetch();

      if ((int)($row['has_home'] ?? 0) !== 1 || (int)($row['has_away'] ?? 0) !== 1) {
        $flash = ['type' => 'error', 'msg' => 'Both teams must belong to the selected league.'];
      } else {
        $ins = $pdo->prepare("
          INSERT INTO matches
          (league_id, home_team_id, away_team_id, match_datetime, location, status, home_score, away_score, notes)
          VALUES
          (:league_id, :home_team_id, :away_team_id, :match_datetime, :location, :status, :home_score, :away_score, :notes)
        ");
        $ins->execute([
          ':league_id' => $leagueId,
          ':home_team_id' => $homeTeamId,
          ':away_team_id' => $awayTeamId,
          ':match_datetime' => $dt,
          ':location' => $location,
          ':status' => $status,
          ':home_score' => $homeScore,
          ':away_score' => $awayScore,
          ':notes' => $notes,
        ]);

        header("Location: admin_matches.php?created=1");
        exit;
      }
    } catch (PDOException $e) {
      $flash = ['type' => 'error', 'msg' => 'DB error: ' . $e->getMessage()];
    }
  }
}

/**
 * Filters
 */
$q = trim($_GET['q'] ?? '');
$filterLeagueId = (int)($_GET['league_id'] ?? 0);
$filterTeamId = (int)($_GET['team_id'] ?? 0);
$filterStatus = $_GET['status'] ?? '';

/**
 * Dropdowns
 */
$leagues = $pdo->query("
  SELECT league_id, season, sport, name
  FROM leagues
  ORDER BY season DESC, sport ASC, name ASC
")->fetchAll();

$teams = $pdo->query("
  SELECT t.team_id, t.name AS team_name, l.league_id, l.name AS league_name, l.season, l.sport
  FROM teams t
  JOIN leagues l ON l.league_id = t.league_id
  ORDER BY l.season DESC, l.sport ASC, l.name ASC, t.name ASC
")->fetchAll();

/**
 * List matches
 */
$sql = "
  SELECT
    m.match_id,
    m.match_datetime,
    m.location,
    m.status,
    m.home_score,
    m.away_score,
    l.league_id,
    l.name AS league_name,
    l.season,
    l.sport,
    ht.team_id AS home_team_id,
    ht.name AS home_team_name,
    at.team_id AS away_team_id,
    at.name AS away_team_name
  FROM matches m
  JOIN leagues l ON l.league_id = m.league_id
  JOIN teams ht ON ht.team_id = m.home_team_id
  JOIN teams at ON at.team_id = m.away_team_id
  WHERE 1=1
";

$params = [];

if ($q !== '') {
  $sql .= " AND (
    ht.name LIKE :q OR at.name LIKE :q OR l.name LIKE :q OR m.location LIKE :q
  )";
  $params[':q'] = "%{$q}%";
}

if ($filterLeagueId > 0) {
  $sql .= " AND l.league_id = :league_id";
  $params[':league_id'] = $filterLeagueId;
}

if ($filterTeamId > 0) {
  $sql .= " AND (:team_id IN (m.home_team_id, m.away_team_id))";
  $params[':team_id'] = $filterTeamId;
}

if (in_array($filterStatus, ['scheduled','completed','canceled'], true)) {
  $sql .= " AND m.status = :status";
  $params[':status'] = $filterStatus;
}

$sql .= " ORDER BY m.match_datetime DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matches = $stmt->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div>
      <div class="sp-card__title">Matches</div>
      <div class="sp-card__sub">Live matches schedule (home/away, date, score, status).</div>
    </div>

    <div class="sp-actions">
      <button class="sp-btn sp-btn--pill" type="button" data-dialog-open="#dlgMatchCreate">
        <i class="fa-solid fa-plus"></i>&nbsp; Add Match
      </button>
    </div>
  </div>

  <div class="sp-card__bd">

    <?php if (isset($_GET['created'])): ?>
      <div class="sp-alert sp-alert--success" style="margin-bottom:12px;">Match created successfully.</div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="sp-alert <?php echo $flash['type'] === 'error' ? 'sp-alert--danger' : 'sp-alert--success'; ?>" style="margin-bottom:12px;">
        <?php echo h($flash['msg']); ?>
      </div>
    <?php endif; ?>

    <form class="sp-filterbar" method="GET" action="admin_matches.php">
      <div class="sp-filterbar__left">
        <div class="sp-search">
          <i class="fa-solid fa-magnifying-glass icon"></i>
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search team, league, location…" />
        </div>

        <select class="sp-select" name="league_id">
          <option value="0">All leagues</option>
          <?php foreach ($leagues as $l): ?>
            <option value="<?php echo (int)$l['league_id']; ?>" <?php echo $filterLeagueId === (int)$l['league_id'] ? 'selected' : ''; ?>>
              <?php echo h($l['season'].' · '.$l['sport'].' · '.$l['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select class="sp-select" name="team_id">
          <option value="0">All teams</option>
          <?php foreach ($teams as $t): ?>
            <option value="<?php echo (int)$t['team_id']; ?>" <?php echo $filterTeamId === (int)$t['team_id'] ? 'selected' : ''; ?>>
              <?php echo h($t['team_name']); ?> (<?php echo h($t['league_name']); ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <select class="sp-select" name="status">
          <option value="">Any status</option>
          <option value="scheduled" <?php echo $filterStatus==='scheduled'?'selected':''; ?>>Scheduled</option>
          <option value="completed" <?php echo $filterStatus==='completed'?'selected':''; ?>>Completed</option>
          <option value="canceled" <?php echo $filterStatus==='canceled'?'selected':''; ?>>Canceled</option>
        </select>

        <button class="sp-btn sp-btn--ghost" type="submit"><i class="fa-solid fa-filter"></i>&nbsp; Apply</button>
        <a class="sp-btn sp-btn--ghost" href="admin_matches.php"><i class="fa-solid fa-rotate-left"></i>&nbsp; Reset</a>
      </div>
    </form>

    <div style="height:12px"></div>

    <div class="sp-table-wrap" style="max-height: 540px; border:1px solid var(--line)">
      <table id="tblMatches" class="sp-table sp-table--light">
        <thead>
          <tr>
            <th style="width:70px">ID</th>
            <th style="width:170px">Date/Time</th>
            <th>Match</th>
            <th style="width:160px">League</th>
            <th style="width:170px">Location</th>
            <th style="width:120px">Status</th>
            <th style="width:120px">Score</th>
            <th style="width:200px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($matches as $m): ?>
            <?php
              $st = $m['status'];
              $pill = ($st === 'completed') ? 'sp-pill--success' : (($st === 'scheduled') ? 'sp-pill--warning' : '');
              $score = ($m['home_score'] !== null && $m['away_score'] !== null)
                ? ((int)$m['home_score'] . ' - ' . (int)$m['away_score'])
                : '—';
            ?>
            <tr>
              <td><?php echo (int)$m['match_id']; ?></td>
              <td><?php echo h($m['match_datetime']); ?></td>
              <td>
                <strong><?php echo h($m['home_team_name']); ?></strong>
                <span class="sp-card__sub"> vs </span>
                <strong><?php echo h($m['away_team_name']); ?></strong>
              </td>
              <td><?php echo h($m['league_name']); ?></td>
              <td><?php echo $m['location'] ? h($m['location']) : '—'; ?></td>
              <td><span class="sp-pill <?php echo $pill; ?>"><?php echo h(ucfirst($st)); ?></span></td>
              <td><?php echo h($score); ?></td>
              <td>
                <div class="sp-actions">
                  <button class="sp-btn-tag primary" type="button">View</button>
                  <button class="sp-btn-tag" type="button">Edit</button>
                  <button class="sp-btn-tag danger" type="button">Cancel</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($matches)): ?>
            <tr><td colspan="8" class="sp-card__sub">No matches found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<dialog id="dlgMatchCreate" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Add Match</div>
    <div class="sp-card__sub">Select league, home & away team, set date/time and location.</div>
  </div>

  <div class="sp-dialog__bd">
    <form method="POST" action="admin_matches.php">
      <input type="hidden" name="action" value="create_match" />

      <div class="sp-form-grid">
        <div class="sp-col-12">
          <label class="sp-card__sub">League</label>
          <select class="sp-select" style="width:100%" name="league_id" required>
            <option value="">Select a league…</option>
            <?php foreach ($leagues as $l): ?>
              <option value="<?php echo (int)$l['league_id']; ?>">
                <?php echo h($l['season'].' · '.$l['sport'].' · '.$l['name']); ?> (ID <?php echo (int)$l['league_id']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Home team</label>
          <select class="sp-select" style="width:100%" name="home_team_id" required>
            <option value="">Select home team…</option>
            <?php foreach ($teams as $t): ?>
              <option value="<?php echo (int)$t['team_id']; ?>">
                <?php echo h($t['team_name']); ?> — <?php echo h($t['league_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Away team</label>
          <select class="sp-select" style="width:100%" name="away_team_id" required>
            <option value="">Select away team…</option>
            <?php foreach ($teams as $t): ?>
              <option value="<?php echo (int)$t['team_id']; ?>">
                <?php echo h($t['team_name']); ?> — <?php echo h($t['league_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Date & time</label>
          <input class="sp-input" style="width:100%" name="match_datetime" type="datetime-local" required />
          <div class="sp-card__sub" style="margin-top:6px;">Tip: browser sends as YYYY-MM-DDTHH:MM</div>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Location</label>
          <input class="sp-input" style="width:100%" name="location" placeholder="RIT Turf Field" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Status</label>
          <select class="sp-select" style="width:100%" name="status">
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>

        <div class="sp-col-3">
          <label class="sp-card__sub">Home score</label>
          <input class="sp-input" style="width:100%" name="home_score" type="number" min="0" />
        </div>

        <div class="sp-col-3">
          <label class="sp-card__sub">Away score</label>
          <input class="sp-input" style="width:100%" name="away_score" type="number" min="0" />
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Notes</label>
          <textarea class="sp-input" style="width:100%; min-height:80px" name="notes" placeholder="Referee, special notes…"></textarea>
        </div>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-close>Cancel</button>
        <button class="sp-btn sp-btn--pill" type="submit">Create Match</button>
      </div>
    </form>
  </div>
</dialog>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>