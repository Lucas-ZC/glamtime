<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$lista = $pdo->query("SELECT id, nome, telefone, email FROM clientes ORDER BY nome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container">
    <h4 class="fw-bold">Clientes</h4>
    <table class="table table-hover bg-white shadow-sm">
      <thead><tr><th>Nome</th><th>Telefone</th><th>E-mail</th></tr></thead>
      <tbody><?php foreach ($lista as $c): ?><tr>
        <td><?= htmlspecialchars($c['nome']) ?></td><td><?= htmlspecialchars($c['telefone']) ?></td><td><?= htmlspecialchars($c['email']) ?></td>
      </tr><?php endforeach; ?></tbody>
    </table>
  </main>
</body>
</html>