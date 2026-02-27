-- ================================================
-- Sistema de Ponto Pessoal
-- Schema SQLite
-- ================================================

-- Tabela de configuracoes
CREATE TABLE IF NOT EXISTS config (
    id INTEGER PRIMARY KEY,
    carga_horaria TEXT NOT NULL DEFAULT '08:00',
    intervalo_almoco TEXT NOT NULL DEFAULT '01:00',
    dias_uteis TEXT NOT NULL DEFAULT '1,2,3,4,5',
    nome_usuario TEXT NOT NULL DEFAULT 'Michael',
    fuso_horario TEXT NOT NULL DEFAULT 'America/Belem'
);

INSERT OR IGNORE INTO config (id, carga_horaria, intervalo_almoco, dias_uteis, nome_usuario, fuso_horario)
VALUES (1, '08:00', '01:00', '1,2,3,4,5', 'Michael', 'America/Belem');

-- Tabela de registros de ponto
CREATE TABLE IF NOT EXISTS registros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    data DATE NOT NULL UNIQUE,
    entrada TEXT,
    saida_almoco TEXT,
    volta_almoco TEXT,
    saida TEXT,
    tipo_dia TEXT NOT NULL DEFAULT 'normal',
    observacao TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tipos de dia validos:
-- normal             = dia de trabalho comum
-- feriado_trabalhado = feriado mas trabalhou
-- feriado_folga      = feriado e nao trabalhou
-- abonado            = dia abonado conta como cumprido
-- folga              = dia de folga programada
-- falta              = falta sem justificativa

-- Tabela de feriados
CREATE TABLE IF NOT EXISTS feriados (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    data DATE NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    tipo TEXT NOT NULL DEFAULT 'nacional'
);

-- Feriados nacionais 2025
INSERT OR IGNORE INTO feriados (data, nome, tipo) VALUES
('2025-01-01', 'Ano Novo', 'nacional'),
('2025-04-18', 'Sexta-feira Santa', 'nacional'),
('2025-04-21', 'Tiradentes', 'nacional'),
('2025-05-01', 'Dia do Trabalho', 'nacional'),
('2025-06-19', 'Corpus Christi', 'nacional'),
('2025-09-07', 'Independencia do Brasil', 'nacional'),
('2025-10-12', 'Nossa Senhora Aparecida', 'nacional'),
('2025-11-02', 'Finados', 'nacional'),
('2025-11-15', 'Proclamacao da Republica', 'nacional'),
('2025-12-25', 'Natal', 'nacional');

-- Feriados nacionais 2026
INSERT OR IGNORE INTO feriados (data, nome, tipo) VALUES
('2026-01-01', 'Ano Novo', 'nacional'),
('2026-04-03', 'Sexta-feira Santa', 'nacional'),
('2026-04-21', 'Tiradentes', 'nacional'),
('2026-05-01', 'Dia do Trabalho', 'nacional'),
('2026-06-04', 'Corpus Christi', 'nacional'),
('2026-09-07', 'Independencia do Brasil', 'nacional'),
('2026-10-12', 'Nossa Senhora Aparecida', 'nacional'),
('2026-11-02', 'Finados', 'nacional'),
('2026-11-15', 'Proclamacao da Republica', 'nacional'),
('2026-12-25', 'Natal', 'nacional');

-- Indices para performance
CREATE INDEX IF NOT EXISTS idx_registros_data ON registros(data);
CREATE INDEX IF NOT EXISTS idx_feriados_data ON feriados(data);
