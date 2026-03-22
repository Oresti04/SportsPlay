<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['parent']);
$userId = (int)$_SESSION['user_id'];
$parent = sp_parent_data($pdo, $userId);
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Payments'; $activeNav='payments';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-card">
  <div class="sp-card__hd">
    <div><div class="sp-card__title">Payment History</div><div class="sp-card__sub">Fees, dues, and billing status</div></div>
  </div>
  <div class="sp-card__bd">
    <?php if (empty($parent['payments'])): ?>
      <p class="sp-muted">No payment records found. Payments will appear here once processed by the admin.</p>
    <?php else: ?>
      <div class="sp-table-wrap sp-table-wrap--light">
        <table class="sp-table sp-table--light">
          <thead><tr><th>Item</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($parent['payments'] as $p): ?>
              <tr>
                <td style="font-weight:700;"><?php echo htmlspecialchars($p['item']); ?></td>
                <td><?php echo htmlspecialchars($p['amount']); ?></td>
                <td>
                  <?php
                    $cls = 'sp-pill';
                    if (strtolower($p['status'])==='paid') $cls .= ' sp-pill--success';
                    elseif (strtolower($p['status'])==='unpaid') $cls .= ' sp-pill--danger';
                    else $cls .= ' sp-pill--warning';
                  ?>
                  <span class="<?php echo $cls; ?>"><?php echo htmlspecialchars($p['status']); ?></span>
                </td>
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
