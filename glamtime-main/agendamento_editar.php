<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$id  = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = :id AND status = 'agendado'");
$stmt->execute([':id' => $id]);
$ag = $stmt->fetch();
if (!$ag) { header('Location: agendamentos.php'); exit; }

$clientes  = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome")->fetchAll();
$servicos  = $pdo->query("SELECT id, nome FROM servicos ORDER BY nome")->fetchAll();
$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));

$erroDao = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['csrf'] ?? '') !== $csrf) { die('Token CSRF inválido.'); }

    $novoCliente = (int) ($_POST['cliente_id'] ?? 0);
    $novoServ    = (int) ($_POST['servico_id'] ?? 0);
    $novaData    = $_POST['data_hora'] ?? '';

    // fim do novo intervalo usa a duração do serviço ESCOLHIDO
    $durStmt = $pdo->prepare("SELECT duracao_min FROM servicos WHERE id = :id");
    $durStmt->execute([':id' => $novoServ]);
    $duracao = (int) $durStmt->fetchColumn();
    $inicio = $novaData ? date('Y-m-d H:i:s', strtotime($novaData)) : '';
    $fim    = $novaData ? date('Y-m-d H:i:s', strtotime($novaData) + $duracao * 60) : '';

    require_once __DIR__ . '/src/AgendamentoDAO.php';
    $dao = new AgendamentoDAO($pdo);

    if ($novaData === '' || $duracao === 0 || $dao->verificarConflito($inicio, $fim, $id)) {
        $erroDao = 'Horário conflita com outro agendamento ativo.';
    } else {
        $upd = $pdo->prepare(
            "UPDATE agendamentos SET cliente_id = :c, servico_id = :s, data_hora = :d WHERE id = :id"
        );
        $upd->execute([':c' => $novoCliente, ':s' => $novoServ, ':d' => $inicio, ':id' => $id]);
        $_SESSION['flash'] = 'Agendamento atualizado!';
        header('Location: agendamentos.php'); exit;
    }
    // repopula $ag com os valores enviados em caso de erro
    $ag = array_merge($ag, ['cliente_id' => $novoCliente, 'servico_id' => $novoServ, 'data_hora' => $novaData]);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Editar agendamento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container" style="max-width:560px">
    <div class="card shadow-sm"><div class="card-body">
      <h5 class="fw-bold mb-3">Editar Agendamento #<?= (int) $ag['id'] ?></h5>
      <?php if ($erroDao): ?><div class="alert alert-danger"><?= htmlspecialchars($erroDao) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3"><label class="form-label">Cliente</label>
          <select class="form-select" name="cliente_id" required>
            <?php foreach ($clientes as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= (int) $ag['cliente_id'] === (int) $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-3"><label class="form-label">Serviço</label>
          <select class="form-select" name="servico_id" required>
            <?php foreach ($servicos as $s): ?>
              <option value="<?= (int) $s['id'] ?>" <?= (int) $ag['servico_id'] === (int) $s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-3"><label class="form-label">Data e hora</label>
          <input type="datetime-local" class="form-control" name="data_hora"
                 value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($ag['data_hora']))) ?>" required></div>
        <button class="btn btn-primary w-100">Salvar alterações</button>
        <a href="agendamentos.php" class="btn btn-link w-100">Voltar</a>
      </form>
    </div></div>
  </main>
</body>
</html>