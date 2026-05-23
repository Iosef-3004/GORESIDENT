<?php
$pageTitle = 'Inicio'; $pageSlug = 'dashboard';
require_once __DIR__ . '/../app/includes/header.php';
$stats = $db->query('SELECT * FROM vw_dashboard')->fetch() ?: ['total_cupos'=>0,'cupos_disponibles'=>0,'vehiculos_dentro'=>0,'tickets_generados'=>0,'ocupacion_pct'=>0];
$recent = $db->query("SELECT movement_ts, plate, ticket, access_type, unit_name, event_type, status FROM movement_log ORDER BY movement_ts DESC, id DESC LIMIT 10")->fetchAll();
$active = $db->query("SELECT ps.ticket, ps.plate, ps.vehicle_type, ps.access_type, ps.unit_name, ps.entered_at, sp.code AS space_code FROM parking_sessions ps JOIN parking_spaces sp ON sp.id = ps.space_id WHERE ps.exited_at IS NULL ORDER BY ps.entered_at DESC LIMIT 10")->fetchAll();
?>
<div class="card section" style="margin-bottom:16px;">
  <div class="page-head">
    <div>
      <div class="badge"><span class="dot blue"></span>Panel</div>
      <h3 class="welcome">Bienvenido, <?= e(current_user()['name']) ?></h3>
      <p class="hint">Resumen operativo del conjunto residencial.</p>
    </div>
    <div class="actions">
      <?php if (is_gate()): ?><a class="btn success" href="parking-entry.php">Registrar entrada</a><a class="btn" href="parking-exit.php">Registrar salida</a><?php endif; ?>
      <a class="btn" href="reports.php">Ver reportes</a>
    </div>
  </div>
</div>
<div class="stat-cards">
  <div class="card section"><div class="small">Cupos disponibles</div><div style="font-size:28px;font-weight:900;margin-top:6px;"><?= e((string)$stats['cupos_disponibles']) ?></div><div class="small">de <?= e((string)$stats['total_cupos']) ?></div></div>
  <div class="card section"><div class="small">Vehículos dentro</div><div style="font-size:28px;font-weight:900;margin-top:6px;"><?= e((string)$stats['vehiculos_dentro']) ?></div><div class="small">activos</div></div>
  <div class="card section"><div class="small">Tickets generados</div><div style="font-size:28px;font-weight:900;margin-top:6px;"><?= e((string)$stats['tickets_generados']) ?></div><div class="small">histórico</div></div>
  <div class="card section"><div class="small">Ocupación</div><div style="font-size:28px;font-weight:900;margin-top:6px;"><?= e((string)$stats['ocupacion_pct']) ?>%</div><div class="small">estimada</div></div>
</div>
<div class="split" style="margin-top:16px;">
  <div class="card section">
    <h3>Últimos movimientos</h3>
    <table class="table small-table"><thead><tr><th>Fecha/Hora</th><th>Placa</th><th>Ticket</th><th>Acceso</th><th>Unidad</th><th>Evento</th><th>Estado</th></tr></thead><tbody>
      <?php if (!$recent): ?><tr><td colspan="7" class="empty">Aún no hay movimientos.</td></tr><?php endif; ?>
      <?php foreach ($recent as $row): ?><tr><td><?= e($row['movement_ts']) ?></td><td><b><?= e($row['plate']) ?></b></td><td><?= e($row['ticket']) ?></td><td><?= e($row['access_type']) ?></td><td><?= e($row['unit_name'] ?: '—') ?></td><td><?= e($row['event_type']) ?></td><td><?= e($row['status']) ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
  <div class="card section">
    <h3>Sesiones activas</h3>
    <table class="table small-table"><thead><tr><th>Ticket</th><th>Placa</th><th>Cupo</th><th>Tipo</th><th>Ingreso</th></tr></thead><tbody>
      <?php if (!$active): ?><tr><td colspan="5" class="empty">No hay vehículos dentro.</td></tr><?php endif; ?>
      <?php foreach ($active as $row): ?><tr><td><?= e($row['ticket']) ?></td><td><b><?= e($row['plate']) ?></b></td><td><?= e($row['space_code']) ?></td><td><?= e($row['access_type']) ?></td><td><?= e($row['entered_at']) ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<?php require_once __DIR__ . '/../app/includes/footer.php'; ?>
