<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['player']);
$userId = (int)$_SESSION['user_id'];
$player = sp_player_data($pdo, $userId);
$roleLabel='Player'; $roleSub='Player Console'; $sidebarInclude='player_sidebar.php';
$pageTitle='Payments'; $activeNav='payments';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Payment History</div><div class="sp-card__sub">Your fees and billing</div></div>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($player['payments'])): ?>
      <p class="sp-muted">No payment records found.</p>
    <?php else: ?>
      <div class="sp-table-wrap sp-table-wrap--light">
        <table class="sp-table sp-table--light">
          <thead><tr><th>Item</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($player['payments'] as $p): ?>
              <tr>
                <td style="font-weight:700;"><?php echo htmlspecialchars($p['item']); ?></td>
                <td><?php echo htmlspecialchars($p['amount']); ?></td>
                <td><span class="sp-pill <?php echo strtolower($p['status'])==='paid'?'sp-pill--success':(strtolower($p['status'])==='unpaid'?'sp-pill--danger':'sp-pill--warning'); ?>"><?php echo htmlspecialchars($p['status']); ?></span></td>
                <td><?php echo htmlspecialchars($p['date']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>
