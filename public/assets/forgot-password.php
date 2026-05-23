<?php
require_once __DIR__ . '/../app/includes/init.php';
if (is_logged()) { header('Location: dashboard.php'); exit; }
ensure_password_resets_table($db);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)post('email')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo válido.';
    } else {
        $stmt = $db->prepare('SELECT id, email, full_name FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        $message = 'Si el correo está registrado, enviaremos las instrucciones para restablecer la contraseña.';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL')
               ->execute(['user_id' => $user['id']]);
            $db->prepare('INSERT INTO password_resets(user_id, token_hash, expires_at) VALUES(:user_id, :token_hash, :expires_at)')
               ->execute(['user_id' => $user['id'], 'token_hash' => $tokenHash, 'expires_at' => $expiresAt]);

            $resetUrl = app_base_url() . '/reset-password.php?token=' . urlencode($token);
            $name = $user['full_name'] ?: $user['email'];
            $body = "Hola {$name},\n\nRecibimos una solicitud para restablecer tu contraseña de GoResidentGo.\n\nAbre este enlace antes de 1 hora:\n{$resetUrl}\n\nSi no solicitaste este cambio, ignora este mensaje.\n";
            send_app_email($user['email'], 'Restablecer contraseña - GoResidentGo', $body);
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Recuperar contraseña • GoResidentGo</title>
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
    <h2>¿Olvidó su contraseña?</h2>
    <p class="hint">Escribe tu correo y te enviaremos un enlace para crear una nueva contraseña.</p>
    <?php if ($message): ?><div class="flash ok"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-grid">
      <div>
        <label>Correo registrado</label>
        <input class="input" type="email" name="email" placeholder="usuario@correo.com" required autofocus>
      </div>
      <div class="actions">
        <button class="btn primary" type="submit">Enviar instrucciones</button>
        <a class="btn ghost" href="login.php">Volver al login</a>
      </div>
    </form>
    <p class="footer-note"> Enviaremos las intrucciones a tu correo<span class="code">,gracias</span>.</p>
  </section>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>