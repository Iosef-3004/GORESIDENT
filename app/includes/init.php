<?php
session_start();
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (is_file($autoload)) { require_once $autoload; }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
$db = (new Database())->connect();

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function money($n): string { return '$ ' . number_format((float)$n, 0, ',', '.'); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged(): bool { return !empty($_SESSION['user']); }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function is_gate(): bool { return in_array((current_user()['role'] ?? ''), ['admin','porteria'], true); }
function is_resident(): bool { return (current_user()['role'] ?? '') === 'residente'; }
function require_login(): void { if (!is_logged()) { header('Location: login.php'); exit; } }
function require_admin(): void { if (!is_admin()) { http_response_code(403); exit('Acceso denegado'); } }
function can_manage_csv(): bool { return is_admin(); }
function require_csv_admin(): void { if (!can_manage_csv()) { http_response_code(403); exit('Acceso denegado. Solo el administrador puede gestionar archivos CSV.'); } }
function set_flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function get_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function normalize_plate(?string $plate): ?string {
    $plate = strtoupper(str_replace(' ', '', trim((string)$plate)));
    return $plate === '' ? null : $plate;
}
function verify_password_input(array $user, string $input): bool {
    $stored = (string)($user['pass_hash'] ?? '');
    if ($stored !== '' && password_verify($input, $stored)) return true;
    // Compatibilidad temporal con hashes antiguos. Elimina estas líneas tras migrar todas las claves.
    if ($stored !== '' && hash('sha256', $input) === $stored) return true;
    if ($stored !== '' && hash('md5', $input) === $stored) return true;
    return false;
}
function csrf_token(): string { return $_SESSION['csrf_token'] ?? ''; }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string)post('csrf_token');
        if ($token === '' || !hash_equals(csrf_token(), $token)) {
            http_response_code(419);
            exit('Token CSRF inválido. Recarga la página e intenta nuevamente.');
        }
    }
}
function password_strength_error(string $password): ?string {
    if (strlen($password) < 10) return 'La contraseña debe tener mínimo 10 caracteres.';
    if (!preg_match('/[A-ZÁÉÍÓÚÑ]/u', $password)) return 'La contraseña debe incluir al menos una mayúscula.';
    if (!preg_match('/[0-9]/', $password)) return 'La contraseña debe incluir al menos un número.';
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return 'La contraseña debe incluir al menos un símbolo.';
    return null;
}
function app_log(string $channel, string $message, array $context = []): void {
    $dir = __DIR__ . '/../../storage/logs';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($dir . '/' . preg_replace('/[^a-z0-9_-]/i', '', $channel) . '.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}
function ensure_login_attempts_table(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        ip_address VARCHAR(64) NOT NULL,
        attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        success TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id), KEY idx_login_attempts_lookup (email, ip_address, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function login_is_blocked(PDO $db, string $email, string $ip): bool {
    ensure_login_attempts_table($db);
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE success=0 AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND (email=:email OR ip_address=:ip)");
    $stmt->execute(['email'=>$email,'ip'=>$ip]);
    return (int)$stmt->fetchColumn() >= 5;
}
function record_login_attempt(PDO $db, string $email, string $ip, bool $success): void {
    ensure_login_attempts_table($db);
    $stmt = $db->prepare('INSERT INTO login_attempts(email, ip_address, success) VALUES(:email, :ip, :success)');
    $stmt->execute(['email'=>$email,'ip'=>$ip,'success'=>$success ? 1 : 0]);
}
function audit_user_change(PDO $db, string $action, int $targetUserId, array $old = [], array $new = []): void {
    $db->exec("CREATE TABLE IF NOT EXISTS user_audit_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        actor_user_id BIGINT UNSIGNED NULL,
        target_user_id BIGINT UNSIGNED NOT NULL,
        action VARCHAR(40) NOT NULL,
        old_data JSON NULL,
        new_data JSON NULL,
        ip_address VARCHAR(64) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id), KEY idx_user_audit_target (target_user_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $stmt = $db->prepare('INSERT INTO user_audit_log(actor_user_id,target_user_id,action,old_data,new_data,ip_address) VALUES(:actor,:target,:action,:old,:new,:ip)');
    $stmt->execute([
        'actor'=>current_user()['id'] ?? null, 'target'=>$targetUserId, 'action'=>$action,
        'old'=>$old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
        'new'=>$new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
        'ip'=>$_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
function ensure_password_resets_table(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_password_resets_token_hash (token_hash),
        KEY idx_password_resets_user_id (user_id),
        CONSTRAINT fk_password_resets_user,
        FOREIGN KEY (user_id) REFERENCES users(id)
          ON DELETE CASCADE
          ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function app_base_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $scheme . '://' . $host . ($dir ? $dir : '');
}
function send_app_email(string $to, string $subject, string $body, string $replyTo = '', bool $isHtml = false): bool {
    $sent = false;

    // Envío principal: Gmail SMTP con PHPMailer.
    if (function_exists('send_smtp_email')) {
        $sent = send_smtp_email($to, $subject, $body, $replyTo, $isHtml);
    }

    // Fallback opcional. Por defecto queda desactivado para no ocultar errores de Gmail SMTP.
    if (!$sent && (string)env('MAIL_FALLBACK_PHP_MAIL', 'false') === 'true' && function_exists('mail')) {
        $fromEmail = (string)env('MAIL_FROM_ADDRESS', env('MAIL_FROM', 'no-reply@goresidentgo.local'));
        $fromName = (string)env('MAIL_FROM_NAME', 'GoResidentGo');
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>'
        ];
        if ($replyTo !== '') $headers[] = 'Reply-To: ' . $replyTo;
        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    app_log('mail', 'Correo procesado', ['to'=>$to, 'subject'=>$subject, 'sent'=>$sent ? 'SI' : 'NO']);
    return $sent;
}
function role_label(string $role): string {
    return ['admin' => 'Administrador', 'porteria' => 'Portería', 'residente' => 'Residente'][$role] ?? $role;
}
function post(string $key, $default = '') { return $_POST[$key] ?? $default; }
function get(string $key, $default = '') { return $_GET[$key] ?? $default; }
