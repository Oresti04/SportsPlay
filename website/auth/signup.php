<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/role_helpers.php";
require_once __DIR__ . "/../includes/db_queries.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $role      = strtolower(trim($_POST['account_role'] ?? 'parent'));

    if (!in_array($role, ['coach','parent','player'], true)) {
        $role = 'parent';
    }

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $parts = preg_split('/\s+/', $full_name, 2);
            $first_name = $parts[0];
            $last_name  = $parts[1] ?? '';
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $metadata = json_encode([
                'app_role' => $role,
                'signup_source' => 'sportsplay_auth',
            ], JSON_UNESCAPED_SLASHES);

            $isCoach = ($role === 'coach') ? 1 : 0;

            $insert = $pdo->prepare(
                'INSERT INTO users (email, password_hash, first_name, last_name, phone, ip_address, metadata, is_coach)
                 VALUES (:email, :password_hash, :first_name, :last_name, :phone, :ip, :metadata, :is_coach)'
            );
            $insert->execute([
                'email'         => $email,
                'password_hash' => $password_hash,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'phone'         => $phone !== '' ? $phone : null,
                'ip'            => $_SERVER['REMOTE_ADDR'] ?? null,
                'metadata'      => $metadata,
                'is_coach'      => $isCoach,
            ]);

            $newUserId = (int)$pdo->lastInsertId();

            // Create lightweight profile row for parent accounts
            if ($role === 'parent') {
                try {
                    $parentIns = $pdo->prepare('INSERT INTO parents (user_id, phone) VALUES (:uid, :phone)');
                    $parentIns->execute([
                        'uid' => $newUserId,
                        'phone' => $phone !== '' ? $phone : null,
                    ]);
                } catch (Throwable $e) {
                    // Ignore if parents table not present yet
                }
            }

            session_regenerate_id(true);
            $_SESSION['user_id']   = $newUserId;
            $_SESSION['user_name'] = $first_name;
            $_SESSION['is_admin']  = 0;
            $_SESSION['is_coach']  = $isCoach;
            $_SESSION['app_role']  = $role;

            header('Location: ../dashboard.php');
            exit;
        }
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$google_login_uri = $scheme . '://' . $host . $base . '/google_login.php';
$selectedRole = strtolower(trim($_POST['account_role'] ?? 'parent'));
if (!in_array($selectedRole, ['coach','parent','player'], true)) { $selectedRole = 'parent'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sportsplay - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-left auth-left--signup">
        <div class="auth-left-inner">
            <div class="logo"><span>Sportsplay</span></div>

            <h1 class="title"><span class="red">Get</span> Started</h1>
            <p class="subtitle">Welcome to SportsPlay &middot; Let's create your account!</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="signup.php" class="auth-form" novalidate>
                <div class="form-group">
                    <span class="form-icon"><i class="fa-regular fa-user"></i></span>
                    <input type="text" name="full_name" placeholder="Enter Full Name"
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <span class="form-icon"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" name="email" placeholder="Enter Email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="role-picker">
                    <label class="role-picker__label">Create account as</label>
                    <input type="hidden" name="account_role" id="account_role" value="<?php echo htmlspecialchars($selectedRole); ?>">
                    <div class="role-picker__row" role="radiogroup" aria-label="Account role">
                        <button type="button" class="role-btn <?php echo $selectedRole==='coach' ? 'active' : ''; ?>" data-role="coach"><i class="fa-solid fa-clipboard-list"></i> Coach</button>
                        <button type="button" class="role-btn <?php echo $selectedRole==='parent' ? 'active' : ''; ?>" data-role="parent"><i class="fa-solid fa-users"></i> Parent</button>
                        <button type="button" class="role-btn <?php echo $selectedRole==='player' ? 'active' : ''; ?>" data-role="player"><i class="fa-solid fa-futbol"></i> Player</button>
                    </div>
                </div>

                <div class="form-group has-toggle">
                    <span class="form-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="toggle-password" data-toggle-target="password" aria-label="Toggle password"><i class="fa-regular fa-eye-slash"></i></span>
                </div>

                <div class="form-group has-toggle">
                    <span class="form-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <span class="toggle-password" data-toggle-target="confirm_password" aria-label="Toggle password"><i class="fa-regular fa-eye-slash"></i></span>
                </div>

                <button type="submit" class="btn-primary">Sign Up</button>
            </form>

            <div class="or-divider"><span>or</span></div>

            <div class="google-wrap" aria-hidden="true">
                <div id="g_id_onload"
                     data-client_id="<?php echo htmlspecialchars($google_client_id); ?>"
                     data-login_uri="<?php echo htmlspecialchars($google_login_uri); ?>"
                     data-auto_prompt="false"></div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="signup_with"
                     data-shape="pill"
                     data-logo_alignment="left"></div>
            </div>

            <div class="social-row">
                <button type="button" class="social-btn google" id="googleSignUpBtn"><i class="fa-brands fa-google sicon"></i> <span>Google</span></button>
                <button type="button" class="social-btn"><i class="fa-brands fa-facebook-f sicon"></i> <span>Facebook</span></button>
                <button type="button" class="social-btn apple"><i class="fa-brands fa-apple sicon"></i> <span>Apple</span></button>
            </div>

            <p class="small-text">Have an account? <a href="login.php">Sign In</a></p>
        </div>
    </div>

    <div class="auth-right"></div>
</div>

<script>
(function(){
  function syncFocus(group, on){ if(group) group.classList.toggle('is-focused', on); }
  document.querySelectorAll('.form-group input, .form-group select').forEach(el => {
    el.addEventListener('focus', () => syncFocus(el.closest('.form-group'), true));
    el.addEventListener('blur',  () => syncFocus(el.closest('.form-group'), false));
  });

  document.querySelectorAll('[data-toggle-target]').forEach(btn => {
    btn.addEventListener('click', function(){
      const target = document.getElementById(this.dataset.toggleTarget);
      if(!target) return;
      const icon = this.querySelector('i');
      const isPassword = target.type === 'password';
      target.type = isPassword ? 'text' : 'password';
      if(icon){ icon.className = isPassword ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash'; }
    });
  });

  const roleInput = document.getElementById('account_role');
  document.querySelectorAll('.role-btn[data-role]').forEach(btn => {
    btn.addEventListener('click', function(){
      const role = this.dataset.role;
      if (roleInput) roleInput.value = role;
      document.querySelectorAll('.role-btn[data-role]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
    });
  });

  const googleBtn = document.getElementById('googleSignUpBtn');
  if (googleBtn) {
    googleBtn.addEventListener('click', function(){
      const interval = setInterval(() => {
        const lateBtn = document.querySelector('.g_id_signin div[role="button"]');
        if (lateBtn) {
          clearInterval(interval);
          lateBtn.click();
        }
      }, 300);
      setTimeout(() => clearInterval(interval), 4000);
    });
  }
})();
</script>
</body>
</html>
