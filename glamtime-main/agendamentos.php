<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$filtro = $_GET['data'] ?? '';
if ($filtro) {
    $stmt = $pdo->prepare("SELECT a.id, c.nome cliente, s.nome servico, a.data_hora, a.status FROM agendamentos a JOIN clientes c ON a.cliente_id=c.id JOIN servicos s ON a.servico_id=s.id WHERE DATE(a.data_hora) = :d ORDER BY a.data_hora");
    $stmt->execute([':d' => $filtro]);
} else {
    $stmt = $pdo->query("SELECT a.id, c.nome cliente, s.nome servico, a.data_hora, a.status FROM agendamentos a JOIN clientes c ON a.cliente_id=c.id JOIN servicos s ON a.servico_id=s.id ORDER BY a.data_hora");
}
$lista = $stmt->fetchAll();
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$badges = ['agendado' => 'bg-primary', 'concluido' => 'bg-success', 'cancelado' => 'bg-secondary'];
$rotulos = ['agendado' => 'Agendado', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Agendamentos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container">
    <?php if ($flash): ?><div class="alert alert-success"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <form class="card card-body mb-3 d-flex flex-row gap-2">
      <input type="date" class="form-control" name="data" value="<?= htmlspecialchars($filtro) ?>">
      <button class="btn btn-outline-primary">Filtrar</button>
      <a href="agendamento_form.php" class="btn btn-primary">Novo</a>
    </form>
    <table class="table table-hover bg-white shadow-sm">
      <thead><tr><th>Data/Hora</th><th>Cliente</th><th>Serviço</th><th>Status</th><th>Ações</th></tr></thead>
      <tbody><?php foreach ($lista as $a): ?>
        <tr>
          <td><?= date('d/m/Y H:i', strtotime($a['data_hora'])) ?></td>
          <td><?= htmlspecialchars($a['cliente']) ?></td>
          <td><?= htmlspecialchars($a['servico']) ?></td>
          <td><span class="badge <?= $badges[$a['status']] ?? 'bg-secondary' ?>"><?= $rotulos[$a['status']] ?? $a['status'] ?></span></td>
          <td>
  <?php if ($a['status'] === 'agendado'): ?>
    <a href="agendamento_editar.php?id=<?= (int) $a['id'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
    <a href="agendamento_agendar_novamente.php?id=<?= (int) $a['id'] ?>" class="btn btn-outline-success btn-sm">Agendar novamente</a>
    <form method="post" action="agendamento_cancelar.php" class="d-inline">
      <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)))) ?>">
      <button class="btn btn-outline-danger btn-sm">Cancelar</button>
    </form>
  <?php else: ?>
    <span class="text-muted small">—</span>
  <?php endif; ?>
</td>
        </tr>
      <?php endforeach; ?></tbody>
    </table>
  </main>
</body>
</html>