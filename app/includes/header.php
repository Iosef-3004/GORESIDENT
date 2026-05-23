<?php 
require_once __DIR__ . '/init.php'; require_login(); $flash = get_flash(); $user = current_user(); $pageTitle = $pageTitle ?? 'GoResidentGo'; $pageSlug = $pageSlug ?? ''; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle) ?> • GoResidentGo</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <div class="brand"><span class="brand-dot"></span><span>GoResidentGo</span></div>
    <nav class="nav topbar-nav">
      <div class="topbar-actions" aria-label="Accesos rapidos">
        <button class="topbar-icon-btn has-pulse" type="button" data-chatbot-toggle title="Abrir asistente" aria-label="Abrir asistente GoResidentGo">
          <span class="topbar-icon" aria-hidden="true">🤖</span>
          <span class="topbar-label">Bot</span>
          <span class="topbar-badge" data-chatbot-badge>?</span>
        </button>
        <button class="topbar-icon-btn" type="button" data-pqr-open title="Crear PQR" aria-label="Crear PQR">
          <span class="topbar-icon" aria-hidden="true">📩</span>
          <span class="topbar-label">PQR</span>
        </button>
      </div>
      <button class="btn" data-theme-toggle>🌙 Tema</button>
      <span class="badge"><span class="dot blue"></span><?= e(role_label($user['role'])) ?></span>
      <a class="btn" href="logout.php">Salir</a>
    </nav>
  </div>
</header>
<div class="shell" style="display:grid;grid-template-columns:280px 1fr;">
  <aside class="sidebar">
    <div class="side-head">
      <div class="brand"><span class="brand-dot"></span><span>GoResidentGo</span></div>
      <span class="badge"><?= e(role_label($user['role'])) ?></span>
    </div>
    <div class="hr"></div>
    <nav class="side-menu">
      <a class="side-link <?= $pageSlug==='dashboard'?'active':'' ?>" href="dashboard.php"><span>Inicio</span><span class="small">🏠</span></a>
      <?php if (is_gate()): ?>
        <a class="side-link <?= $pageSlug==='entrada'?'active':'' ?>" href="parking-entry.php"><span>Entradas</span><span class="small">↘</span></a>
        <a class="side-link <?= $pageSlug==='salida'?'active':'' ?>" href="parking-exit.php"><span>Salidas</span><span class="small">↗</span></a>
      <?php endif; ?>
      <a class="side-link <?= $pageSlug==='cupos'?'active':'' ?>" href="spaces.php"><span>Cupos</span><span class="small">🅿</span></a>
      <?php if (is_gate()): ?>
        <a class="side-link <?= $pageSlug==='residentes'?'active':'' ?>" href="residents.php"><span>Residentes</span><span class="small">👥</span></a>
      <?php endif; ?>
      <?php if (is_admin()): ?>
        <a class="side-link <?= $pageSlug==='usuarios'?'active':'' ?>" href="users.php"><span>Usuarios</span><span class="small">🔐</span></a>
        <a class="side-link <?= $pageSlug==='tarifas'?'active':'' ?>" href="rates.php"><span>Tarifas</span><span class="small">💲</span></a>
      <?php endif; ?>
      <a class="side-link <?= $pageSlug==='reportes'?'active':'' ?>" href="reports.php"><span>Reportes</span><span class="small">📊</span></a>
    </nav>
    <div class="hr"></div>
  </aside>
  <main class="main">
    <div class="main-top">
      <div>
        <h2 class="page-title"><?= e($pageTitle) ?></h2>
      </div>
    </div>
    <?php if ($flash): ?>
      <div class="flash <?= $flash['type']==='ok' ? 'ok' : 'err' ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>