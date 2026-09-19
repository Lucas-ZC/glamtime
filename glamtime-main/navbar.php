<?php $perfil = $_SESSION['usuario_role'] ?? ''; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">💇 GlamTime</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="agendamentos.php">Agendamentos</a></li>
        <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
        <?php if ($perfil === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="servicos.php">Serviços</a></li>
        <?php endif; ?>
      </ul>
      <a href="logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
    </div>
  </div>
</nav>