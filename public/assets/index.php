<?php
require_once __DIR__ . '/../app/includes/init.php';
if (is_logged()) { header('Location: dashboard.php'); exit; }
header('Location: login.php');