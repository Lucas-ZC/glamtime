<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';

/**
 * registro.php — Self-service de criação de conta (recepcionista).
 * ⚠️ Contas aquí sempre NASCEM como 'recepcionista' — escalonar para
 * admin é função exclusiva de um admin já autenticado (Bloco 4 - RBAC).
 */

$erros = [];
$sucesso = null;
$valores = ['nome' => '', 'email' => ''];

function validarForcaSenha(string $senha): ?string
{
    if (mb_strlen($senha) < 8)                    return 'Mínimo de 8 caracteres.';
    if (!preg_match('/[A-Z]/', $senha))           return 'Precisa de uma letra maiúscula.';
    if (!preg_match('/[a-z]/', $senha))           return 'Precisa de uma letra minúscula.';
    if (!preg_match('/\d/', $senha))              return 'Precisa de um dígito.';
    if (in_array(strtolower($senha), ['admin1234','senha1234','password1'], true)) {
        return 'Esta senha é muito comum — escolha outra.';
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_SESSION['csrf'] ?? '') === '' || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erros[] = 'Sessão expirada, tente novamente.';
  } else {
      $nome     = trim($_POST['nome'] ?? '');
      $nome     = trim($_POST['nome'] ?? '');
      $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
      $senha    = $_POST['senha'] ?? '';
      $confirma = $_POST['confirmar'] ?? '';

      if ($nome === '')                                               $erros[] = 'Informe o nome.';
      if (!$email)                                                    $erros[] = 'Informe um e-mail válido.';
      if ($motivo = validarForcaSenha($senha))                        $erros[] = $motivo;
      if ($senha !== $confirma)                                       $erros[] = 'Confirmação não corresponde à senha.';

      $valores['nome']  = $nome;
      $valores['email'] = $email ?? '';

      if (!$erros) {
          $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
          $check->execute([':email' => $email]);
          if ($check->fetch()) {
              $erros[] = 'Este e-mail já está registrado.';
          } else {
              // BCRYPT via PASSWORD_DEFAULT — hash SÓ no servidor, nunca na view
              $stmt = $pdo->prepare(
                  "INSERT INTO usuarios (nome, email, senha_hash, role)
                  VALUES (:nome, :email, :hash, 'recepcionista')"
              );
              $stmt->execute([
                  ':nome'  => $nome,
                  ':email' => $email,
                  ':hash'  => password_hash($senha, PASSWORD_DEFAULT),
              ]);
              $_SESSION['flash'] = 'Conta criada! Faça login com o e-mail cadastrado.';
              header('Location: login.php');
              exit;
          }
      }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Criar conta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <div class="container" style="max-width:480px">
    <div class="card shadow-sm my-5">
      <div class="card-body p-4">
        <h4 class="fw-bold mb-3">Criar conta</h4>

        <?php foreach ($erros as $e): ?><div class="alert alert-danger"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

        <form method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)))) ?>">
          <div class="mb-2"><input class="form-control" name="nome"             placeholder="Nome completo" value="<?= htmlspecialchars($valores['nome']) ?>" required></div>
          <div class="mb-2"><input class="form-control" type="email" name="email" placeholder="E-mail"            value="<?= htmlspecialchars($valores['email']) ?>" required></div>
          <div class="mb-2"><input class="form-control" type="password" name="senha"    placeholder="Senha (mín. 8 + Aa + dígito)" required></div>
          <div class="mb-2"><input class="form-control" type="password" name="confirmar" placeholder="Confirmar senha" required></div>
          <button class="btn btn-primary w-100 mb-2">Cadastrar</button>
          <a href="login.php" class="btn btn-link w-100">Já tenho conta</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>