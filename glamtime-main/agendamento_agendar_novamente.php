<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT cliente_id, servico_id FROM agendamentos WHERE id = :id");
$stmt->execute([':id' => $id]);
$ag = $stmt->fetch();

$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
if (!$ag) { header('Location: agendamentos.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['csrf'] ?? '') !== $csrf) { die('Token inválido.'); }
    $novaData = $_POST['data_hora'] ?? '';
    $durStmt = $pdo->prepare("SELECT duracao_min FROM servicos WHERE id = :id");
    $durStmt->execute([':id' => (int) $ag['servico_id']]);
    $dur = (int) $durStmt->fetchColumn();
    if ($novaData === '' || strtotime($novaData) === false) {
        $_SESSION['flash'] = 'Data inválida.';
        header('Location: agendamentos.php'); exit;
    }
    $inicio = date('Y-m-d H:i:s', strtotime($novaData));
    $fim    = date('Y-m-d H:i:s', strtotime($novaData) + $dur * 60);

    require_once __DIR__ . '/src/AgendamentoDAO.php';
    $dao = new AgendamentoDAO($pdo);

    $pdo->beginTransaction();
    try {
        if ($dao->verificarConflito($inicio, $fim)) {
            throw new RuntimeException('Horário conflita com outro agendamento.');
        }
        $ins = $pdo->prepare("INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status)
                              VALUES (:c, :s, :d, 'agendado')");
        $ins->execute([':c' => (int) $ag['cliente_id'], ':s' => (int) $ag['servico_id'], ':d' => $inicio]);
        $upd = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = :id");
        $upd->execute([':id' => $id]);
        $pdo->commit();
        $_SESSION['flash'] = 'Novo agendamento criado a partir do anterior.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = $e->getMessage();
    }
    header('Location: agendamentos.php'); exit;
}
?>
<form method="post" onsubmit="return confirm('Criar novo agendamento?')" class="d-inline">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
  <input type="datetime-local" name="data_hora" class="form-control d-inline w-auto" required>
  <button class="btn btn-outline-success btn-sm">Confirmar</button>
</form>
    