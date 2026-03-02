<?php
$activeNav = $activeNav ?? '';
$nav = [
  ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'href' => 'player_dashboard.php'],
  ['key' => 'messages', 'label' => 'Coach Messages', 'icon' => 'fa-comments', 'href' => 'player_messages.php'],
  ['key' => 'announcements', 'label' => 'Announcements', 'icon' => 'fa-bullhorn', 'href' => 'player_announcements.php'],
  ['key' => 'payments', 'label' => 'Payments', 'icon' => 'fa-credit-card', 'href' => 'player_payments.php'],
  ['key' => 'settings', 'label' => 'Settings', 'icon' => 'fa-gear', 'href' => 'player_settings.php'],
];
?>
<aside class="sp-sidebar" aria-label="Player navigation">
  <div class="sp-brand">
    <div class="sp-brand__mark">
      <div class="sp-brand__logo">sportsplay</div>
      <div class="sp-brand__sub">Player dashboard</div>
    </div>
    <a href="player_dashboard.php" class="sp-btn sp-btn--ghost" style="text-decoration:none; color:inherit;" title="Go to dashboard"><i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="sp-sidebar__section-title">Navigation</div>
  <ul class="sp-nav">
    <?php foreach ($nav as $item): ?>
      <li><a href="<?php echo htmlspecialchars($item['href']); ?>" class="<?php echo ($activeNav === $item['key']) ? 'active' : ''; ?>"><span class="ico"><i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i></span><span><?php echo htmlspecialchars($item['label']); ?></span></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="sp-sidebar__footer"><div style="opacity:.9; font-weight:600;">SportsPlay Teams, Inc.</div><div style="margin-top:6px;">&copy; <?php echo date('Y'); ?> · v0.2</div></div>
</aside>
