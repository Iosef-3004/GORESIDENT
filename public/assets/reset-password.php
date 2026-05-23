<?php
require_once __DIR__ . '/../app/includes/init.php';
if (is_logged()) { header('Location: dashboard.php'); exit; }
ensure_password_resets_table($db);
$token = (string)get('token');
$tokenHash = $token !== '' ? hash('sha256', $token) : '';
$error = '';
$validReset = null;

if ($tokenHash !== '') {
    $stmt = $db->prepare('SELECT pr.id, pr.user_id, u.email, u.full_name
                          FROM password_resets pr
                          INNER JOIN users u ON u.id = pr.user_id
                          WHERE pr.token_hash = :token_hash
                            AND pr.used_at IS NULL
                            AND pr.expires_at > NOW()
                            AND u.is_active = 1
                          LIMIT 1');
    $stmt->execute(['token_hash' => $tokenHash]);
    $validReset = $stmt->fetch();
}

if (!$validReset) {
    $error = 'El enlace no es válido o ya expiró. Solicita uno nuevo.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)post('password');
    $confirm = (string)post('confirm_password');

    if (strlen($password) < 8) {
        $error = 'La contraseña debe tener mínimo 8 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->prepare('UPDATE users SET pass_hash = :pass_hash WHERE id = :id')
           ->execute(['pass_hash' => $hash, 'id' => $validReset['user_id']]);
        $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id')
           ->execute(['id' => $validReset['id']]);
        header('Location: login.php?ok=reset');
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nueva contraseña • GoResidentGo</title>
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
    <h2>Crear nueva contraseña</h2>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <?php if ($validReset): ?>
      <p class="hint">Cuenta: <span class="code"><?= e($validReset['email']) ?></span></p>
      <form method="post" class="form-grid">
        <div>
          <label>Nueva contraseña</label>
          <input class="input" type="password" name="password" minlength="8" required autofocus placeholder="Mínimo 8 caracteres">
        </div>
        <div>
          <label>Confirmar contraseña</label>
          <input class="input" type="password" name="confirm_password" minlength="8" required placeholder="Repite la contraseña">
        </div>
        <div class="actions">
          <button class="btn primary" type="submit">Guardar contraseña</button>
          <a class="btn ghost" href="login.php">Cancelar</a>
        </div>
      </form>
    <?php else: ?>
      <div class="actions"><a class="btn primary" href="forgot-password.php">Solicitar nuevo enlace</a><a class="btn ghost" href="login.php">Volver al login</a></div>
    <?php endif; ?>
  </section>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>