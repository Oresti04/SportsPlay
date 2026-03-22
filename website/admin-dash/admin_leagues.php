<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Leagues';
$activeNav = 'leagues';

// CREATE league
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_league') {
  $season = trim($_POST['season'] ?? '');
  $sport  = trim($_POST['sport'] ?? '');
  $name   = trim($_POST['name'] ?? '');
  $ageMin = ($_POST['age_min'] ?? '') !== '' ? (int)$_POST['age_min'] : null;
  $ageMax = ($_POST['age_max'] ?? '') !== '' ? (int)$_POST['age_max'] : null;

  $feeDollars = ($_POST['fee'] ?? '') !== '' ? (float)$_POST['fee'] : 0;
  $feeCents = (int) round($feeDollars * 100);

  $rosterCap = ($_POST['roster_cap'] ?? '') !== '' ? (int)$_POST['roster_cap'] : 0;
  $regOpen   = ($_POST['reg_open'] ?? '') ?: null;
  $regClose  = ($_POST['reg_close'] ?? '') ?: null;
  $status    = $_POST['status'] ?? 'draft';

  if ($season === '' || $sport === '' || $name === '') {
    $flash = ['type' => 'error', 'msg' => 'Season, sport and league name are required.'];
  } else {
    try {
      $stmt = $pdo->prepare("
        INSERT INTO leagues (season, sport, name, age_min, age_max, fee_cents, roster_cap, reg_open, reg_close, status)
        VALUES (:season, :sport, :name, :age_min, :age_max, :fee_cents, :roster_cap, :reg_open, :reg_close, :status)
      ");
      $stmt->execute([
        ':season' => $season,
        ':sport' => $sport,
        ':name' => $name,
        ':age_min' => $ageMin,
        ':age_max' => $ageMax,
        ':fee_cents' => $feeCents,
        ':roster_cap' => $rosterCap,
        ':reg_open' => $regOpen,
        ':reg_close' => $regClose,
        ':status' => $status,
      ]);

      header("Location: admin_leagues.php?created=1");
      exit;
    } catch (PDOException $e) {
      $flash = ['type' => 'error', 'msg' => 'DB error: ' . $e->getMessage()];
    }
  }
}

// LIST leagues
$leagues = $pdo->query("
  SELECT league_id, season, sport, name, age_min, age_max, fee_cents, roster_cap, reg_open, reg_close, status
  FROM leagues
  ORDER BY season DESC, sport ASC, name ASC
")->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<div class="sp-split">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div>
        <div class="sp-card__title">Leagues & Divisions</div>
        <div class="sp-card__sub">Central configuration for seasons, sports, age groups, and fees.</div>
      </div>

      <div class="sp-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-open="#dlgLeagueCreate"><i class="fa-solid fa-plus"></i>&nbsp; Create League</button>
        <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-copy"></i>&nbsp; Duplicate Season</button>
      </div>
    </div>

    <div class="sp-card__bd">
      <div class="sp-filterbar">
        <div class="sp-filterbar__left">
          <div class="sp-search">
            <i class="fa-solid fa-magnifying-glass icon"></i>
            <input data-table-search="#tblLeagues" type="text" placeholder="Search leagues…" />
          </div>

          <select class="sp-select" data-table-filter="#tblLeagues" data-col="1">
            <option value="">All seasons</option>
            <option>2026 Spring</option>
            <option>2025 Fall</option>
          </select>

          <select class="sp-select" data-table-filter="#tblLeagues" data-col="2">
            <option value="">All sports</option>
            <option>Soccer</option>
            <option>Basketball</option>
          </select>
        </div>

        <div class="sp-filterbar__right">
          <button class="sp-btn sp-btn--ghost" type="button"><i class="fa-solid fa-file-export"></i>&nbsp; Export</button>
        </div>
      </div>

      <div style="height:12px"></div>

      <div class="sp-table-wrap" style="max-height: 520px; border:1px solid var(--line)">
        <table id="tblLeagues" class="sp-table sp-table--light">
          <thead>
            <tr>
              <th>League</th>
              <th style="width:140px">Season</th>
              <th style="width:120px">Sport</th>
              <th style="width:120px">Fee</th>
              <th style="width:140px">Reg Window</th>
              <th style="width:120px">Status</th>
              <th style="width:220px">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leagues as $l): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($l['name']); ?></strong>
                  <div class="sp-card__sub">
                    <?php
                      $ageTxt = ($l['age_min'] !== null && $l['age_max'] !== null) ? ($l['age_min'].'–'.$l['age_max']) : '—';
                      echo "Age: " . htmlspecialchars($ageTxt) . " · Roster cap: " . (int)$l['roster_cap'];
                    ?>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($l['season']); ?></td>
                <td><?php echo htmlspecialchars($l['sport']); ?></td>
                <td>$<?php echo number_format(((int)$l['fee_cents'])/100, 2); ?></td>
                <td>
                  <?php
                    $w = (($l['reg_open'] ?? '') && ($l['reg_close'] ?? ''))
                      ? htmlspecialchars($l['reg_open']) . " – " . htmlspecialchars($l['reg_close'])
                      : '—';
                    echo $w;
                  ?>
                </td>
                <td>
                  <?php
                    $st = $l['status'];
                    $pillClass = ($st === 'open') ? 'sp-pill--success' : (($st === 'draft') ? 'sp-pill--warning' : '');
                  ?>
                  <span class="sp-pill <?php echo $pillClass; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span>
                </td>
                <td>
                  <div class="sp-actions">
                    <button class="sp-btn-tag primary" type="button">Select</button>
                    <button class="sp-btn-tag" type="button">Edit</button>
                    <button class="sp-btn-tag danger" type="button">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>

            <?php if (empty($leagues)): ?>
              <tr><td colspan="7" class="sp-card__sub">No leagues yet. Create the first one.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="sp-alert" style="margin-top:12px">
        <strong>Admin autonomy idea:</strong> League = "template" for everything (fee, forms, roster cap, schedule rules). Then teams inherit defaults.
      </div>
    </div>
  </section>

  <section class="sp-card">
    <div class="sp-card__hd">
      <div>
        <div class="sp-card__title">League Settings</div>
        <div class="sp-card__sub">Edit once, apply everywhere (selected: U14 Soccer · 2026 Spring)</div>
      </div>
      <span class="sp-pill"><i class="fa-solid fa-wand-magic-sparkles"></i> Smart Defaults</span>
    </div>

    <div class="sp-card__bd">
      <div class="sp-form-grid">
        <div class="sp-col-6">
          <label class="sp-card__sub">Registration fee</label>
          <input class="sp-input" style="width:100%" value="$180" />
        </div>
        <div class="sp-col-6">
          <label class="sp-card__sub">Roster cap</label>
          <input class="sp-input" style="width:100%" value="18" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Registration opens</label>
          <input class="sp-input" style="width:100%" value="2026-02-01" />
        </div>
        <div class="sp-col-6">
          <label class="sp-card__sub">Registration closes</label>
          <input class="sp-input" style="width:100%" value="2026-03-01" />
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Required forms</label>
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <span class="sp-pill sp-pill--success"><i class="fa-solid fa-file-signature"></i> Waiver</span>
            <span class="sp-pill sp-pill--success"><i class="fa-solid fa-heart-pulse"></i> Medical</span>
            <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-id-card"></i> ID Proof</span>
          </div>
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Auto-notifications</label>
          <div class="sp-alert" style="background:#f8fafc">
            Send confirmation email, payment receipt, and season kickoff reminder 7 days before first match.
          </div>
        </div>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button">Reset</button>
        <button class="sp-btn sp-btn--pill" type="button">Save Changes (UI)</button>
      </div>

      <div class="sp-alert" style="margin-top:12px">
        <strong>Space tip:</strong> keep "edit" on the right and list on the left. Admin can click a league row and settings swap in-place.
      </div>
    </div>
  </section>
</div>

<dialog id="dlgLeagueCreate" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Create League</div>
    <div class="sp-card__sub">UI-only.</div>
  </div>
  <div class="sp-dialog__bd">
    <form method="POST" action="admin_leagues.php">
      <input type="hidden" name="action" value="create_league" />

      <div class="sp-form-grid">
        <div class="sp-col-6">
          <label class="sp-card__sub">Season</label>
          <input class="sp-input" style="width:100%" name="season" placeholder="2026 Spring" required />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Sport</label>
          <input class="sp-input" style="width:100%" name="sport" placeholder="Soccer" required />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">League</label>
          <input class="sp-input" style="width:100%" name="name" placeholder="U14" required />
        </div>

        <div class="sp-col-3">
          <label class="sp-card__sub">Age min</label>
          <input class="sp-input" style="width:100%" name="age_min" type="number" min="1" max="99" placeholder="13" />
        </div>

        <div class="sp-col-3">
          <label class="sp-card__sub">Age max</label>
          <input class="sp-input" style="width:100%" name="age_max" type="number" min="1" max="99" placeholder="14" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Fee (USD)</label>
          <input class="sp-input" style="width:100%" name="fee" type="number" step="0.01" min="0" placeholder="180.00" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Roster cap</label>
          <input class="sp-input" style="width:100%" name="roster_cap" type="number" min="0" placeholder="18" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Reg open</label>
          <input class="sp-input" style="width:100%" name="reg_open" type="date" />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Reg close</label>
          <input class="sp-input" style="width:100%" name="reg_close" type="date" />
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Status</label>
          <select class="sp-select" style="width:100%" name="status">
            <option value="draft">Draft</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-close>Cancel</button>
        <button class="sp-btn sp-btn--pill" type="submit">Create</button>
      </div>
    </form>
  </div>
</dialog>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
