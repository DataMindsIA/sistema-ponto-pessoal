<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);
$data = $input['data'] ?? date('Y-m-d');
$tipo = $input['tipo'] ?? 'normal';
$obs = $input['observacao'] ?? '';

$sucesso = atualizarTipoDia($data, $tipo, $obs);
echo json_encode(['sucesso' => $sucesso]);
