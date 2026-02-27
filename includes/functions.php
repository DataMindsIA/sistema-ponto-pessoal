<?php
/**
 * Funcoes auxiliares do Sistema de Ponto
 */

require_once __DIR__ . '/../db/conexao.php';

// ============================================
// REGISTRO DE PONTO
// ============================================

function getRegistroDia(string $data): array {
    $pdo = getConexao();
    $stmt = $pdo->prepare('SELECT * FROM registros WHERE data = ?');
    $stmt->execute([$data]);
    $registro = $stmt->fetch();

    if (!$registro) {
        // Criar registro vazio para o dia
        $pdo->prepare('INSERT OR IGNORE INTO registros (data) VALUES (?)')
            ->execute([$data]);
        return ['data' => $data, 'entrada' => null, 'saida_almoco' => null,
                'volta_almoco' => null, 'saida' => null, 'tipo_dia' => 'normal', 'observacao' => null];
    }

    return $registro;
}

function getProximaAcao(array $registro): string {
    if (!$registro['entrada'])      return 'entrada';
    if (!$registro['saida_almoco']) return 'saida_almoco';
    if (!$registro['volta_almoco']) return 'volta_almoco';
    if (!$registro['saida'])        return 'saida';
    return 'completo';
}

function getLabelAcao(string $acao): string {
    return match($acao) {
        'entrada'      => '🟢 Registrar Entrada',
        'saida_almoco' => '🟡 Saida para Almoco',
        'volta_almoco' => '🔵 Volta do Almoco',
        'saida'        => '🔴 Registrar Saida',
        'completo'     => '✅ Dia Completo',
        default        => $acao,
    };
}

function registrarPonto(string $acao, string $horario = ''): array {
    $pdo     = getConexao();
    $hoje    = date('Y-m-d');
    $horario = $horario ?: date('H:i:s');
    $campos  = ['entrada', 'saida_almoco', 'volta_almoco', 'saida'];

    if (!in_array($acao, $campos)) {
        return ['sucesso' => false, 'mensagem' => 'Acao invalida'];
    }

    $registro = getRegistroDia($hoje);

    if ($registro[$acao]) {
        return ['sucesso' => false, 'mensagem' => ucfirst(str_replace('_', ' ', $acao)) . ' ja registrada'];
    }

    $pdo->prepare("UPDATE registros SET {$acao} = ?, updated_at = CURRENT_TIMESTAMP WHERE data = ?")
        ->execute([$horario, $hoje]);

    return ['sucesso' => true, 'mensagem' => 'Ponto registrado: ' . $horario, 'horario' => $horario];
}

function atualizarTipoDia(string $data, string $tipo, string $observacao = ''): bool {
    $tiposValidos = ['normal', 'feriado_trabalhado', 'feriado_folga', 'abonado', 'folga', 'falta'];
    if (!in_array($tipo, $tiposValidos)) return false;

    getConexao()
        ->prepare('UPDATE registros SET tipo_dia = ?, observacao = ?, updated_at = CURRENT_TIMESTAMP WHERE data = ?')
        ->execute([$tipo, $observacao, $data]);

    return true;
}

function editarRegistro(string $data, array $campos): bool {
    $permitidos = ['entrada', 'saida_almoco', 'volta_almoco', 'saida', 'tipo_dia', 'observacao'];
    $sets = [];
    $vals = [];

    foreach ($campos as $campo => $valor) {
        if (in_array($campo, $permitidos)) {
            $sets[] = "{$campo} = ?";
            $vals[] = $valor ?: null;
        }
    }

    if (empty($sets)) return false;

    $sets[] = 'updated_at = CURRENT_TIMESTAMP';
    $vals[] = $data;

    getConexao()
        ->prepare('UPDATE registros SET ' . implode(', ', $sets) . ' WHERE data = ?')
        ->execute($vals);

    return true;
}

// ============================================
// CALCULOS DE HORAS
// ============================================

function calcularHorasTrabalhadas(array $registro): int {
    // Retorna segundos trabalhados
    if (!$registro['entrada']) return 0;

    $entrada = strtotime($registro['data'] . ' ' . $registro['entrada']);

    // Se ainda nao saiu, usa hora atual
    if (!$registro['saida']) {
        $saida = time();
    } else {
        $saida = strtotime($registro['data'] . ' ' . $registro['saida']);
    }

    $total = $saida - $entrada;

    // Descontar almoco se registrado
    if ($registro['saida_almoco'] && $registro['volta_almoco']) {
        $saidaAlm  = strtotime($registro['data'] . ' ' . $registro['saida_almoco']);
        $voltaAlm  = strtotime($registro['data'] . ' ' . $registro['volta_almoco']);
        $total    -= ($voltaAlm - $saidaAlm);
    }

    return max(0, $total);
}

function calcularSaldoSegundos(array $registro, array $config): int {
    $cargaSegundos = horaParaSegundos($config['carga_horaria']);
    $trabalhados   = calcularHorasTrabalhadas($registro);

    return match($registro['tipo_dia']) {
        'abonado'           => 0,           // dia abonado: saldo zerado (nao deve nada)
        'feriado_folga'     => 0,           // feriado sem trabalho: neutro
        'folga'             => 0,           // folga programada: neutro
        'falta'             => -$cargaSegundos, // falta: deve o dia inteiro
        'feriado_trabalhado'=> $trabalhados,    // feriado trabalhado: tudo conta positivo
        default             => $trabalhados - $cargaSegundos,
    };
}

function horaParaSegundos(string $hora): int {
    [$h, $m] = array_map('intval', explode(':', $hora));
    return ($h * 3600) + ($m * 60);
}

function segundosParaHora(int $segundos, bool $comSinal = false): string {
    $sinal = '';
    if ($segundos < 0) {
        $sinal = '-';
        $segundos = abs($segundos);
    } elseif ($comSinal && $segundos > 0) {
        $sinal = '+';
    }

    $h = floor($segundos / 3600);
    $m = floor(($segundos % 3600) / 60);

    return $sinal . sprintf('%02d:%02d', $h, $m);
}

function tempoTrabalhadoHoje(array $registro): string {
    $segundos = calcularHorasTrabalhadas($registro);
    return segundosParaHora($segundos);
}

// ============================================
// RELATORIOS
// ============================================

function getRegistrosMes(string $anoMes): array {
    $pdo  = getConexao();
    $stmt = $pdo->prepare('SELECT * FROM registros WHERE strftime(\'%Y-%m\', data) = ? ORDER BY data ASC');
    $stmt->execute([$anoMes]);
    return $stmt->fetchAll();
}

function getSaldoMesSegundos(string $anoMes): int {
    $registros = getRegistrosMes($anoMes);
    $config    = getConfig();
    $saldo     = 0;

    foreach ($registros as $reg) {
        $saldo += calcularSaldoSegundos($reg, $config);
    }

    return $saldo;
}

function getUltimosDias(int $qtd = 7): array {
    $pdo  = getConexao();
    $stmt = $pdo->prepare('SELECT * FROM registros ORDER BY data DESC LIMIT ?');
    $stmt->execute([$qtd]);
    return array_reverse($stmt->fetchAll());
}

function getDiasFaltando(string $anoMes): array {
    $registros = getRegistrosMes($anoMes);
    $config    = getConfig();
    $faltando  = [];

    foreach ($registros as $reg) {
        $saldo = calcularSaldoSegundos($reg, $config);
        if ($saldo < 0) {
            $faltando[] = [
                'data'   => $reg['data'],
                'saldo'  => $saldo,
                'exibir' => segundosParaHora($saldo, true),
            ];
        }
    }

    return $faltando;
}

// ============================================
// FERIADOS
// ============================================

function isFeriado(string $data): bool {
    $pdo  = getConexao();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM feriados WHERE data = ?');
    $stmt->execute([$data]);
    return (bool) $stmt->fetchColumn();
}

function getNomeFeriado(string $data): string {
    $pdo  = getConexao();
    $stmt = $pdo->prepare('SELECT nome FROM feriados WHERE data = ?');
    $stmt->execute([$data]);
    return $stmt->fetchColumn() ?: '';
}

function getFeriadosMes(string $anoMes): array {
    $pdo  = getConexao();
    $stmt = $pdo->prepare('SELECT * FROM feriados WHERE strftime(\'%Y-%m\', data) = ?');
    $stmt->execute([$anoMes]);
    return $stmt->fetchAll();
}

// ============================================
// HELPERS DE DATA
// ============================================

function getDiaSemana(string $data): string {
    $dias = ['Domingo','Segunda','Terca','Quarta','Quinta','Sexta','Sabado'];
    return $dias[date('w', strtotime($data))];
}

function isHoje(string $data): bool {
    return $data === date('Y-m-d');
}

function formatarData(string $data): string {
    return date('d/m/Y', strtotime($data));
}

function getTipoDiaLabel(string $tipo): string {
    return match($tipo) {
        'normal'             => 'Normal',
        'feriado_trabalhado' => 'Feriado Trabalhado',
        'feriado_folga'      => 'Feriado',
        'abonado'            => 'Abonado',
        'folga'              => 'Folga',
        'falta'              => 'Falta',
        default              => $tipo,
    };
}

function getTipoDiaBadge(string $tipo): string {
    return match($tipo) {
        'feriado_trabalhado' => 'badge-warning',
        'feriado_folga'      => 'badge-info',
        'abonado'            => 'badge-success',
        'folga'              => 'badge-info',
        'falta'              => 'badge-danger',
        default              => 'badge-secondary',
    };
}

function getClasseSaldo(int $segundos): string {
    if ($segundos > 0)  return 'positivo';
    if ($segundos < 0)  return 'negativo';
    return 'neutro';
}
