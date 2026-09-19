<?php
// src/Cliente.class.php
declare(strict_types=1);

class Cliente
{
    public function __construct(
        private ?int    $id,
        private string  $nome,
        private ?string $telefone,
        private ?string $email
    ) {}

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getNome(): string { return $this->nome; }
    public function getId(): ?int { return $this->id; }
}