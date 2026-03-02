<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['player']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$player = $demo['player'];
$roleLabel='Player'; $roleSub='Player Console'; $sidebarInclude='player_sidebar.php';
$pageTitle='Player Payments'; $activeNav='payments';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Payments</div><div class="sp-card__sub">Season fees and kit status</div></div>
      <div class="sp-actions">
        <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-exclamation"></i> Uniform unpaid</span>
        <button class="sp-btn sp-btn--primary" type="button"><i class="fa-solid fa-credit-card"></i> Pay Uniform (Demo)</button>
      </div>
    </div>
    <div class="sp-card__bd" style="overflow:auto;">
      <table class="sp-table sp-table--light" style="width:100%; min-width:680px;">
        <thead><tr><th>Item</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($player['payments'] as $p): ?>
            <tr>
              <td style="font-weight:800;"><?php echo htmlspecialchars($p['item']); ?></td>
              <td><?php echo htmlspecialchars($p['amount']); ?></td>
              <td><?php if($p['status']==='Paid'): ?><span class="sp-pill sp-pill--success">Paid</span><?php else: ?><span class="sp-pill sp-pill--danger"><?php echo htmlspecialchars($p['status']); ?></span><?php endif; ?></td>
              <td><?php echo htmlspecialchars($p['date']); ?></td>
              <td><?php if($p['status']==='Paid'): ?><button class="sp-btn sp-btn--ghost" type="button">Receipt</button><?php else: ?><button class="sp-btn sp-btn--accent" type="button">Pay Now</button><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="sp-sep"></div>
      <div class="sp-grid" style="grid-template-columns:1fr 1fr; gap:16px;">
        <div class="sp-soft-panel">
          <h4 style="margin:0 0 10px;">Saved Payment Method</h4>
          <div class="sp-credit-card-ui compact">
            <div class="chip"></div><div class="number">•••• •••• •••• 4242</div><div class="row"><span>Parent Card</span><span>08/28</span></div>
          </div>
          <div class="sp-actions" style="margin-top:12px;"><a class="sp-btn sp-btn--ghost" href="player_settings.php"><i class="fa-solid fa-gear"></i> Manage in Settings</a></div>
        </div>
        <div class="sp-soft-panel">
          <h4 style="margin:0 0 10px;">Payment Help</h4>
          <ul class="sp-bullets" style="margin:0;">
            <li>Use parent account for billing changes.</li>
            <li>Coach can answer fee-related questions in private chat.</li>
            <li>Receipts and invoices will be backend-connected later.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd"><div><div class="sp-card__title">Progress Snapshot</div><div class="sp-card__sub">Why this matters</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-statline"><span>Matches</span><strong><?php echo (int)$player['stats']['matches']; ?></strong></div>
      <div class="sp-statline"><span>Attendance</span><strong><?php echo htmlspecialchars($player['stats']['attendance']); ?></strong></div>
      <div class="sp-statline"><span>Coach Messages</span><strong><?php echo count($player['messages']); ?> unread tips</strong></div>
      <div class="sp-sep"></div>
      <a class="sp-btn sp-btn--ghost" href="player_messages.php"><i class="fa-solid fa-comments"></i> Ask Coach</a>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>