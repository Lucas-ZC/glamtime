<?php
declare(strict_types=1);

class Agendamento implements JsonSerializable
{
    private ?int $id;
    private int $clienteId;
    private int $servicoId;
    private DateTime $dataHora;
    private string $status;
    private ?float $valorCobrado;

    public function __construct(int $clienteId, int $servicoId, string $dataHora,
                                string $status = 'agendado', ?float $valorCobrado = null, ?int $id = null)
    {
        $this->id           = $id;
        $this->clienteId    = $clienteId;
        $this->servicoId    = $servicoId;
        $this->setDataHora($dataHora);
        $this->status       = $status;
        $this->valorCobrado = $valorCobrado;
    }

    public function setDataHora(string $dataHora): void
    {
        $dt = new DateTime($dataHora);
        if ($dt < new DateTime('now')) {
            throw new InvalidArgumentException('Não é permitido agendar no passado.');
        }
        $this->dataHora = $dt;
    }

    public function getPrecoFormatado(): string
    {
        return 'R$ ' . number_format($this->valorCobrado ?? 0, 2, ',', '.');
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'clienteId' => $this->clienteId,
            'servicoId' => $this->servicoId,
            'dataHora'  => $this->dataHora->format('Y-m-d\TH:i:sP'), // ISO 8601
            'status'    => $this->status,
        ];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getClienteId(): int { return $this->clienteId; }
    public function getServicoId(): int { return $this->servicoId; }
    public function getDataHora(): DateTime { return $this->dataHora; }
    public function getStatus(): string { return $this->status; }
}