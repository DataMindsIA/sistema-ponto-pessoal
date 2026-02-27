<?php
/**
 * Conexao com banco SQLite
 * Sistema de Ponto Pessoal
 */

define('DB_PATH', __DIR__ . '/../ponto.db');
define('SQL_SCHEMA', __DIR__ . '/../sql/schema.sql');

function getConexao(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Habilitar foreign keys no SQLite
            $pdo->exec('PRAGMA foreign_keys = ON;');
            $pdo->exec('PRAGMA journal_mode = WAL;');

            // Criar tabelas se nao existirem
            $schema = file_get_contents(SQL_SCHEMA);
            $pdo->exec($schema);

        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['erro' => 'Falha na conexao com banco de dados: ' . $e->getMessage()]));
        }
    }

    return $pdo;
}

function getConfig(): array {
    $pdo = getConexao();
    $stmt = $pdo->query('SELECT * FROM config WHERE id = 1');
    return $stmt->fetch() ?: [];
}
