<?php
require_once __DIR__ . '/../includes/functions.php';
$hoje = date('Y-m-d');
$registro = getRegistroDia($hoje);
echo tempoTrabalhadoHoje($registro);
