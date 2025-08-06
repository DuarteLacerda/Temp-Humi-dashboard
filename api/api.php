<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sensores = ['temperatura', 'humidade'];


// ============ FUNÇÕES UTILITÁRIAS ============

function calcularMediasPorPeriodo($sensor, $formato) {
    $logPath = "$sensor/log.txt";
    if (!file_exists($logPath)) return [];

    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $agrupado = [];

    foreach ($lines as $line) {
        list($datetime, $valor) = explode(';', $line);
        $chave = date($formato, strtotime(str_replace('/', '-', $datetime)));

        if (!isset($agrupado[$chave])) $agrupado[$chave] = ['soma' => 0, 'count' => 0];
        $agrupado[$chave]['soma'] += intval($valor);
        $agrupado[$chave]['count']++;
    }

    $medias = [];
    foreach ($agrupado as $chave => $info) {
        $medias[$chave] = $info['count'] ? $info['soma'] / $info['count'] : 0;
    }

    return $medias;
}

function guardarMedia($ficheiro, $chave, $valores) {
    if (count($valores) === 0) return;
    $media = array_sum($valores) / count($valores);
    $linha = "$chave;" . intval($media);
    file_put_contents($ficheiro, $linha . "\n", FILE_APPEND);
}

// === Responder a pedidos AJAX de médias JSON ===

if (isset($_GET['media'], $_GET['tipo'])) {
    $sensor = $_GET['media']; // 'temperatura' ou 'humidade'
    $tipo = $_GET['tipo'];    // 'horaria', 'diaria', 'mensal'

    // Validar input
    if (!in_array($sensor, ['temperatura', 'humidade']) || !in_array($tipo, ['horaria', 'diaria', 'mensal'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Parâmetros inválidos']);
        exit;
    }

    // Determinar formato para calcular médias
    switch ($tipo) {
        case 'horaria':
            $formato = 'Y-m-d H'; // Ano-mês-dia hora para média horária
            break;
        case 'diaria':
            $formato = 'Y-m-d';   // Ano-mês-dia para média diária
            break;
        case 'mensal':
            $formato = 'Y-m';     // Ano-mês para média mensal
            break;
    }

    $medias = calcularMediasPorPeriodo($sensor, $formato);

    // Para médias horárias, o timestamp inclui hora, para as outras não
    // Opcional: ordenar as chaves (datas)
    ksort($medias);

    // Retornar JSON com médias arredondadas
    $mediasInt = array_map('intval', $medias);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($mediasInt);
    exit;
}

// ============ EXPORTAÇÃO DE FICHEIROS DE REGISTO E MÉDIAS ============

if (isset($_GET['export'])) {
    $tipo = $_GET['export'];
    $output = '';
    
    if ($tipo === 'registos') {
        $logTemps = file_exists('temperatura/log.txt') ? file('temperatura/log.txt', FILE_IGNORE_NEW_LINES) : [];
        $logHums = file_exists('humidade/log.txt') ? file('humidade/log.txt', FILE_IGNORE_NEW_LINES) : [];

        $temps = [];
        foreach ($logTemps as $line) {
            list($ts, $v) = explode(';', $line);
            $temps[$ts] = intval($v);
        }

        $hums = [];
        foreach ($logHums as $line) {
            list($ts, $v) = explode(';', $line);
            $hums[$ts] = intval($v);
        }

        $todos_ts = array_unique(array_merge(array_keys($temps), array_keys($hums)));
        sort($todos_ts);

        $output = "Timestamp;Temperatura;Humidade\n";
        foreach ($todos_ts as $ts) {
            $output .= "$ts;" . ($temps[$ts] ?? '-') . ";" . ($hums[$ts] ?? '-') . "\n";
        }

        header('Content-Disposition: attachment; filename="registos_horarios.txt"');
    }

    elseif ($tipo === 'diario' || $tipo === 'mensal') {
        $formato = $tipo === 'diario' ? 'Y-m-d' : 'Y-m';
        $mediasTemp = calcularMediasPorPeriodo('temperatura', $formato);
        $mediasHum = calcularMediasPorPeriodo('humidade', $formato);

        $datas = array_unique(array_merge(array_keys($mediasTemp), array_keys($mediasHum)));
        sort($datas);

        $titulo = $tipo === 'diario' ? 'Data' : 'Mês';
        $output = "$titulo;Temperatura;Humidade\n";
        foreach ($datas as $d) {
            $t = isset($mediasTemp[$d]) ? intval($mediasTemp[$d]) : '-';
            $h = isset($mediasHum[$d]) ? intval($mediasHum[$d]) : '-';
            $output .= "$d;$t;$h\n";
        }

        $filename = $tipo === 'diario' ? 'medias_diarias.txt' : 'medias_mensais.txt';
        header("Content-Disposition: attachment; filename=\"$filename\"");
    }

    else {
        http_response_code(400);
        die("Erro: parâmetro export inválido.");
    }

    header('Content-Type: text/plain');
    echo $output;
    exit;
}


// ============ DOWNLOAD DIRETO DOS FICHEIROS DE MÉDIA ============

if (isset($_GET['ficheiro'])) {
    $sensor = $_GET['sensor'] ?? '';
    $tipo = $_GET['ficheiro'];

    $valido = in_array($sensor, ['temperatura', 'humidade']) && in_array($tipo, ['media_horaria', 'media_diaria', 'media_mes']);
    if (!$valido) {
        http_response_code(400);
        die("Parâmetros inválidos.");
    }

    $path = "$sensor/$tipo.txt";

    if (!file_exists($path)) {
        http_response_code(404);
        die("Ficheiro não encontrado.");
    }

    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$tipo.txt\"");
    readfile($path);
    exit;
}



// ============ POST PARA REGISTO COM CÁLCULO DE MÉDIAS ============

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['nome'], $_POST['valor'])) {
        http_response_code(400);
        die("<p><strong>Erro:</strong> Parâmetros 'nome' e 'valor' são obrigatórios.</p>");
    }

    $nome = $_POST['nome'];
    $valor = intval($_POST['valor']);
    $hora = date('Y/m/d H:i:s');
    $dataAtual = date('Y-m-d');
    $anoMesAtual = date('Y-m');
    $horaAtual = date('Y-m-d H:00');

    if (!file_exists($nome)) mkdir($nome, 0777, true);

    $logPath = "$nome/log.txt";
    $mediaDiaPath = "$nome/media_diaria.txt";
    $mediaMesPath = "$nome/media_mes.txt";
    $mediaHoraPath = "$nome/media_horaria.txt";

    $linhasLog = file_exists($logPath) ? file($logPath, FILE_IGNORE_NEW_LINES) : [];

    $ultimoDiaLog = null;
    if (!empty($linhasLog)) {
        list($dt, ) = explode(';', end($linhasLog));
        $ultimoDiaLog = substr(str_replace('/', '-', $dt), 0, 10);
    }

    // ============ RESET MÉDIA HORÁRIA DIARIAMENTE ============
    if (!file_exists($mediaHoraPath) || substr(filemtime($mediaHoraPath), 0, 10) !== $dataAtual) {
        file_put_contents($mediaHoraPath, '');
    }
    file_put_contents($mediaHoraPath, "$horaAtual;$valor\n", FILE_APPEND);

    // ============ GUARDAR MÉDIA DIÁRIA NO FIM DO DIA ============

    if ($ultimoDiaLog !== null && $ultimoDiaLog !== $dataAtual) {
        // Reset do ficheiro de médias diárias se mudou o mês
        if (!file_exists($mediaDiaPath) || substr($ultimoDiaLog, 0, 7) !== $anoMesAtual) {
            file_put_contents($mediaDiaPath, '');
        }

        $valoresDia = array_filter($linhasLog, fn($l) => substr(str_replace('/', '-', explode(';', $l)[0]), 0, 10) === $ultimoDiaLog);
        $valoresNum = array_map(fn($l) => intval(explode(';', $l)[1]), $valoresDia);
        guardarMedia($mediaDiaPath, $ultimoDiaLog, $valoresNum);

        // ============ GUARDAR MÉDIA MENSAL NO FIM DO MÊS ============

        // Reset do ficheiro de médias mensais se mudou o ano
        if (!file_exists($mediaMesPath) || date('Y', filemtime($mediaMesPath)) !== date('Y')) {
            file_put_contents($mediaMesPath, '');
        }

        $anoMesAnterior = substr($ultimoDiaLog, 0, 7);
        $mediasMes = [];

        if (file_exists($mediaDiaPath)) {
            $linhasMedias = file($mediaDiaPath, FILE_IGNORE_NEW_LINES);
            foreach ($linhasMedias as $linha) {
                list($dia, $media) = explode(';', $linha);
                if (strpos($dia, $anoMesAnterior) === 0) {
                    $mediasMes[] = intval($media);
                }
            }
        }
        guardarMedia($mediaMesPath, $anoMesAnterior, $mediasMes);
    }

    // ============ GUARDAR VALOR ATUAL E REGISTO NO LOG ============

    $valorAnterior = file_exists("$nome/valor.txt") ? trim(file_get_contents("$nome/valor.txt")) : null;
    if ($valorAnterior !== strval($valor)) {
        file_put_contents("$nome/valor.txt", $valor);
        file_put_contents("$nome/hora.txt", $hora);
        file_put_contents("$nome/nome.txt", $nome);

        if (count($linhasLog) >= 25) array_shift($linhasLog);
        $linhasLog[] = "$hora;$valor";
        file_put_contents($logPath, implode("\n", $linhasLog) . "\n");
    }

    http_response_code(200);
    die("<p><strong>Sucesso:</strong> Dados de '$nome' atualizados.</p>");
}


// ============ GET PARA DASHBOARD (VALORES ATUAIS) ============

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get']) && $_GET['get'] == 1) {
    header('Content-Type: application/json; charset=utf-8');
    $res = [];

    foreach ($sensores as $s) {
        $path = "$s/valor.txt";
        if (file_exists($path)) $res[$s] = trim(file_get_contents($path));
    }

    echo json_encode($res);
    exit;
}