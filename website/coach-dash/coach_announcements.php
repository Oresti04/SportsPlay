<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/role_helpers.php';
require_once __DIR__ . '/../includes/db_queries.php';
sportsplay_require_role(['coach']);

$userId = (int)$_SESSION['user_id'];
$userFirst = trim((string)($_SESSION['user_name'] ?? 'Coach'));
$coach = sp_coach_data($pdo, $userId);
$teamId = (int)$coach['team']['team_id'];

$success = '';
$error = '';

// Handle POST — create announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $teamId > 0) {
    $title    = trim($_POST['title'] ?? '');
    $body     = trim($_POST['body'] ?? '');
    $audience = trim($_POST['audience'] ?? 'all');
    $isPinned = (int)(($_POST['is_pinned'] ?? '0') === '1');

    if (!in_array($audience, ['all', 'players', 'parents', 'staff'], true)) {
        $audience = 'all';
    }

    if ($title === '' || $body === '') {
        $error = 'Title and message are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO announcements (team_id, author_id, title, body, audience, is_pinned)
                VALUES (:tid, :uid, :title, :body, :audience, :is_pinned)
            ");
            $stmt->execute([
                'tid'      => $teamId,
                'uid'      => $userId,
                'title'    => $title,
                'body'     => $body,
                'audience' => $audience,
                'is_pinned' => $isPinned,
            ]);
            $success = 'Announcement sent successfully!';
            // Refresh announcements
            $coach = sp_coach_data($pdo, $userId);
        } catch (Throwable $e) {
            $error = 'Could not save announcement. Make sure the announcements table exists.';
        }
    }
}

$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='/../includes/coach_sidebar.php';
$pageTitle='Announcements'; $activeNav='announcements';
include __DIR__ . '/../includes/role_header.php'; ?>

<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Create Announcement</div><div class="sp-card__sub">Broadcast to players and/or parents</div></div>
      <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-bullhorn"></i> New</span>
    </div>
    <div class="sp-card__bd">
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

      <form method="post" action="coach_announcements.php">
        <div class="sp-form-grid">
          <div class="sp-col-6">
            <label class="sp-form-label">Audience</label>
            <select name="audience" class="sp-select" style="width:100%;">
              <option value="all">All</option>
              <option value="players">Players only</option>
              <option value="parents">Parents only</option>
              <option value="staff">Staff only</option>
            </select>
          </div>
          <div class="sp-col-6">
            <label class="sp-form-label">Pin</label>
            <select name="is_pinned" class="sp-select" style="width:100%;">
              <option value="0">Normal</option>
              <option value="1">Pinned</option>
            </select>
          </div>
          <div class="sp-col-12">
            <label class="sp-form-label">Title</label>
            <input name="title" class="sp-input" style="width:100%;" type="text" placeholder="Announcement title..." />
          </div>
          <div class="sp-col-12">
            <label class="sp-form-label">Message</label>
            <textarea name="body" class="sp-input" style="width:100%; height:140px; padding:12px;" placeholder="Write announcement body..."></textarea>
          </div>
          <div class="sp-col-12">
            <div class="sp-actions">
              <button class="sp-btn sp-btn--primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Send Announcement</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Tips</div><div class="sp-card__sub">Best practices</div></div>
    </div>
    <div class="sp-card__bd">
      <ul class="sp-bullets">
        <li>Include date, arrival time, and exact field/location.</li>
        <li>Mention required gear (kits, shin guards, water).</li>
        <li>Use private messages for individual issues.</li>
        <li>Keep emergency changes at the top of the message.</li>
      </ul>
    </div>
  </section>
</section>

<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Sent Announcements</div><div class="sp-card__sub">Team broadcasts</div></div>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($coach['announcements'])): ?>
      <p class="sp-muted">No announcements sent yet. Create your first one above.</p>
    <?php else: ?>
      <div class="sp-stack">
        <?php foreach ($coach['announcements'] as $a): ?>
          <article class="sp-announcement-card">
            <div class="sp-announcement-card__hd">
              <div>
                <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                <div class="sp-announcement-card__sub"><?php echo htmlspecialchars($a['time']); ?><?php if (!empty($a['audience'])): ?> &middot; <?php echo htmlspecialchars($a['audience']); ?><?php endif; ?></div>
              </div>
              <span class="sp-pill sp-pill--success"><i class="fa-solid fa-check"></i> Sent</span>
            </div>
            <p><?php echo htmlspecialchars($a['body']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/../includes/role_footer.php'; ?>
