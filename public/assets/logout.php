<?php
require_once __DIR__ . '/../app/includes/init.php';
session_destroy();
header('Location: login.php');