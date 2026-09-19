<?php
declare(strict_types=1);

class AgendamentoDAO
{
    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        $sql = "SELECT a.id, a.cliente_id, a.servico_id, a.data_hora, a.status,
                       c.nome cliente, s.nome servico
                FROM agendamentos a
                JOIN clientes c ON a.cliente_id = c.id
                JOIN servicos s ON a.servico_id = s.id
                ORDER BY a.data_hora";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM agendamentos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function inserir(Agendamento $a): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status)
             VALUES (:c, :s, :d, :st)"
        );
        $stmt->execute([
            ':c'  => $a->getClienteId(),
            ':s'  => $a->getServicoId(),
            ':d'  => $a->getDataHora()->format('Y-m-d H:i:s'),
            ':st' => $a->getStatus(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function cancelar(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE agendamentos SET status = 'cancelado' WHERE id = :id AND status = 'agendado'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function contarPorStatus(string $status): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE status = :st");
        $stmt->execute([':st' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function verificarConflito(string $inicio, string $fim, ?int $ignorarId = null): bool
{
    $sql = "SELECT COUNT(*) FROM agendamentos a
            JOIN servicos s ON a.servico_id = s.id
            WHERE a.status = 'agendado'
              AND :inicio < DATE_ADD(a.data_hora, INTERVAL s.duracao_min MINUTE)
              AND :fim    > a.data_hora";

    $params = [':inicio' => $inicio, ':fim' => $fim];

    if ($ignorarId !== null) {
        $sql .= " AND a.id <> :ign";
        $params[':ign'] = $ignorarId;
    }

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}
}