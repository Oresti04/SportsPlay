<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Parents';
$activeNav = 'parents';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$flash = null;

/**
 * CREATE parent + link players
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_parent') {
  $userId = (int)($_POST['user_id'] ?? 0);
  $phone = trim($_POST['phone'] ?? '');
  $phone = $phone !== '' ? $phone : null;

  $preferred = $_POST['preferred_contact'] ?? 'email';
  if (!in_array($preferred, ['email','sms','phone'], true)) $preferred = 'email';

  $balance = trim($_POST['balance'] ?? '');
  $balanceCents = $balance !== '' ? (int) round(((float)$balance) * 100) : 0;

  $isActive = isset($_POST['is_active']) ? 1 : 0;

  $playerIds = $_POST['player_ids'] ?? [];
  if (!is_array($playerIds)) $playerIds = [];

  if ($userId <= 0) {
    $flash = ['type' => 'error', 'msg' => 'User (account) is required.'];
  } else {
    try {
      $pdo->beginTransaction();

      // prevent duplicate parent profile
      $chk = $pdo->prepare("SELECT parent_id FROM parents WHERE user_id = :uid LIMIT 1");
      $chk->execute([':uid' => $userId]);
      if ($chk->fetch()) {
        $pdo->rollBack();
        $flash = ['type' => 'error', 'msg' => 'This user already has a parent profile.'];
      } else {
        $ins = $pdo->prepare("
          INSERT INTO parents (user_id, phone, preferred_contact, balance_cents, is_active)
          VALUES (:user_id, :phone, :preferred_contact, :balance_cents, :is_active)
        ");
        $ins->execute([
          ':user_id' => $userId,
          ':phone' => $phone,
          ':preferred_contact' => $preferred,
          ':balance_cents' => $balanceCents,
          ':is_active' => $isActive,
        ]);

        $parentId = (int)$pdo->lastInsertId();

        // link selected players (optional)
        if (!empty($playerIds)) {
          $link = $pdo->prepare("
            INSERT IGNORE INTO parent_players (parent_id, player_id, relation)
            VALUES (:parent_id, :player_id, :relation)
          ");

          $relation = $_POST['relation'] ?? 'parent';
          if (!in_array($relation, ['parent','guardian','other'], true)) $relation = 'parent';

          foreach ($playerIds as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) {
              $link->execute([
                ':parent_id' => $parentId,
                ':player_id' => $pid,
                ':relation' => $relation,
              ]);
            }
          }
        }

        $pdo->commit();
        header("Location: admin_parents.php?created=1");
        exit;
      }
    } catch (PDOException $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $flash = ['type' => 'error', 'msg' => 'DB error: ' . $e->getMessage()];
    }
  }
}

/**
 * ASSIGN existing parent -> player(s)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign_parent_player') {
  $parentId = (int)($_POST['parent_id'] ?? 0);
  $playerIds = $_POST['player_ids'] ?? [];
  if (!is_array($playerIds)) $playerIds = [];

  $relation = $_POST['relation'] ?? 'parent';
  if (!in_array($relation, ['parent','guardian','other'], true)) $relation = 'parent';

  if ($parentId <= 0 || empty($playerIds)) {
    $flash = ['type' => 'error', 'msg' => 'Parent and at least one player are required.'];
  } else {
    try {
      $chk = $pdo->prepare("SELECT parent_id FROM parents WHERE parent_id = :pid LIMIT 1");
      $chk->execute([':pid' => $parentId]);
      if (!$chk->fetch()) {
        $flash = ['type' => 'error', 'msg' => 'Parent record not found.'];
      } else {
        $ins = $pdo->prepare("
          INSERT IGNORE INTO parent_players (parent_id, player_id, relation)
          VALUES (:parent_id, :player_id, :relation)
        ");

        $added = 0;
        foreach ($playerIds as $pid) {
          $pid = (int)$pid;
          if ($pid <= 0) continue;
          $ins->execute([
            ':parent_id' => $parentId,
            ':player_id' => $pid,
            ':relation' => $relation,
          ]);
          $added += (int)$ins->rowCount();
        }

        if ($added > 0) {
          header("Location: admin_parents.php?assigned=1");
          exit;
        }
        $flash = ['type' => 'error', 'msg' => 'Selected link(s) already exist or could not be added.'];
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
$status = $_GET['status'] ?? '';   // active / disabled / ''
$balance = $_GET['balance'] ?? ''; // overdue / paid / ''

/**
 * Users that don't already have parent profile
 */
$availableUsers = $pdo->query("
  SELECT u.user_id, u.email, u.first_name, u.last_name
  FROM users u
  LEFT JOIN parents p ON p.user_id = u.user_id
  WHERE p.user_id IS NULL
  ORDER BY u.last_name ASC, u.first_name ASC
  LIMIT 500
")->fetchAll();

/**
 * Players for multiselect
 */
$allPlayers = $pdo->query("
  SELECT p.player_id,
         CONCAT(u.first_name,' ',u.last_name) AS player_name,
         t.name AS team_name
  FROM players p
  JOIN users u ON u.user_id = p.user_id
  JOIN teams t ON t.team_id = p.team_id
  ORDER BY u.last_name ASC, u.first_name ASC
")->fetchAll();

/**
 * LIST parents (JOIN + players count)
 */
$sql = "
  SELECT
    pr.parent_id,
    pr.phone,
    pr.preferred_contact,
    pr.balance_cents,
    pr.is_active,
    u.user_id,
    u.email,
    u.first_name,
    u.last_name,
    COUNT(pp.player_id) AS players_count
  FROM parents pr
  JOIN users u ON u.user_id = pr.user_id
  LEFT JOIN parent_players pp ON pp.parent_id = pr.parent_id
  WHERE 1=1
";

$params = [];

if ($q !== '') {
  $sql .= " AND (u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q OR pr.phone LIKE :q)";
  $params[':q'] = "%{$q}%";
}

if ($status === 'active') {
  $sql .= " AND pr.is_active = 1";
} elseif ($status === 'disabled') {
  $sql .= " AND pr.is_active = 0";
}

if ($balance === 'paid') {
  $sql .= " AND pr.balance_cents <= 0";
} elseif ($balance === 'overdue') {
  $sql .= " AND pr.balance_cents > 0";
}

$sql .= " GROUP BY pr.parent_id
          ORDER BY pr.is_active DESC, u.last_name ASC, u.first_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$parents = $stmt->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div>
      <div class="sp-card__title">Parents</div>
      <div class="sp-card__sub">Manage parent profiles, child links, contact and status.</div>
    </div>

    <div class="sp-actions">
      <button class="sp-btn sp-btn--pill" type="button" data-dialog-open="#dlgParentCreate">
        <i class="fa-solid fa-plus"></i>&nbsp; Add Parent
      </button>
    </div>
  </div>

  <div class="sp-card__bd">

    <?php if (isset($_GET['created'])): ?>
      <div class="sp-alert sp-alert--success" style="margin-bottom:12px;">
        Parent created successfully.
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['assigned'])): ?>
      <div class="sp-alert sp-alert--success" style="margin-bottom:12px;">
        Player assigned to parent successfully.
      </div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="sp-alert <?php echo $flash['type'] === 'error' ? 'sp-alert--danger' : 'sp-alert--success'; ?>" style="margin-bottom:12px;">
        <?php echo h($flash['msg']); ?>
      </div>
    <?php endif; ?>

    <form class="sp-filterbar" method="GET" action="admin_parents.php">
      <div class="sp-filterbar__left">
        <div class="sp-search">
          <i class="fa-solid fa-magnifying-glass icon"></i>
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search parents by name, email, phone…" />
        </div>

        <select class="sp-select" name="balance">
          <option value="" <?php echo $balance===''?'selected':''; ?>>Any balance</option>
          <option value="overdue" <?php echo $balance==='overdue'?'selected':''; ?>>Overdue</option>
          <option value="paid" <?php echo $balance==='paid'?'selected':''; ?>>Paid</option>
        </select>

        <select class="sp-select" name="status">
          <option value="" <?php echo $status===''?'selected':''; ?>>Any status</option>
          <option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option>
          <option value="disabled" <?php echo $status==='disabled'?'selected':''; ?>>Disabled</option>
        </select>

        <button class="sp-btn sp-btn--ghost" type="submit"><i class="fa-solid fa-filter"></i>&nbsp; Apply</button>
        <a class="sp-btn sp-btn--ghost" href="admin_parents.php"><i class="fa-solid fa-rotate-left"></i>&nbsp; Reset</a>
      </div>
    </form>

    <div style="height:12px"></div>

    <div class="sp-table-wrap" style="max-height: 540px; border:1px solid var(--line)">
      <table id="tblParents" class="sp-table sp-table--light">
        <thead>
          <tr>
            <th>Parent</th>
            <th>Email</th>
            <th style="width:160px">Phone</th>
            <th style="width:90px">Players</th>
            <th style="width:120px">Balance</th>
            <th style="width:120px">Status</th>
            <th style="width:220px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($parents as $p): ?>
            <?php
              $balCents = (int)$p['balance_cents'];
              $balLabel = $balCents > 0 ? 'Overdue' : 'Paid';
              $balClass = $balCents > 0 ? 'sp-pill--warning' : 'sp-pill--success';
              $stLabel = ((int)$p['is_active'] === 1) ? 'Active' : 'Disabled';
              $stClass = ((int)$p['is_active'] === 1) ? 'sp-pill--success' : '';
            ?>
            <tr>
              <td>
                <strong><?php echo h($p['first_name'] . ' ' . $p['last_name']); ?></strong>
                <div class="sp-card__sub">Preferred: <?php echo h(strtoupper($p['preferred_contact'])); ?></div>
              </td>
              <td><?php echo h($p['email']); ?></td>
              <td><?php echo $p['phone'] ? h($p['phone']) : '—'; ?></td>
              <td><?php echo (int)$p['players_count']; ?></td>
              <td><span class="sp-pill <?php echo $balClass; ?>"><?php echo h($balLabel); ?></span></td>
              <td><span class="sp-pill <?php echo $stClass; ?>"><?php echo h($stLabel); ?></span></td>
              <td>
                <div class="sp-actions">
                  <button class="sp-btn-tag primary" type="button">Message</button>
                  <button class="sp-btn-tag" type="button">Children</button>
                  <?php if ((int)$p['players_count'] === 0): ?>
                    <button
                      class="sp-btn-tag"
                      type="button"
                      data-dialog-open="#dlgAssignChild"
                      data-parent-id="<?php echo (int)$p['parent_id']; ?>"
                      data-parent-name="<?php echo h($p['first_name'] . ' ' . $p['last_name']); ?>">
                      Assign Player
                    </button>
                  <?php endif; ?>
                  <button class="sp-btn-tag danger" type="button"><?php echo ((int)$p['is_active']===1) ? 'Disable' : 'Enable'; ?></button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($parents)): ?>
            <tr><td colspan="7" class="sp-card__sub">No parents found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="sp-alert" style="margin-top:12px">
      <strong>Next upgrade:</strong> tie balance to invoices/payments table (instead of manual balance_cents).
    </div>
  </div>
</section>

<dialog id="dlgParentCreate" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Add Parent</div>
    <div class="sp-card__sub">Create parent profile from an existing user and optionally link children (players).</div>
  </div>

  <div class="sp-dialog__bd">
    <form method="POST" action="admin_parents.php">
      <input type="hidden" name="action" value="create_parent" />

      <div class="sp-form-grid">
        <div class="sp-col-12">
          <label class="sp-card__sub">User (account)</label>
          <select class="sp-select" style="width:100%" name="user_id" required>
            <option value="">Select a user…</option>
            <?php foreach ($availableUsers as $u): ?>
              <option value="<?php echo (int)$u['user_id']; ?>">
                <?php echo h(($u['first_name'].' '.$u['last_name']) ?: $u['email']); ?> — <?php echo h($u['email']); ?> (ID <?php echo (int)$u['user_id']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($availableUsers)): ?>
            <div class="sp-card__sub" style="margin-top:6px;">No available users. Create users first.</div>
          <?php endif; ?>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Phone</label>
          <input class="sp-input" style="width:100%" name="phone" placeholder="585-..." />
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Preferred contact</label>
          <select class="sp-select" style="width:100%" name="preferred_contact">
            <option value="email">Email</option>
            <option value="sms">SMS</option>
            <option value="phone">Phone</option>
          </select>
        </div>

        <div class="sp-col-6">
          <label class="sp-card__sub">Balance (USD)</label>
          <input class="sp-input" style="width:100%" name="balance" type="number" step="0.01" min="0" placeholder="0.00" />
          <div class="sp-card__sub" style="margin-top:6px;">For now manual. Later: invoices/payments will compute it.</div>
        </div>

        <div class="sp-col-6" style="display:flex; align-items:flex-end; gap:10px;">
          <label class="sp-card__sub" style="display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="is_active" checked />
            Active
          </label>
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Link children (players) — optional</label>
          <select class="sp-select" style="width:100%; min-height:140px;" name="player_ids[]" multiple>
            <?php foreach ($allPlayers as $pl): ?>
              <option value="<?php echo (int)$pl['player_id']; ?>">
                <?php echo h($pl['player_name']); ?> — <?php echo h($pl['team_name']); ?> (Player ID <?php echo (int)$pl['player_id']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="sp-card__sub" style="margin-top:6px;">Hold CTRL (Windows) to select multiple.</div>
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Relation</label>
          <select class="sp-select" style="width:100%" name="relation">
            <option value="parent">Parent</option>
            <option value="guardian">Guardian</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-close>Cancel</button>
        <button class="sp-btn sp-btn--pill" type="submit">Create Parent</button>
      </div>
    </form>
  </div>
</dialog>

<dialog id="dlgAssignChild" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Assign Player to Parent</div>
    <div class="sp-card__sub">Link an existing parent profile to one or more players.</div>
  </div>
  <div class="sp-dialog__bd">
    <form method="POST" action="admin_parents.php">
      <input type="hidden" name="action" value="assign_parent_player" />
      <input type="hidden" name="parent_id" id="assignParentId" value="" />

      <div class="sp-form-grid">
        <div class="sp-col-12">
          <label class="sp-card__sub">Parent</label>
          <input id="assignParentName" class="sp-input" style="width:100%" value="" readonly />
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Players to link</label>
          <select class="sp-select" style="width:100%; min-height:160px;" name="player_ids[]" multiple required>
            <?php foreach ($allPlayers as $pl): ?>
              <option value="<?php echo (int)$pl['player_id']; ?>">
                <?php echo h($pl['player_name']); ?> â€” <?php echo h($pl['team_name']); ?> (Player ID <?php echo (int)$pl['player_id']; ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="sp-card__sub" style="margin-top:6px;">Hold CTRL (Windows) to select multiple.</div>
        </div>

        <div class="sp-col-12">
          <label class="sp-card__sub">Relation</label>
          <select class="sp-select" style="width:100%" name="relation">
            <option value="parent">Parent</option>
            <option value="guardian">Guardian</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--ghost" type="button" data-dialog-close>Cancel</button>
        <button class="sp-btn sp-btn--pill" type="submit">Assign Player</button>
      </div>
    </form>
  </div>
</dialog>

<script>
(function(){
  const assignButtons = document.querySelectorAll('[data-dialog-open="#dlgAssignChild"][data-parent-id]');
  const parentIdInput = document.getElementById('assignParentId');
  const parentNameInput = document.getElementById('assignParentName');
  if (!assignButtons.length || !parentIdInput || !parentNameInput) return;

  assignButtons.forEach(btn => {
    btn.addEventListener('click', function(){
      parentIdInput.value = this.getAttribute('data-parent-id') || '';
      parentNameInput.value = this.getAttribute('data-parent-name') || '';
    });
  });
})();
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
