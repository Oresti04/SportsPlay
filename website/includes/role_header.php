<?php
// Shared header for role dashboards (coach/parent/player)
// Expected variables:
//  - $pageTitle (string)
//  - $activeNav (string)
//  - $roleLabel (string) e.g., 'Coach'
//  - $roleSub (string) e.g., 'Coach Console'
//  - $sidebarInclude (string) file name in this /includes directory

$pageTitle = $pageTitle ?? 'Dashboard';
$activeNav = $activeNav ?? '';
$roleLabel = $roleLabel ?? 'User';
$roleSub   = $roleSub ?? 'SportsPlay';
$sidebarInclude = $sidebarInclude ?? 'role_sidebar.php';

$userName = $_SESSION['user_name'] ?? $roleLabel;
$initial = strtoupper(substr(trim((string)$userName), 0, 1));
$profileImage = null;
if (!empty($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => (int)$_SESSION['user_id']]);
        $profileImage = $stmt->fetchColumn() ?: null;
    } catch (Throwable $e) {
        $profileImage = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($pageTitle); ?> · SportsPlay</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/admin-ui.css" />
</head>
<body>
  <div class="sp-admin">
    <?php include __DIR__ . '/' . $sidebarInclude; ?>

    <div class="sp-main">
      <header class="sp-topbar">
        <div class="sp-topbar__left">
          <button class="sp-burger" type="button" data-burger aria-label="Open menu">
            <i class="fa-solid fa-bars"></i>
          </button>

          <div class="sp-breadcrumb">
            <div>
              <div class="sp-breadcrumb__title"><?php echo htmlspecialchars($pageTitle); ?></div>
              <div class="sp-role"><?php echo htmlspecialchars($roleSub); ?></div>
            </div>
          </div>
        </div>

        <div class="sp-topbar__center">
          <div class="sp-role-title" aria-label="Current role">
            <span class="label">Role</span>
            <span class="value"><?php echo htmlspecialchars($roleLabel); ?></span>
          </div>
        </div>

        <div class="sp-topbar__right">
          <div class="sp-search" aria-label="Quick search">
            <i class="fa-solid fa-magnifying-glass icon"></i>
            <input type="text" placeholder="Search team, schedule, messages…" />
          </div>

          <div class="sp-avatar" title="<?php echo htmlspecialchars((string)$userName); ?>">
            <?php if ($profileImage): ?>
              <img class="sp-avatar__img" src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile picture" />
            <?php else: ?>
              <?php echo htmlspecialchars($initial); ?>
            <?php endif; ?>
          </div>

          <form method="post" action="auth/logout.php" style="margin:0;">
            <button class="sp-btn sp-btn--pill" type="submit">Logout</button>
          </form>
        </div>
      </header>

      <main class="sp-content">
