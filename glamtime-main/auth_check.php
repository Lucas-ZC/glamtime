<?php
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['flash'] = 'Acesso restrito. Faça login.';
    header('Location: login.php');
    exit;
}