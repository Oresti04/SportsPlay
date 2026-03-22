<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/role_helpers.php";
require_once __DIR__ . "/../includes/db_queries.php";

$errors = [];

if (!empty($_SESSION['oauth_error'])) {
    $errors[] = $_SESSION['oauth_error'];
    unset($_SESSION['oauth_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $errors[] = 'Email/username and password are required.';
    } else {
        $user = null;
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare('
                SELECT user_id, first_name, password_hash, is_active, is_admin, is_coach, metadata
                FROM users
                WHERE email = :identifier_email
                LIMIT 1
            ');
            $stmt->execute([
                'identifier_email' => $identifier,
            ]);
            $user = $stmt->fetch();
        } else {
            // Username login path (mapped to first_name). Require uniqueness to avoid wrong-account logins.
            $stmt = $pdo->prepare('
                SELECT user_id, first_name, password_hash, is_active, is_admin, is_coach, metadata
                FROM users
                WHERE first_name = :identifier_name
                ORDER BY user_id ASC
                LIMIT 2
            ');
            $stmt->execute([
                'identifier_name' => $identifier,
            ]);
            $rows = $stmt->fetchAll();
            if (count($rows) > 1) {
                $errors[] = 'Username is not unique. Please log in with email.';
            } else {
                $user = $rows[0] ?? null;
            }
        }

        if (empty($errors) && (!$user || !password_verify($password, $user['password_hash']))) {
            $errors[] = 'Invalid email/username or password.';
        } elseif (empty($errors) && (int)$user['is_active'] !== 1) {
            $errors[] = 'This account is disabled.';
        } elseif (empty($errors)) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['is_admin']  = (int)$user['is_admin'];
            $_SESSION['is_coach']  = (int)$user['is_coach'];

            // Detect role from DB tables first, fallback to metadata
            $metaRole = sportsplay_extract_app_role_from_metadata($user['metadata'] ?? null);
            $detectedRole = sp_detect_role($pdo, (int)$user['user_id'], $metaRole);
            $_SESSION['app_role'] = $detectedRole;

            // Also set is_coach if detected from team_coaches table
            if ($detectedRole === 'coach') {
                $_SESSION['is_coach'] = 1;
            }

            if (!empty($_SESSION['is_admin'])) {
                header('Location: ../admin-dash/admin_dashboard.php');
            } else {
                header('Location: ../dashboard.php');
            }
            exit;
        }
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$google_login_uri = $scheme . '://' . $host . $base . '/google_login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sportsplay - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-left">
        <div class="auth-left-inner">
            <div class="logo"><span>Sportsplay</span></div>

            <h1 class="title"><span class="red">Welcome</span> Back</h1>
            <p class="subtitle">Enter your credentials to sign in</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" class="auth-form" novalidate>
                <div class="form-group">
                    <span class="form-icon"><i class="fa-regular fa-envelope"></i></span>
                    <input type="text" name="email" placeholder="Enter Email or Username"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group has-toggle">
                    <span class="form-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="login_password" name="password" placeholder="Password" required>
                    <span class="toggle-password" data-toggle-target="login_password" aria-label="Toggle password"><i class="fa-regular fa-eye-slash"></i></span>
                </div>

                <button type="submit" class="btn-primary">Sign In</button>
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
                     data-text="signin_with"
                     data-shape="pill"
                     data-logo_alignment="left"></div>
            </div>

            <div class="social-row">
                <button type="button" class="social-btn google" id="googleSignInBtn"><i class="fa-brands fa-google sicon"></i> <span>Google</span></button>
                <button type="button" class="social-btn"><i class="fa-brands fa-facebook-f sicon"></i> <span>Facebook</span></button>
                <button type="button" class="social-btn apple"><i class="fa-brands fa-apple sicon"></i> <span>Apple</span></button>
            </div>

            <p class="small-text">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </p>
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
      if(icon){
        icon.className = isPassword ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
      }
    });
  });

  const googleBtn = document.getElementById('googleSignInBtn');
  if (googleBtn) {
    googleBtn.addEventListener('click', function(){
      const gisBtn = document.querySelector('.g_id_signin div[role="button"], .g_id_signin iframe');
      if (gisBtn && typeof gisBtn.click === 'function') {
        gisBtn.click();
        return;
      }
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
