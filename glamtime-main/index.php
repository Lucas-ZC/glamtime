<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$hoje   = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE DATE(data_hora) = CURDATE() AND status = 'agendado'")->fetchColumn();
$mes    = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE MONTH(data_hora) = MONTH(CURDATE()) AND YEAR(data_hora) = YEAR(CURDATE()) AND status = 'concluido'")->fetchColumn();
$top3   = $pdo->query("SELECT s.nome, COUNT(a.id) total FROM agendamentos a JOIN servicos s ON a.servico_id = s.id WHERE a.status='concluido' GROUP BY s.id, s.nome ORDER BY total DESC LIMIT 3")->fetchAll();
$flash  = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container">
    <?php if ($flash): ?><div class="alert alert-warning"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><div class="card text-bg-primary"><div class="card-body"><h3><?= (int) $hoje ?></h3>Agendados hoje</div></div></div>
      <div class="col-md-6"><div class="card text-bg-success"><div class="card-body"><h3><?= (int) $mes ?></h3>Concluídos no mês</div></div></div>
    </div>
    <div class="card"><div class="card-header bg-white fw-bold">Top 3 Serviços</div>
      <ul class="list-group list-group-flush"><?php foreach ($top3 as $t): ?><li class="list-group-item"><?= htmlspecialchars($t['nome']) ?> <span class="badge bg-primary"><?= (int) $t['total'] ?></span></li><?php endforeach; ?></ul>
    </div>
  </main>
</body>
</html>