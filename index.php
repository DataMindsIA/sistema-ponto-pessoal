<?php require 'includes/functions.php'; $hoje = date('Y-m-d'); $registro = getRegistroDia($hoje); $config = getConfig(); $proxima_acao = getProximaAcao($registro); $saldo_hoje = calcularSaldoSegundos($registro, $config); $tempo_trab = tempoTrabalhadoHoje($registro); $ultimos = getUltimosDias(5); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponto - <?= $config['nome_usuario'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { font-size: 1.5em; margin-bottom: 5px; }
        .data { font-size: 0.9em; opacity: 0.9; margin-bottom: 15px; }
        .saldo-card { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 15px; margin: 15px 0; }
        .saldo { font-size: 2.5em; font-weight: bold; margin: 10px 0; }
        .saldo.positivo { color: #4CAF50; }
        .saldo.negativo { color: #ff5252; }
        .saldo.neutro { color: #FFC107; }
        .timer { font-size: 1.3em; opacity: 0.95; }
        .content { padding: 25px; }
        .btn { display: block; width: 100%; padding: 18px; margin: 12px 0; border: none; border-radius: 12px; font-size: 1.2em; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .btn:active { transform: translateY(0); }
        .btn-entrada { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
        .btn-saida-alm { background: linear-gradient(135deg, #f2994a, #f2c94c); color: white; }
        .btn-volta-alm { background: linear-gradient(135deg, #2193b0, #6dd5ed); color: white; }
        .btn-saida { background: linear-gradient(135deg, #ee0979, #ff6a00); color: white; }
        .btn-completo { background: linear-gradient(135deg, #56ab2f, #a8e063); color: white; cursor: default; }
        .btn-completo:hover { transform: none; }
        .btn-secondary { background: #f5f5f5; color: #333; font-size: 1em; padding: 15px; }
        .historico { background: #f8f9fa; padding: 20px; border-radius: 15px; margin-top: 15px; }
        .historico h3 { font-size: 1.1em; margin-bottom: 15px; color: #333; }
        .dia-item { background: white; padding: 15px; margin: 10px 0; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .dia-data { font-weight: 600; color: #555; }
        .dia-saldo { font-weight: 700; font-size: 1.1em; }
        .nav-links { display: flex; gap: 10px; margin-top: 15px; }
        .nav-links a { flex: 1; text-align: center; padding: 12px; background: #f5f5f5; text-decoration: none; color: #555; border-radius: 10px; font-weight: 600; transition: all 0.3s; }
        .nav-links a:hover { background: #e0e0e0; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75em; font-weight: 600; margin-left: 8px; }
        .badge-feriado { background: #ff9800; color: white; }
        .pontos-dia { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 15px 0; padding: 15px; background: #f9f9f9; border-radius: 10px; font-size: 0.9em; }
        .ponto-item { display: flex; flex-direction: column; }
        .ponto-label { color: #666; font-size: 0.85em; margin-bottom: 3px; }
        .ponto-hora { font-weight: 700; color: #333; font-size: 1.1em; }
        .ponto-vazio { color: #ccc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 <?= htmlspecialchars($config['nome_usuario']) ?> - Ponto</h1>
            <div class="data"><?= strftime('%A, %d de %B de %Y', strtotime($hoje)) ?></div>
            
            <div class="saldo-card">
                <div>Tempo Trabalhado Hoje</div>
                <div class="timer" id="timer"><?= $tempo_trab ?></div>
                <div style="margin-top: 10px; font-size: 0.9em;">Saldo do Dia</div>
                <div class="saldo <?= getClasseSaldo($saldo_hoje) ?>"><?= segundosParaHora($saldo_hoje, true) ?></div>
            </div>
        </div>
        
        <div class="content">
            <?php if ($proxima_acao !== 'completo'): ?>
                <button class="btn btn-<?= $proxima_acao ?>" onclick="registrar('<?= $proxima_acao ?>')">
                    <?= getLabelAcao($proxima_acao) ?> (<?= date('H:i') ?>)
                </button>
            <?php else: ?>
                <button class="btn btn-completo">✅ Dia Completo!</button>
            <?php endif; ?>
            
            <div class="pontos-dia">
                <div class="ponto-item">
                    <span class="ponto-label">🟢 Entrada</span>
                    <span class="ponto-hora <?= !$registro['entrada'] ? 'ponto-vazio' : '' ?>">
                        <?= $registro['entrada'] ? substr($registro['entrada'], 0, 5) : '--:--' ?>
                    </span>
                </div>
                <div class="ponto-item">
                    <span class="ponto-label">🟡 Saida Almoco</span>
                    <span class="ponto-hora <?= !$registro['saida_almoco'] ? 'ponto-vazio' : '' ?>">
                        <?= $registro['saida_almoco'] ? substr($registro['saida_almoco'], 0, 5) : '--:--' ?>
                    </span>
                </div>
                <div class="ponto-item">
                    <span class="ponto-label">🔵 Volta Almoco</span>
                    <span class="ponto-hora <?= !$registro['volta_almoco'] ? 'ponto-vazio' : '' ?>">
                        <?= $registro['volta_almoco'] ? substr($registro['volta_almoco'], 0, 5) : '--:--' ?>
                    </span>
                </div>
                <div class="ponto-item">
                    <span class="ponto-label">🔴 Saida</span>
                    <span class="ponto-hora <?= !$registro['saida'] ? 'ponto-vazio' : '' ?>">
                        <?= $registro['saida'] ? substr($registro['saida'], 0, 5) : '--:--' ?>
                    </span>
                </div>
            </div>
            
            <button class="btn btn-secondary" onclick="toggleTipoDia()">
                Tipo do Dia: <?= getTipoDiaLabel($registro['tipo_dia']) ?>
            </button>
            
            <div class="nav-links">
                <a href="relatorio.php">📈 Relatorios</a>
                <a href="editar.php">✏️ Editar</a>
                <a href="config.php">⚙️ Config</a>
            </div>
            
            <div class="historico">
                <h3>📅 Ultimos Dias</h3>
                <?php foreach($ultimos as $dia): 
                    if ($dia['data'] === $hoje) continue;
                    $saldo_dia = calcularSaldoSegundos($dia, $config);
                ?>
                    <div class="dia-item">
                        <div>
                            <span class="dia-data"><?= formatarData($dia['data']) ?></span>
                            <?php if (isFeriado($dia['data'])): ?>
                                <span class="badge badge-feriado"><?= getNomeFeriado($dia['data']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dia-saldo <?= getClasseSaldo($saldo_dia) ?>">
                            <?= segundosParaHora($saldo_dia, true) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        function registrar(acao) {
            fetch('api/registrar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({acao: acao})
            })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    location.reload();
                } else {
                    alert(data.mensagem || 'Erro ao registrar ponto');
                }
            })
            .catch(err => alert('Erro de conexao: ' + err));
        }
        
        function toggleTipoDia() {
            const tipos = ['normal', 'feriado_trabalhado', 'feriado_folga', 'abonado', 'folga', 'falta'];
            const tipoAtual = '<?= $registro['tipo_dia'] ?>';
            const idx = tipos.indexOf(tipoAtual);
            const novoTipo = tipos[(idx + 1) % tipos.length];
            
            fetch('api/atualizar_tipo.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({data: '<?= $hoje ?>', tipo: novoTipo})
            })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) location.reload();
            });
        }
        
        // Atualizar timer a cada segundo
        setInterval(() => {
            fetch('api/tempo_hoje.php')
                .then(r => r.text())
                .then(html => document.getElementById('timer').textContent = html);
        }, 1000);
        
        // Atualizar relogio no botao
        setInterval(() => {
            const now = new Date();
            const time = now.getHours().toString().padStart(2,'0') + ':' + 
                         now.getMinutes().toString().padStart(2,'0');
            document.querySelectorAll('.btn').forEach(btn => {
                btn.innerHTML = btn.innerHTML.replace(/\d{2}:\d{2}/, time);
            });
        }, 1000);
    </script>
</body>
</html>
