<?php
// src/Servico.class.php
declare(strict_types=1);

class Servico
{
    public function __construct(
        private ?int   $id,
        private string $nome,
        private int    $duracaoMin,
        private float  $preco
    ) {}

    public function getDuracaoMin(): int { return $this->duracaoMin; }
    public function getPrecoFormatado(): string
    {
        return 'R$ ' . number_format($this->preco, 2, ',', '.');
    }
}