<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Schedule';
$activeNav = 'schedule';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/**
 * Week navigation
 */
$weekOffset = (int)($_GET['w'] ?? 0); // 0 = this week, -1 = previous, +1 = next
$baseMonday = new DateTime('monday this week');
if ($weekOffset !== 0) $baseMonday->modify(($weekOffset > 0 ? '+' : '') . $weekOffset . ' week');

$weekStart = clone $baseMonday;                      // Monday
$weekEnd = (clone $baseMonday)->modify('+6 days');   // Sunday

$rangeStart = $weekStart->format('Y-m-d') . ' 00:00:00';
$rangeEnd   = $weekEnd->format('Y-m-d') . ' 23:59:59';

/**
 * Filters
 */
$leagueId = (int)($_GET['league_id'] ?? 0);
$teamId   = (int)($_GET['team_id'] ?? 0);
$show     = $_GET['show'] ?? 'both'; // both | matches | trainings
if (!in_array($show, ['both','matches','trainings'], true)) $show = 'both';

/**
 * Dropdown data
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
 * Fetch events from DB
 * Normalize fields:
 * type, dt, title, location, league_id, team_id
 */
$events = [];

try {
  if ($show === 'both' || $show === 'trainings') {
    $sqlTr = "
      SELECT
        'training' AS type,
        tr.training_id AS id,
        tr.training_datetime AS dt,
        tr.location,
        tr.league_id,
        tr.team_id,
        t.name AS team_name
      FROM trainings tr
      JOIN teams t ON t.team_id = tr.team_id
      WHERE tr.training_datetime BETWEEN :start AND :end
    ";

    $paramsTr = [':start' => $rangeStart, ':end' => $rangeEnd];

    if ($leagueId > 0) { $sqlTr .= " AND tr.league_id = :league_id"; $paramsTr[':league_id'] = $leagueId; }
    if ($teamId > 0)   { $sqlTr .= " AND tr.team_id = :team_id";     $paramsTr[':team_id'] = $teamId; }

    $sqlTr .= " ORDER BY tr.training_datetime ASC";

    $st = $pdo->prepare($sqlTr);
    $st->execute($paramsTr);

    foreach ($st->fetchAll() as $r) {
      $events[] = [
        'type' => 'training',
        'dt' => $r['dt'],
        'title' => 'Training · ' . $r['team_name'],
        'location' => $r['location'],
        'league_id' => (int)$r['league_id'],
        'team_id' => (int)$r['team_id'],
      ];
    }
  }

  if ($show === 'both' || $show === 'matches') {
    $sqlM = "
      SELECT
        'match' AS type,
        m.match_id AS id,
        m.match_datetime AS dt,
        m.location,
        m.league_id,
        m.home_team_id,
        m.away_team_id,
        ht.name AS home_team,
        at.name AS away_team
      FROM matches m
      JOIN teams ht ON ht.team_id = m.home_team_id
      JOIN teams at ON at.team_id = m.away_team_id
      WHERE m.match_datetime BETWEEN :start AND :end
    ";

    $paramsM = [':start' => $rangeStart, ':end' => $rangeEnd];

    if ($leagueId > 0) { $sqlM .= " AND m.league_id = :league_id"; $paramsM[':league_id'] = $leagueId; }
    if ($teamId > 0)   { $sqlM .= " AND (:team_id IN (m.home_team_id, m.away_team_id))"; $paramsM[':team_id'] = $teamId; }

    $sqlM .= " ORDER BY m.match_datetime ASC";

    $sm = $pdo->prepare($sqlM);
    $sm->execute($paramsM);

    foreach ($sm->fetchAll() as $r) {
      // team_id for filtering already handled; for display we can set "home"
      $events[] = [
        'type' => 'match',
        'dt' => $r['dt'],
        'title' => 'Match · ' . $r['home_team'] . ' vs ' . $r['away_team'],
        'location' => $r['location'],
        'league_id' => (int)$r['league_id'],
        'team_id' => (int)$r['home_team_id'],
      ];
    }
  }

  // sort combined
  usort($events, fn($a,$b) => strcmp($a['dt'], $b['dt']));
} catch (PDOException $e) {
  // keep page alive even if db hiccups
  $events = [];
  $dbError = $e->getMessage();
}

/**
 * Group events by day (Mon..Sun)
 */
$days = [];
for ($i=0; $i<7; $i++) {
  $d = (clone $weekStart)->modify("+{$i} days");
  $key = $d->format('Y-m-d');
  $days[$key] = [
    'label' => $d->format('D'), // Mon, Tue...
    'events' => []
  ];
}

foreach ($events as $ev) {
  $dayKey = date('Y-m-d', strtotime($ev['dt']));
  if (isset($days[$dayKey])) $days[$dayKey]['events'][] = $ev;
}

/**
 * Range pill label (e.g., Mar 2 – Mar 8)
 */
$rangeLabel = $weekStart->format('M j') . ' – ' . $weekEnd->format('M j');

include __DIR__ . '/../includes/admin_header.php';
?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div>
      <div class="sp-card__title">Schedule</div>
      <div class="sp-card__sub">Unified view of matches + trainings. Use filters to reduce noise.</div>
      <?php if (!empty($dbError ?? null)): ?>
        <div class="sp-card__sub" style="color:#b91c1c;margin-top:6px;">DB error: <?php echo h($dbError); ?></div>
      <?php endif; ?>
    </div>

    <div class="sp-actions">
      <select class="sp-select" style="height:38px" disabled>
        <option selected>Week view</option>
        <option>Day view</option>
        <option>Month view</option>
      </select>

      <button class="sp-btn sp-btn--ghost" type="button" onclick="document.getElementById('dlgFilters').showModal()">
        <i class="fa-solid fa-filter"></i>&nbsp; Filters
      </button>

      <button class="sp-btn sp-btn--pill" type="button" onclick="document.getElementById('dlgAddEvent').showModal()">
        <i class="fa-solid fa-plus"></i>&nbsp; Add Event
      </button>
    </div>
  </div>

  <div class="sp-card__bd">
    <div class="sp-filterbar">
      <div class="sp-filterbar__left">
        <form method="GET" action="admin_schedule.php" style="display:flex;gap:10px;align-items:center;">
          <input type="hidden" name="w" value="<?php echo (int)$weekOffset; ?>"/>

          <select class="sp-select" name="league_id" onchange="this.form.submit()">
            <option value="0">All leagues</option>
            <?php foreach ($leagues as $l): ?>
              <option value="<?php echo (int)$l['league_id']; ?>" <?php echo $leagueId===(int)$l['league_id']?'selected':''; ?>>
                <?php echo h($l['season'].' · '.$l['sport'].' · '.$l['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select class="sp-select" name="team_id" onchange="this.form.submit()">
            <option value="0">All teams</option>
            <?php foreach ($teams as $t): ?>
              <option value="<?php echo (int)$t['team_id']; ?>" <?php echo $teamId===(int)$t['team_id']?'selected':''; ?>>
                <?php echo h($t['team_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select class="sp-select" name="show" onchange="this.form.submit()">
            <option value="both" <?php echo $show==='both'?'selected':''; ?>>Show: Matches + Trainings</option>
            <option value="matches" <?php echo $show==='matches'?'selected':''; ?>>Show: Matches only</option>
            <option value="trainings" <?php echo $show==='trainings'?'selected':''; ?>>Show: Trainings only</option>
          </select>
        </form>
      </div>

      <div class="sp-filterbar__right">
        <?php
          $q = $_GET;
          $q['w'] = $weekOffset - 1;
          $prevUrl = 'admin_schedule.php?' . http_build_query($q);

          $q['w'] = $weekOffset + 1;
          $nextUrl = 'admin_schedule.php?' . http_build_query($q);
        ?>
        <a class="sp-btn sp-btn--ghost" href="<?php echo h($prevUrl); ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <span class="sp-pill"><?php echo h($rangeLabel); ?></span>
        <a class="sp-btn sp-btn--ghost" href="<?php echo h($nextUrl); ?>"><i class="fa-solid fa-chevron-right"></i></a>
      </div>
    </div>

    <div style="height:14px"></div>

    <div class="sp-calendar" aria-label="Weekly calendar (DB live)">
      <div></div>

      <?php foreach ($days as $key => $d): ?>
        <div class="day-hd"><?php echo h($d['label']); ?></div>
      <?php endforeach; ?>

      <!-- Left time column (kept for UI vibe) -->
      <div class="time-col">
        <div class="time-slot">16:00</div>
        <div class="time-slot">18:00</div>
        <div class="time-slot">20:00</div>
      </div>

      <?php foreach ($days as $dayKey => $d): ?>
        <div class="day">
          <?php foreach ($d['events'] as $ev): ?>
            <?php
              $time = date('H:i', strtotime($ev['dt']));
              $cls = $ev['type'] === 'match' ? 'sp-event sp-event--match' : 'sp-event sp-event--training';
              $loc = $ev['location'] ? $ev['location'] : '—';
            ?>
            <div class="<?php echo h($cls); ?>">
              <div class="t"><?php echo h($ev['title']); ?></div>
              <div class="m"><?php echo h($time . ' · ' . $loc); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="sp-alert" style="margin-top:12px">
      <strong>Admin autonomy idea:</strong> let admin set "venue blackout dates" + weather alerts so events auto-suggest rescheduling.
    </div>
  </div>
</section>

<!-- Filters modal (optional / keeps UI consistent) -->
<dialog id="dlgFilters" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Filters</div>
    <div class="sp-card__sub">Use the dropdowns in the toolbar to filter schedule.</div>
  </div>
  <div class="sp-dialog__bd">
    <div class="sp-alert">Filters are already active in the top bar (league/team/show).</div>
    <div class="sp-form-actions">
      <button class="sp-btn sp-btn--pill" type="button" onclick="document.getElementById('dlgFilters').close()">Close</button>
    </div>
  </div>
</dialog>

<!-- Add Event modal (simple links to existing pages) -->
<dialog id="dlgAddEvent" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Add Event</div>
    <div class="sp-card__sub">Choose what you want to create.</div>
  </div>
  <div class="sp-dialog__bd">
    <div class="sp-form-grid">
      <div class="sp-col-12">
        <a class="sp-btn sp-btn--pill" href="admin_trainings.php" style="width:100%;text-align:center;">
          <i class="fa-solid fa-dumbbell"></i>&nbsp; Add Training
        </a>
      </div>
      <div class="sp-col-12">
        <a class="sp-btn sp-btn--pill" href="admin_matches.php" style="width:100%;text-align:center;">
          <i class="fa-solid fa-futbol"></i>&nbsp; Add Match
        </a>
      </div>
    </div>
    <div class="sp-form-actions">
      <button class="sp-btn sp-btn--ghost" type="button" onclick="document.getElementById('dlgAddEvent').close()">Cancel</button>
    </div>
  </div>
</dialog>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>