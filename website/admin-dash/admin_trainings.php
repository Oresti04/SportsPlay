<?php
include __DIR__ . '/../includes/admin_guard.php';

$pageTitle = 'Trainings';
$activeNav = 'trainings';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$flash = null;

/* CREATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_training') {

  $teamId = (int)($_POST['team_id'] ?? 0);
  $leagueId = (int)($_POST['league_id'] ?? 0);
  $dt = trim($_POST['training_datetime'] ?? '');
  $dt = str_replace('T',' ',$dt);
  if ($dt !== '') $dt .= ':00';

  $location = trim($_POST['location'] ?? '');
  $duration = ($_POST['duration_minutes'] ?? '') !== '' ? (int)$_POST['duration_minutes'] : null;
  $intensity = $_POST['intensity'] ?? 'medium';
  $status = $_POST['status'] ?? 'scheduled';
  $notes = trim($_POST['notes'] ?? '');
  $notes = $notes !== '' ? $notes : null;

  if ($teamId <= 0 || $leagueId <= 0 || $dt === '') {
    $flash = ['type'=>'error','msg'=>'Team, league and date/time required.'];
  } else {
    try {
      $stmt = $pdo->prepare("
        INSERT INTO trainings
        (team_id, league_id, training_datetime, location, duration_minutes, intensity, status, notes)
        VALUES
        (:team_id,:league_id,:dt,:location,:duration,:intensity,:status,:notes)
      ");
      $stmt->execute([
        ':team_id'=>$teamId,
        ':league_id'=>$leagueId,
        ':dt'=>$dt,
        ':location'=>$location,
        ':duration'=>$duration,
        ':intensity'=>$intensity,
        ':status'=>$status,
        ':notes'=>$notes
      ]);

      header("Location: admin_trainings.php?created=1");
      exit;
    } catch(PDOException $e){
      $flash=['type'=>'error','msg'=>$e->getMessage()];
    }
  }
}

/* DATA */
$teams = $pdo->query("
  SELECT t.team_id, t.name AS team_name, l.league_id, l.name AS league_name, l.season, l.sport
  FROM teams t
  JOIN leagues l ON l.league_id = t.league_id
  ORDER BY l.season DESC, t.name ASC
")->fetchAll();

$trainings = $pdo->query("
  SELECT tr.*, t.name AS team_name, l.name AS league_name
  FROM trainings tr
  JOIN teams t ON t.team_id = tr.team_id
  JOIN leagues l ON l.league_id = tr.league_id
  ORDER BY tr.training_datetime DESC
")->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>

<section class="sp-card">
  <div class="sp-card__hd">
    <div>
      <div class="sp-card__title">Trainings</div>
      <div class="sp-card__sub">Team training sessions schedule.</div>
    </div>

    <div class="sp-actions">
      <button class="sp-btn sp-btn--pill" data-dialog-open="#dlgTrainingCreate">
        <i class="fa-solid fa-plus"></i> Add Training
      </button>
    </div>
  </div>

  <div class="sp-card__bd">

    <?php if(isset($_GET['created'])): ?>
      <div class="sp-alert sp-alert--success">Training created.</div>
    <?php endif; ?>

    <?php if($flash): ?>
      <div class="sp-alert sp-alert--danger"><?php echo h($flash['msg']); ?></div>
    <?php endif; ?>

    <div class="sp-table-wrap">
      <table class="sp-table sp-table--light">
        <thead>
          <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Team</th>
            <th>League</th>
            <th>Location</th>
            <th>Duration</th>
            <th>Intensity</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($trainings as $tr): ?>
          <tr>
            <td><?php echo $tr['training_id']; ?></td>
            <td><?php echo h($tr['training_datetime']); ?></td>
            <td><?php echo h($tr['team_name']); ?></td>
            <td><?php echo h($tr['league_name']); ?></td>
            <td><?php echo h($tr['location']); ?></td>
            <td><?php echo $tr['duration_minutes'] ? $tr['duration_minutes'].' min' : '—'; ?></td>
            <td><?php echo ucfirst($tr['intensity']); ?></td>
            <td><?php echo ucfirst($tr['status']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<dialog id="dlgTrainingCreate" class="sp-dialog">
  <div class="sp-dialog__hd">
    <div class="sp-dialog__title">Add Training</div>
  </div>

  <div class="sp-dialog__bd">
    <form method="POST">
      <input type="hidden" name="action" value="create_training">

      <select name="league_id" class="sp-select" required>
        <option value="">Select League</option>
        <?php foreach($teams as $t): ?>
          <option value="<?php echo $t['league_id']; ?>">
            <?php echo h($t['league_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="team_id" class="sp-select" required>
        <option value="">Select Team</option>
        <?php foreach($teams as $t): ?>
          <option value="<?php echo $t['team_id']; ?>">
            <?php echo h($t['team_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <input type="datetime-local" name="training_datetime" class="sp-input" required>
      <input type="text" name="location" class="sp-input" placeholder="Location">
      <input type="number" name="duration_minutes" class="sp-input" placeholder="Duration (min)">

      <select name="intensity" class="sp-select">
        <option value="low">Low</option>
        <option value="medium" selected>Medium</option>
        <option value="high">High</option>
      </select>

      <select name="status" class="sp-select">
        <option value="scheduled">Scheduled</option>
        <option value="completed">Completed</option>
        <option value="canceled">Canceled</option>
      </select>

      <textarea name="notes" class="sp-input" placeholder="Notes"></textarea>

      <div class="sp-form-actions">
        <button class="sp-btn sp-btn--pill" type="submit">Create</button>
      </div>
    </form>
  </div>
</dialog>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>