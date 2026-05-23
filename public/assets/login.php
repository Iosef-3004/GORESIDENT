<?php
require_once __DIR__ . '/../app/includes/init.php';
if (is_logged()) { header('Location: dashboard.php'); exit; }
$error = '';
$ok = (string)get('ok') === 'reset' ? 'Contraseña actualizada. Ya puedes iniciar sesión.' : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)post('email')));
    $password = (string)post('password');
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    if ($user && verify_password_input($user, $password)) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name'] ?: $user['email'],
            'role' => $user['role'],
            'unit_name' => $user['unit_name'],
            'plate' => $user['plate'],
        ];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Credenciales inválidas.';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login • GoResidentGo</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <div class="brand"><span class="brand-dot"></span><span>GoResidentGo</span></div>
    <nav class="nav"><button class="btn" data-theme-toggle>🌙 Tema</button></nav>
  </div>
</header>
<main class="container auth-wrap">
  <section class="card section auth-card">
    <h2>Iniciar sesión</h2>
    <p class="hint">Bienvenido.</p>
    <?php if ($ok): ?><div class="flash ok"><?= e($ok) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-grid">
      <div>
        <label>Correo</label>
        <input class="input" type="email" name="email" placeholder="admin@goresidentgo.com" required>
      </div>
      <div>
        <label>Contraseña</label>
        <input class="input" type="password" name="password" placeholder="••••••••" required>
      </div>
      <div class="actions">
        <button class="btn primary" type="submit">Entrar al sistema</button>
        <a class="btn ghost" href="forgot-password.php">¿Olvidó su contraseña?</a>
      </div>
    </form>
    <div class="login-help">
      <div class="pill">Admin: <span class="code">admin@goresidentgo.com</span> / <span class="code">admin123</span></div>
      <div class="pill">Portería: <span class="code">porteria@goresidentgo.com</span> / <span class="code">porteria123</span></div>
      <div class="pill">Residente: <span class="code">residente@goresidentgo.com</span> / <span class="code">residente123</span></div>
    </div>
  </section>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>