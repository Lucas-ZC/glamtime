<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../auth_check.php';

header('Content-Type: application/json; charset=utf-8');
$stmt = $pdo->query("SELECT a.id, c.nome cliente, s.nome servico, a.data_hora, a.status
                     FROM agendamentos a JOIN clientes c ON a.cliente_id=c.id JOIN servicos s ON a.servico_id=s.id
                     ORDER BY a.data_hora");
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
exit;
?>