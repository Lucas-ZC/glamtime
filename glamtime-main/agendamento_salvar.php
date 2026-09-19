<?php
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    header('Location: agendamentos.php'); exit;
}

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$servicoId = (int) ($_POST['servico_id'] ?? 0);
$dataHora  = $_POST['data_hora'] ?? '';

if (!$clienteId || !$servicoId || $dataHora === '' || strtotime($dataHora) === false) {
    $_SESSION['flash'] = 'Dados inválidos.';
    header('Location: agendamento_form.php'); exit;
}

// Regra de negócio: bloqueio de conflito de horário (sobreposição de intervalos)
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos a JOIN servicos s ON a.servico_id = s.id
        WHERE a.status = 'agendado'
          AND :novo_inicio < DATE_ADD(a.data_hora, INTERVAL s.duracao_min MINUTE)
          AND DATE_ADD(:novo_inicio, INTERVAL (SELECT duracao_min FROM servicos WHERE id = :serv) MINUTE) > a.data_hora");
    $stmt->execute([':novo_inicio' => $dataHora, ':serv' => $servicoId]);

    if ((int) $stmt->fetchColumn() > 0) {
        throw new RuntimeException('Horário conflita com outro agendamento ativo.');
    }

    $ins = $pdo->prepare("INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status) VALUES (:c, :s, :d, 'agendado')");
    $ins->execute([':c' => $clienteId, ':s' => $servicoId, ':d' => $dataHora]);
    $pdo->commit();
    $_SESSION['flash'] = 'Agendamento criado com sucesso!';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = $e->getMessage();
}
header('Location: agendamentos.php');
exit;