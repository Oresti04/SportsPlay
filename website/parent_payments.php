<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/role_demo_data.php';
sportsplay_require_role(['parent']);
$demo = sportsplay_role_demo_data();
$userFirst = $demo['meta']['first'];
$parent = $demo['parent'];
$roleLabel='Parent'; $roleSub='Parent Console'; $sidebarInclude='parent_sidebar.php';
$pageTitle='Parent Payments'; $activeNav='payments';
include __DIR__ . '/includes/role_header.php'; ?>
<section class="sp-page-grid">
  <section class="sp-card">
    <div class="sp-card__hd">
      <div><div class="sp-card__title">Payment Status</div><div class="sp-card__sub">Fees, invoices and payment actions (demo)</div></div>
      <div class="sp-actions">
        <span class="sp-pill sp-pill--warning"><i class="fa-solid fa-clock"></i> 1 pending</span>
        <button class="sp-btn sp-btn--primary" type="button"><i class="fa-solid fa-credit-card"></i> Pay Now (Demo)</button>
      </div>
    </div>
    <div class="sp-card__bd" style="overflow:auto;">
      <table class="sp-table sp-table--light" style="width:100%; min-width:700px;">
        <thead><tr><th>Item</th><th>Amount</th><th>Status</th><th>Date / Due</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($parent['payments'] as $p): ?>
            <tr>
              <td style="font-weight:800;"><?php echo htmlspecialchars($p['item']); ?></td>
              <td><?php echo htmlspecialchars($p['amount']); ?></td>
              <td>
                <?php if ($p['status']==='Paid'): ?><span class="sp-pill sp-pill--success">Paid</span>
                <?php elseif ($p['status']==='Unpaid'): ?><span class="sp-pill sp-pill--danger">Unpaid</span>
                <?php else: ?><span class="sp-pill sp-pill--warning"><?php echo htmlspecialchars($p['status']); ?></span><?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($p['date']); ?></td>
              <td>
                <?php if ($p['status']!=='Paid'): ?>
                  <button class="sp-btn sp-btn--accent" type="button">Pay</button>
                <?php else: ?>
                  <button class="sp-btn sp-btn--ghost" type="button">Receipt</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="sp-card sp-surface">
    <div class="sp-card__hd"><div><div class="sp-card__title">Saved Card</div><div class="sp-card__sub">Profile-managed payment method</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-credit-card-ui">
        <div class="chip"></div>
        <div class="number">4242 •••• •••• 4242</div>
        <div class="row"><span>Parent Maria</span><span>08/28</span></div>
      </div>
      <div class="sp-sep"></div>
      <a class="sp-btn sp-btn--ghost" href="parent_settings.php"><i class="fa-solid fa-gear"></i> Manage Card in Settings</a>
    </div>
  </section>
</section>
<?php include __DIR__ . '/includes/role_footer.php'; ?>