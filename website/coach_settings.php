<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/role_helpers.php';
require_once __DIR__ . '/includes/db_queries.php';
sportsplay_require_role(['coach']);

$userId = (int)$_SESSION['user_id'];
$coach = sp_coach_data($pdo, $userId);

$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone, password_hash, profile_image FROM users WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$profile = $stmt->fetch();

$profileSuccess = '';
$profileError = '';
$securitySuccess = '';
$securityError = '';

function sp_coach_remove_profile_image_file(?string $path): void
{
    if (!$path || strpos($path, 'assets/uploads/profiles/') !== 0) {
        return;
    }
    $abs = __DIR__ . '/' . $path;
    if (is_file($abs)) {
        @unlink($abs);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'save_profile') {
        $fn = trim($_POST['first_name'] ?? '');
        $ln = trim($_POST['last_name'] ?? '');
        $ph = trim($_POST['phone'] ?? '');
        $newImagePath = $profile['profile_image'] ?? null;

        if (!empty($_POST['remove_profile_image'])) {
            sp_coach_remove_profile_image_file($newImagePath);
            $newImagePath = null;
        }

        if (!empty($_FILES['profile_image']['name'] ?? '')) {
            if ((int)($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $profileError = 'Image upload failed.';
            } else {
                $tmp = $_FILES['profile_image']['tmp_name'];
                $size = (int)($_FILES['profile_image']['size'] ?? 0);
                $info = @getimagesize($tmp);
                $mime = $info['mime'] ?? '';
                $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!$info || !isset($extMap[$mime])) {
                    $profileError = 'Allowed formats are JPG, PNG and WEBP.';
                } elseif ($size > 3 * 1024 * 1024) {
                    $profileError = 'Maximum image size is 3MB.';
                } else {
                    $uploadDir = __DIR__ . '/assets/uploads/profiles';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'profile_' . $userId . '_' . time() . '.' . $extMap[$mime];
                    $destAbs = $uploadDir . '/' . $fileName;
                    if (move_uploaded_file($tmp, $destAbs)) {
                        sp_coach_remove_profile_image_file($newImagePath);
                        $newImagePath = 'assets/uploads/profiles/' . $fileName;
                    } else {
                        $profileError = 'Could not save uploaded image.';
                    }
                }
            }
        }

        if ($fn !== '' && $profileError === '') {
            $stmtUp = $pdo->prepare("UPDATE users SET first_name=:fn, last_name=:ln, phone=:ph, profile_image=:img WHERE user_id=:uid");
            $stmtUp->execute(['fn'=>$fn,'ln'=>$ln,'ph'=>$ph?:null,'img'=>$newImagePath,'uid'=>$userId]);
            $_SESSION['user_name'] = $fn;
            $profileSuccess = 'Profile updated successfully.';
            $stmt->execute(['uid' => $userId]);
            $profile = $stmt->fetch();
        } elseif ($fn === '' && $profileError === '') {
            $profileError = 'First name is required.';
        }
    } elseif ($action === 'change_password') {
        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            $securityError = 'All password fields are required.';
        } elseif (!password_verify($current, (string)$profile['password_hash'])) {
            $securityError = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $securityError = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $securityError = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = :ph WHERE user_id = :uid")
                ->execute(['ph' => $newHash, 'uid' => $userId]);
            $securitySuccess = 'Password changed successfully.';
            $stmt->execute(['uid' => $userId]);
            $profile = $stmt->fetch();
        }
    }
}

$initial = strtoupper(substr(trim((string)($profile['first_name'] ?? 'C')), 0, 1));
$roleLabel='Coach'; $roleSub='Coach Console'; $sidebarInclude='coach_sidebar.php';
$pageTitle='Settings'; $activeNav='settings';
include __DIR__ . '/includes/role_header.php'; ?>

<section class="sp-card">
  <div class="sp-card__hd"><div><div class="sp-card__title">Profile Settings</div><div class="sp-card__sub">Photo, contact and team information</div></div></div>
  <div class="sp-card__bd">
    <?php if ($profileSuccess): ?>
      <div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:12px;padding:10px 14px;margin-bottom:14px;color:#15803d;font-size:13px;font-weight:600;">
        <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($profileSuccess); ?>
      </div>
    <?php endif; ?>
    <?php if ($profileError): ?>
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:10px 14px;margin-bottom:14px;color:#b91c1c;font-size:13px;font-weight:600;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($profileError); ?>
      </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save_profile">
      <div class="sp-settings-layout">
        <aside class="sp-profile-left sp-settings-side">
          <div class="sp-avatar-xl">
            <?php if (!empty($profile['profile_image'])): ?>
              <img src="<?php echo htmlspecialchars((string)$profile['profile_image']); ?>" alt="Profile picture" />
            <?php else: ?>
              <?php echo htmlspecialchars($initial); ?>
            <?php endif; ?>
          </div>
          <input type="file" name="profile_image" class="sp-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="width:100%;height:auto;padding:8px;">
          <div class="sp-upload-note">JPG, PNG or WEBP up to 3MB</div>
          <?php if (!empty($profile['profile_image'])): ?>
            <button class="sp-btn sp-btn--ghost" type="submit" name="remove_profile_image" value="1" style="width:100%;">Remove Photo</button>
          <?php endif; ?>
        </aside>

        <div class="sp-settings-main">
          <div class="sp-settings-grid">
            <div class="sp-settings-field"><label class="sp-form-label">First name</label><input name="first_name" class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars((string)($profile['first_name'] ?? '')); ?>" required></div>
            <div class="sp-settings-field"><label class="sp-form-label">Last name</label><input name="last_name" class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars((string)($profile['last_name'] ?? '')); ?>"></div>
            <div class="sp-settings-field"><label class="sp-form-label">Email</label><input class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars((string)($profile['email'] ?? '')); ?>" readonly></div>
            <div class="sp-settings-field"><label class="sp-form-label">Phone</label><input name="phone" class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars((string)($profile['phone'] ?? '')); ?>"></div>
            <div class="sp-settings-field"><label class="sp-form-label">Team</label><input class="sp-input" style="width:100%;" value="<?php echo htmlspecialchars((string)($coach['team']['name'] ?? '-')); ?>" readonly></div>
            <div class="sp-settings-field"><label class="sp-form-label">Role</label><input class="sp-input" style="width:100%;" value="Coach" readonly></div>
          </div>
          <div class="sp-form-actions" style="margin-top:14px;">
            <button class="sp-btn sp-btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Profile</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<section class="sp-split sp-split--gap-lg">
  <section class="sp-card">
    <div class="sp-card__hd"><div><div class="sp-card__title">Security</div><div class="sp-card__sub">Change your password</div></div></div>
    <div class="sp-card__bd">
      <?php if ($securitySuccess): ?>
        <div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:12px;padding:10px 14px;margin-bottom:14px;color:#15803d;font-size:13px;font-weight:600;">
          <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($securitySuccess); ?>
        </div>
      <?php endif; ?>
      <?php if ($securityError): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:10px 14px;margin-bottom:14px;color:#b91c1c;font-size:13px;font-weight:600;">
          <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($securityError); ?>
        </div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="change_password">
        <div class="sp-settings-grid">
          <div class="sp-settings-field"><label class="sp-form-label">Current password</label><input type="password" name="current_password" class="sp-input" style="width:100%;" required></div>
          <div class="sp-settings-field"><label class="sp-form-label">New password</label><input type="password" name="new_password" class="sp-input" style="width:100%;" required></div>
          <div class="sp-settings-field"><label class="sp-form-label">Confirm new password</label><input type="password" name="confirm_password" class="sp-input" style="width:100%;" required></div>
        </div>
        <div class="sp-form-actions" style="margin-top:14px;">
          <button class="sp-btn sp-btn--primary" type="submit"><i class="fa-solid fa-shield"></i> Update Password</button>
        </div>
      </form>
    </div>
  </section>

  <section class="sp-card">
    <div class="sp-card__hd"><div><div class="sp-card__title">Coach Preferences</div><div class="sp-card__sub">Additional controls to fill your workspace</div></div></div>
    <div class="sp-card__bd">
      <div class="sp-stack sp-stack--sm">
        <div class="sp-toggle"><div><strong>Email notifications</strong><div class="sp-muted">Get parent/player message summaries</div></div><label class="sp-switch"><input type="checkbox" checked><span class="sp-slider"></span></label></div>
        <div class="sp-toggle"><div><strong>Training reminders</strong><div class="sp-muted">Send event reminders 24h before</div></div><label class="sp-switch"><input type="checkbox" checked><span class="sp-slider"></span></label></div>
        <div class="sp-toggle"><div><strong>Auto-archive chats</strong><div class="sp-muted">Archive inactive chats after 30 days</div></div><label class="sp-switch"><input type="checkbox"><span class="sp-slider"></span></label></div>
      </div>
      <div class="sp-form-actions" style="margin-top:14px;">
        <button class="sp-btn sp-btn--ghost" type="button">Save Preferences (Preview)</button>
      </div>
    </div>
  </section>
</section>

<?php include __DIR__ . '/includes/role_footer.php'; ?>
