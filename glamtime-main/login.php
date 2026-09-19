<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';

if (isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha !== '') {
        // Prepared statement: dado nunca vira parte do SQL
        $stmt = $pdo->prepare("SELECT id, nome, senha_hash, role FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            session_regenerate_id(true);                 // mitiga Session Fixation
            $_SESSION['usuario_id']   = (int) $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_role'] = $usuario['role'];
            header('Location: index.php');
            exit;
        }
    }
    // Mensagem genérica: não revelar qual campo falhou (evita enumeração de contas)
    $erro = 'Credenciais inválidas.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container" style="max-width:420px">
    <div class="card shadow-sm mt-5">
      <div class="card-body p-4">
        <h4 class="fw-bold mb-1">💇 GlamTime</h4>
        <p class="text-muted small mb-3">Acesse o painel de agendamentos</p>

        <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <input class="form-control" type="email" name="email" placeholder="E-mail" required autofocus>
          </div>
          <div class="mb-3">
            <input class="form-control" type="password" name="senha" placeholder="Senha" required>
          </div>
          <button class="btn btn-primary w-100 mb-2">Entrar</button>
          <a href="registro.php" class="btn btn-outline-secondary w-100">Criar conta</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>