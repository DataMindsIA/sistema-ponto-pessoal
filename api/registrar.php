<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['acao'])) {
        throw new Exception('Acao nao especificada');
    }
    
    $acao = $input['acao'];
    $horario = $input['horario'] ?? '';
    
    $resultado = registrarPonto($acao, $horario);
    echo json_encode($resultado);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
