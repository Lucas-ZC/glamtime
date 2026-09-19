<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome")->fetchAll();
$servicos = $pdo->query("SELECT id, nome FROM servicos ORDER BY nome")->fetchAll();
$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Novo agendamento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container" style="max-width:560px">
    <div class="card shadow-sm"><div class="card-body">
      <h5 class="fw-bold mb-3">Novo Agendamento</h5>
      <form method="post" action="agendamento_salvar.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3"><label class="form-label">Cliente</label>
          <select class="form-select" name="cliente_id" required><?php foreach ($clientes as $c): ?><option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label">Serviço</label>
          <select class="form-select" name="servico_id" required><?php foreach ($servicos as $s): ?><option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label">Data e hora</label>
          <input type="datetime-local" class="form-control" name="data_hora" value="<?= htmlspecialchars($_POST['data_hora'] ?? '') ?>" required></div>
        <button class="btn btn-primary w-100">Agendar</button>
      </form>
    </div></div>
  </main>
</body>
</html>