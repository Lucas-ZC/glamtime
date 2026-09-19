-- ============================================================
-- glamtime.sql — DDL + dados iniciais do banco GlamTime
-- Uso: importe via phpMyAdmin OU: mysql -u root < sql/glamtime.sql
-- Senhas: geradas por password_hash() — nunca em texto plano.
-- ============================================================

CREATE DATABASE IF NOT EXISTS glamtime
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE glamtime;

-- ── ESTRUTURA ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS servicos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(80)   NOT NULL,
    duracao_min INT           NOT NULL,
    preco       DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uq_servico_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nome     VARCHAR(80)  NOT NULL,
    telefone VARCHAR(20)  NULL,
    email    VARCHAR(120) NULL,
    UNIQUE KEY uq_cliente_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(80)  NOT NULL,
    email      VARCHAR(120) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    role       ENUM('admin','recepcionista') NOT NULL DEFAULT 'recepcionista'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agendamentos (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servico_id INT NOT NULL,
    data_hora  DATETIME NOT NULL,
    status     ENUM('agendado','concluido','cancelado') NOT NULL DEFAULT 'agendado',
    CONSTRAINT fk_ag_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_ag_servico FOREIGN KEY (servico_id) REFERENCES servicos(id),
    INDEX idx_data_hora (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DADOS INICIAIS ─────────────────────────────────────────────
INSERT INTO servicos (nome, duracao_min, preco) VALUES
('Corte Feminino',      45,  60.00),
('Escova Modeladora',   30,  40.00),
('Coloração Completa', 120, 180.00),
('Manicure e Pedicure', 60,  35.00);

INSERT INTO clientes (nome, telefone, email) VALUES
('Mariana Souza',  '(49) 98765-4321', 'mariana@email.com'),
('Carlos Eduardo', '(49) 97654-3210', 'carlos.edu@email.com'),
('Juliana Paes',   '(49) 96543-2109', 'juliana.p@email.com');

-- SENHAS: rode no terminal e troque o marcador pelo resultado:
--   php -r "echo password_hash('admin', PASSWORD_DEFAULT), PHP_EOL;"
--   php -r "echo password_hash('recep123', PASSWORD_DEFAULT), PHP_EOL;"
INSERT INTO usuarios (nome, email, senha_hash, role) VALUES
('Administrador Geral', 'admin@glamtime.com',    '[GERAR_HASH_ADMIN]',  'admin'),
('Bruna Recepcionista', 'recepcao@glamtime.com', '[GERAR_HASH_RECEP]',  'recepcionista');

INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status) VALUES
(1, 1, '2026-09-18 10:00:00', 'concluido'),
(2, 3, '2026-09-18 14:00:00', 'agendado'),
(3, 4, '2026-09-19 09:30:00', 'agendado');