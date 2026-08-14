<?php
/**
 * Ramais - cadastro simples de RAMAL | NOME
 * Armazena tudo em data/ramais.json (nao precisa de banco de dados)
 */

session_start();
$logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;

// compatibilidade: funciona mesmo sem a extensao mbstring ou em PHP < 8
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($string) {
        return strtolower($string);
    }
}

$dataFile = __DIR__ . '/data/ramais.json';

// garante que a pasta/arquivo de dados existe
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0775, true);
}
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$buscasFile = __DIR__ . '/data/buscas.json';
if (!file_exists($buscasFile)) {
    file_put_contents($buscasFile, json_encode([]));
}

function carregarRamais($dataFile) {
    $conteudo = file_get_contents($dataFile);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarRamais($dataFile, $dados) {
    file_put_contents($dataFile, json_encode(array_values($dados), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function destacar($texto, $termo) {
    $texto = htmlspecialchars((string)$texto);
    $termo = trim((string)$termo);
    if ($termo === '') return $texto;
    $esc = htmlspecialchars($termo);
    $r = @preg_replace('/(' . preg_quote($esc, '/') . ')/iu', '<mark>$1</mark>', $texto);
    return is_string($r) ? $r : $texto;
}

function zip_armazenar($arquivos) {
    $dados = '';
    $central = '';
    $offset = 0;
    foreach ($arquivos as $nome => $conteudo) {
        $crc = crc32($conteudo);
        $tam = strlen($conteudo);
        $len = strlen($nome);
        $dados .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $tam, $tam, $len, 0) . $nome . $conteudo;
        $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $tam, $tam, $len, 0, 0, 0, 0, 0, $offset) . $nome;
        $offset += 30 + $len + $tam;
    }
    $n = count($arquivos);
    return $dados . $central . pack('VvvvvVVv', 0x06054b50, 0, 0, $n, $n, strlen($central), $offset, 0);
}

$ramais = carregarRamais($dataFile);
$erro = '';
$sucesso = '';

// ---------- EXPORTAÇÃO XLSX (lista completa, formatada) ----------
if (isset($_GET['exportar']) && $_GET['exportar'] === 'xlsx') {
    $todos = carregarRamais($dataFile);
    usort($todos, function ($a, $b) {
        $sa = mb_strtolower($a['setor'] ?? '');
        $sb = mb_strtolower($b['setor'] ?? '');
        if ($sa !== $sb) return strcmp($sa, $sb);
        return strnatcmp($a['ramal'], $b['ramal']);
    });

    $cabecalhos = ['Ramal', 'Nome', 'Cargo', 'Setor', 'E-mail'];
    $larguras = [10, 32, 28, 26, 38];

    $linhas = '<row r="1">';
    foreach ($cabecalhos as $i => $c) {
        $linhas .= '<c r="' . chr(65 + $i) . '1" t="inlineStr" s="1"><is><t>'
            . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . '</t></is></c>';
    }
    $linhas .= '</row>';
    $linha = 2;
    foreach ($todos as $r) {
        $valores = [$r['ramal'] ?? '', $r['nome'] ?? '', $r['cargo'] ?? '', $r['setor'] ?? '', $r['email'] ?? ''];
        $linhas .= '<row r="' . $linha . '">';
        foreach ($valores as $i => $v) {
            $linhas .= '<c r="' . chr(65 + $i) . $linha . '" t="inlineStr" s="2"><is><t>'
                . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '</t></is></c>';
        }
        $linhas .= '</row>';
        $linha++;
    }

    $cols = '<cols>';
    foreach ($larguras as $i => $w) {
        $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
    }
    $cols .= '</cols>';

    $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . $cols . '<sheetData>' . $linhas . '</sheetData>'
        . '<autoFilter ref="A1:E' . ($linha - 1) . '"/>'
        . '</worksheet>';

    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="2">'
        . '<font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
        . '</fonts>'
        . '<fills count="3">'
        . '<fill><patternFill patternType="none"/></fill>'
        . '<fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/><bgColor indexed="64"/></patternFill></fill>'
        . '</fills>'
        . '<borders count="2">'
        . '<border><left/><right/><top/><bottom/><diagonal/></border>'
        . '<border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right>'
        . '<top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border>'
        . '</borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="3">'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
        . '</cellXfs>'
        . '</styleSheet>';

    $arquivos = [
        '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>',
        '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>',
        'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Ramais" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>',
        'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>',
        'xl/worksheets/sheet1.xml' => $sheet,
        'xl/styles.xml' => $styles,
    ];

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="ramais.xlsx"');
    echo zip_armazenar($arquivos);
    exit;
}

// ---------- AÇÕES (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$logado) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $ramal = trim($_POST['ramal'] ?? '');
        $nome  = trim($_POST['nome'] ?? '');
        $setor = trim($_POST['setor'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $idEdicao = trim($_POST['id_edicao'] ?? '');

        if ($ramal === '' || $nome === '') {
            $erro = 'Preencha o RAMAL e o NOME.';
        } else {
            // verifica duplicidade de ramal (ignorando o proprio registro em edicao)
            $duplicado = false;
            foreach ($ramais as $r) {
                if ($r['ramal'] === $ramal && $r['id'] !== $idEdicao) {
                    $duplicado = true;
                    break;
                }
            }

            if ($duplicado) {
                $erro = "O ramal $ramal já está cadastrado para outra pessoa.";
            } else {
                if ($idEdicao !== '') {
                    // edicao
                    foreach ($ramais as &$r) {
                        if ($r['id'] === $idEdicao) {
                            $r['ramal'] = $ramal;
                            $r['nome'] = $nome;
                            $r['setor'] = $setor;
                            $r['cargo'] = $cargo;
                            $r['email'] = $email;
                        }
                    }
                    unset($r);
                    $sucesso = 'Ramal atualizado com sucesso.';
                } else {
                    // novo
                    $ramais[] = [
                        'id' => uniqid(),
                        'ramal' => $ramal,
                        'nome' => $nome,
                        'setor' => $setor,
                        'cargo' => $cargo,
                        'email' => $email,
                    ];
                    $sucesso = 'Ramal cadastrado com sucesso.';
                }
                salvarRamais($dataFile, $ramais);
                $ramais = carregarRamais($dataFile);
            }
        }
    }

    if ($acao === 'excluir') {
        $id = $_POST['id'] ?? '';
        $ramais = array_filter($ramais, fn($r) => $r['id'] !== $id);
        salvarRamais($dataFile, $ramais);
        $ramais = carregarRamais($dataFile);
        $sucesso = 'Ramal excluído.';
    }
}

// ---------- EDIÇÃO (GET) ----------
$emEdicao = null;
if (isset($_GET['editar'])) {
    foreach ($ramais as $r) {
        if ($r['id'] === $_GET['editar']) {
            $emEdicao = $r;
            break;
        }
    }
}

// ---------- BUSCA ----------
$busca = trim($_GET['busca'] ?? '');
if ($busca !== '') {
    $buscaLower = mb_strtolower($busca);
    $ramais = array_filter($ramais, function ($r) use ($buscaLower) {
        return str_contains(mb_strtolower($r['ramal']), $buscaLower)
            || str_contains(mb_strtolower($r['nome']), $buscaLower)
            || str_contains(mb_strtolower($r['setor'] ?? ''), $buscaLower)
            || str_contains(mb_strtolower($r['cargo'] ?? ''), $buscaLower)
            || str_contains(mb_strtolower($r['email'] ?? ''), $buscaLower);
    });

    // registra a pesquisa feita (para o admin ver depois)
    if (!$logado) {
        $buscas = json_decode((string)@file_get_contents($buscasFile), true);
        if (!is_array($buscas)) $buscas = [];
        $ultimo = end($buscas);
        $jaLogada = $ultimo && ($ultimo['termo'] ?? '') === $busca
            && (time() - strtotime((string)($ultimo['quando'] ?? '')) < 300);
        if (!$jaLogada) {
            $buscas[] = ['termo' => $busca, 'quando' => date('Y-m-d H:i:s')];
            $buscas = array_slice($buscas, -100);
            file_put_contents($buscasFile, json_encode($buscas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

// ordena por setor e depois por ramal
usort($ramais, function ($a, $b) {
    $sa = mb_strtolower($a['setor'] ?? '');
    $sb = mb_strtolower($b['setor'] ?? '');
    if ($sa !== $sb) return strcmp($sa, $sb);
    return strnatcmp($a['ramal'], $b['ramal']);
});

// agrupa por setor para exibicao
$grupos = [];
foreach ($ramais as $r) {
    $setor = trim($r['setor'] ?? '');
    if ($setor === '') $setor = 'Sem setor';
    $grupos[$setor][] = $r;
}

// lista de setores existentes (para o datalist do formulario)
$setoresExistentes = [];
foreach (carregarRamais($dataFile) as $r) {
    $s = trim($r['setor'] ?? '');
    if ($s !== '' && !in_array($s, $setoresExistentes)) $setoresExistentes[] = $s;
}
sort($setoresExistentes, SORT_STRING | SORT_FLAG_CASE);

// ---------- IMPRESSÃO / PDF (lista completa, estilo Excel por setor) ----------
if (isset($_GET['imprimir'])) {
    $todos = carregarRamais($dataFile);
    usort($todos, function ($a, $b) {
        $sa = mb_strtolower($a['setor'] ?? '');
        $sb = mb_strtolower($b['setor'] ?? '');
        if ($sa !== $sb) return strcmp($sa, $sb);
        return strnatcmp($a['ramal'], $b['ramal']);
    });
    $metade = (int)ceil(count($todos) / 2);
    $blocos = [array_slice($todos, 0, $metade), array_slice($todos, $metade)];
    $total = count($todos);

    function blocoImpressao($lista) {
        $setorAtual = null;
        foreach ($lista as $r) {
            $setor = trim($r['setor'] ?? '');
            if ($setor === '') $setor = 'Sem setor';
            if ($setor !== $setorAtual) {
                echo '<tr class="setor-head"><td colspan="2">' . htmlspecialchars($setor) . '</td></tr>';
                $setorAtual = $setor;
            }
            echo '<tr>'
                . '<td class="ramal">' . htmlspecialchars($r['ramal'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($r['nome'] ?? '') . '</td>'
                . '</tr>';
        }
    }
    ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Ramais - lista completa</title>
<style>
  * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  @page { size: A4 portrait; margin: 8mm; }
  body { font-family: Arial, sans-serif; color: #000; margin: 0; font-size: 10px; }
  h1 { margin: 0 0 2px; font-size: 1.2rem; }
  .sub { color: #444; margin: 0 0 10px; font-size: .8rem; }
  .blocos { width: 100%; overflow: hidden; }
  .blocos table { width: 49%; float: left; border-collapse: collapse; }
  .blocos table + table { float: right; }
  th, td { border: 1px solid #999; padding: 2px 6px; text-align: left; }
  th { background: #444; color: #fff; font-weight: bold; }
  tr.setor-head td { background: #b0b0b0; font-weight: bold; }
  td.ramal { font-weight: bold; white-space: nowrap; }
  tr { page-break-inside: avoid; }
  .btn { margin-bottom: 10px; padding: 8px 14px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: .9rem; }
  @media print { .btn { display: none; } }
</style>
</head>
<body>
  <button class="btn" onclick="window.print()">Imprimir / salvar PDF</button>
  <h1>Lista de Ramais</h1>
  <p class="sub">Gerada em <?= date('d/m/Y H:i') ?> - Total de <?= $total ?> ramais</p>
  <div class="blocos">
    <table>
      <thead><tr><th>Ramal</th><th>Usuário</th></tr></thead>
      <tbody>
        <?php blocoImpressao($blocos[0]); ?>
      </tbody>
    </table>
    <?php if (!empty($blocos[1])): ?>
    <table>
      <thead><tr><th>Ramal</th><th>Usuário</th></tr></thead>
      <tbody>
        <?php blocoImpressao($blocos[1]); ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <script>window.print();</script>
</body>
</html>
    <?php
    exit;
}

// ---------- RESUMO DAS PESQUISAS (para quem esta logado) ----------
$buscasResumo = [];
if ($logado) {
    $buscas = json_decode((string)@file_get_contents($buscasFile), true);
    if (!is_array($buscas)) $buscas = [];
    foreach (array_reverse($buscas) as $b) {
        $t = mb_strtolower(trim($b['termo'] ?? ''));
        if ($t === '') continue;
        $quando = $b['quando'] ?? '';
        if (!isset($buscasResumo[$t])) {
            $buscasResumo[$t] = ['termo' => $b['termo'], 'qtd' => 0, 'ultima' => $quando];
        }
        $buscasResumo[$t]['qtd']++;
        if ($quando > $buscasResumo[$t]['ultima']) $buscasResumo[$t]['ultima'] = $quando;
    }
    usort($buscasResumo, function ($a, $b) {
        return strcmp($b['ultima'], $a['ultima']);
    });
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ramais</title>
<style>
  :root {
    --azul: #2563eb;
    --azul-escuro: #1e40af;
    --cinza: #f3f4f6;
    --cinza-borda: #e5e7eb;
    --texto: #1f2937;
    --vermelho: #dc2626;
    --verde: #16a34a;
  }
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    background: var(--cinza);
    color: var(--texto);
    margin: 0;
    padding: 0 16px 60px;
  }
  header {
    max-width: 720px;
    margin: 0 auto;
    padding: 32px 0 12px;
    text-align: center;
  }
  header h1 {
    font-size: 1.7rem;
    margin: 0;
    color: var(--azul-escuro);
  }
  header p {
    color: #6b7280;
    margin-top: 4px;
  }
  header .sessao {
    display: inline-block;
    margin-top: 10px;
    font-size: 0.85rem;
    color: var(--azul);
    text-decoration: none;
    font-weight: 600;
  }
  header .sessao:hover { text-decoration: underline; }
  .container {
    max-width: 720px;
    margin: 0 auto;
  }
  .card {
    background: #fff;
    border: 1px solid var(--cinza-borda);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
  }
  form.cadastro {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
  }
  .campo { display: flex; flex-direction: column; gap: 4px; }
  .campo label { font-size: 0.8rem; color: #6b7280; font-weight: 600; }
  .campo input {
    padding: 10px 12px;
    border: 1px solid var(--cinza-borda);
    border-radius: 8px;
    font-size: 1rem;
    min-width: 140px;
  }
  .campo input:focus { outline: 2px solid var(--azul); border-color: transparent; }
  #ramal { max-width: 100px; }
  button, .btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background: var(--azul);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.95rem;
    text-decoration: none;
    display: inline-block;
  }
  button:hover, .btn:hover { background: var(--azul-escuro); }
  .btn-cancelar { background: #9ca3af; }
  .btn-cancelar:hover { background: #6b7280; }
  .msg {
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 0.9rem;
  }
  .msg-erro { background: #fee2e2; color: var(--vermelho); }
  .msg-sucesso { background: #dcfce7; color: var(--verde); }
  .busca { margin-bottom: 12px; }
  .busca input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--cinza-borda);
    border-radius: 8px;
    font-size: 1rem;
  }
  table { width: 100%; border-collapse: collapse; }
  th, td {
    text-align: left;
    padding: 10px 8px;
    border-bottom: 1px solid var(--cinza-borda);
  }
  th { color: #6b7280; font-size: 0.8rem; text-transform: uppercase; letter-spacing: .03em; }
  td.ramal-col { font-weight: 700; color: var(--azul-escuro); width: 90px; }
  .nome { font-weight: 600; }
  .cargo { font-size: 0.8rem; color: #6b7280; }
  .email { color: var(--azul); text-decoration: none; font-size: 0.9rem; }
  .email:hover { text-decoration: underline; }
  .sem-email { color: #d1d5db; }
  .acoes { display: flex; gap: 8px; }
  .acoes a, .acoes button {
    font-size: 0.82rem;
    padding: 6px 10px;
    border-radius: 6px;
  }
  .acoes .editar { background: #fef3c7; color: #92400e; }
  .acoes .editar:hover { background: #fde68a; }
  .acoes .excluir { background: #fee2e2; color: var(--vermelho); border: none; }
  .acoes .excluir:hover { background: #fecaca; }
  .vazio { text-align: center; color: #9ca3af; padding: 24px 0; }
  .grupo {
    background: #fff;
    border: 1px solid var(--cinza-borda);
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
  }
  .setor-titulo {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0;
    padding: 12px 16px;
    background: linear-gradient(135deg, var(--azul), var(--azul-escuro));
    color: #fff;
    font-size: 1rem;
    letter-spacing: .02em;
  }
  .setor-titulo .count {
    background: rgba(255, 255, 255, .2);
    border-radius: 999px;
    padding: 2px 10px;
    font-size: .78rem;
    font-weight: 600;
    white-space: nowrap;
  }
  .setor-titulo .setor-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }
  .setor-titulo .setor-left .nome-setor {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .setor-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 0.9rem;
    padding: 2px 6px;
    border-radius: 6px;
    cursor: pointer;
    transition: transform .2s;
    opacity: .8;
  }
  .setor-btn:hover { background: rgba(255,255,255,.2); opacity: 1; }
  .setor-btn svg { display: block; }
  .grupo.minimizado .setor-btn { transform: rotate(-90deg); }
  .grupo .corpo { overflow: hidden; transition: max-height .25s ease; }
  .grupo.minimizado .corpo { max-height: 0 !important; }
  .setor-titulo.sem-setor { background: linear-gradient(135deg, #9ca3af, #6b7280); }
  .grupo th {
    color: #6b7280;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 10px 16px;
  }
  .grupo td { padding: 10px 16px; }
  .grupo tbody tr:hover { background: var(--cinza); }
  .grupo tbody tr:last-child td { border-bottom: none; }
  .aviso-login { text-align: center; color: #6b7280; font-size: 0.9rem; }
  .aviso-login a { color: var(--azul); font-weight: 600; text-decoration: none; }
  .aviso-login a:hover { text-decoration: underline; }
  mark {
    background: #fde68a;
    color: inherit;
    border-radius: 3px;
    padding: 0 2px;
  }
  .barra {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }
  .barra-esq, .barra-dir {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .btn-sec {
    background: #fff;
    color: var(--azul-escuro);
    border: 1px solid var(--cinza-borda);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
  }
  .btn-sec:hover { background: var(--cinza); }
  .card-titulo {
    margin: 0 0 12px;
    font-size: 1rem;
    color: var(--azul-escuro);
  }
  .tbl-pesquisas th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
  .tbl-pesquisas a { color: var(--azul); text-decoration: none; font-weight: 600; }
  .tbl-pesquisas a:hover { text-decoration: underline; }
  .tbl-pesquisas td.qtd { text-align: center; color: #6b7280; }
  footer { text-align: center; color: #9ca3af; font-size: 0.8rem; margin-top: 24px; }
</style>
</head>
<body>

<header>
  <h1>📞 Ramais</h1>
  <p>Cadastro de ramais e responsáveis</p>
  <?php if ($logado): ?>
    <a class="sessao" href="logout.php">Sair</a>
  <?php else: ?>
    <a class="sessao" href="login.php">Entrar para cadastrar</a>
  <?php endif; ?>
</header>

<div class="container">

  <?php if ($erro): ?>
    <div class="msg msg-erro"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>
  <?php if ($sucesso): ?>
    <div class="msg msg-sucesso"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <?php if ($logado): ?>
  <div class="card">
    <form class="cadastro" method="post">
      <input type="hidden" name="acao" value="salvar">
      <input type="hidden" name="id_edicao" value="<?= htmlspecialchars($emEdicao['id'] ?? '') ?>">
      <div class="campo">
        <label for="ramal">Ramal</label>
        <input type="text" id="ramal" name="ramal" required
               value="<?= htmlspecialchars($emEdicao['ramal'] ?? '') ?>" placeholder="Ex: 1234">
      </div>
      <div class="campo" style="flex:1">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" required
               value="<?= htmlspecialchars($emEdicao['nome'] ?? '') ?>" placeholder="Ex: João Silva">
      </div>
      <div class="campo" style="flex:1">
        <label for="cargo">Cargo</label>
        <input type="text" id="cargo" name="cargo"
               value="<?= htmlspecialchars($emEdicao['cargo'] ?? '') ?>" placeholder="Ex: Analista">
      </div>
      <div class="campo" style="flex:1">
        <label for="setor">Setor</label>
        <input type="text" id="setor" name="setor" list="lista-setores"
               value="<?= htmlspecialchars($emEdicao['setor'] ?? '') ?>" placeholder="Ex: Financeiro">
        <datalist id="lista-setores">
          <?php foreach ($setoresExistentes as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <div class="campo" style="flex:1">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($emEdicao['email'] ?? '') ?>" placeholder="Ex: joao@empresa.com">
      </div>
      <button type="submit"><?= $emEdicao ? 'Salvar alteração' : 'Cadastrar' ?></button>
      <?php if ($emEdicao): ?>
        <a class="btn btn-cancelar" href="index.php">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>
  <?php else: ?>
  <div class="card aviso-login">
    <p>Você está vendo a lista de ramais. Para cadastrar, editar ou excluir, <a href="login.php">faça login</a>.</p>
  </div>
  <?php endif; ?>

  <?php if ($logado && !empty($buscasResumo)): ?>
  <div class="card">
    <h3 class="card-titulo">Pesquisas dos visitantes</h3>
    <table class="tbl-pesquisas">
      <thead>
        <tr>
          <th>Pesquisa</th>
          <th>Vezes</th>
          <th>Última vez</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($buscasResumo as $b): ?>
          <tr>
            <td><a href="?busca=<?= urlencode($b['termo']) ?>"><?= htmlspecialchars($b['termo']) ?></a></td>
            <td class="qtd"><?= $b['qtd'] ?></td>
            <td><?= htmlspecialchars($b['ultima']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <div class="card">
    <form class="busca" method="get">
      <input type="text" name="busca" placeholder="🔍 Buscar por ramal, nome, cargo, setor ou e-mail..."
             value="<?= htmlspecialchars($busca) ?>"
             onchange="this.form.submit()">
    </form>

    <div class="barra">
      <div class="barra-esq">
        <button type="button" class="btn-sec" id="minTodos">Minimizar todos</button>
        <button type="button" class="btn-sec" id="expTodos">Expandir todos</button>
      </div>
      <div class="barra-dir">
        <a class="btn-sec" href="?exportar=xlsx">Baixar Excel</a>
        <a class="btn-sec" href="?imprimir=1" target="_blank">Imprimir / PDF</a>
      </div>
    </div>

    <?php if (empty($ramais)): ?>
      <div class="vazio">Nenhum ramal cadastrado ainda.</div>
    <?php else: ?>
      <?php foreach ($grupos as $setor => $lista): ?>
      <div class="grupo" data-setor="<?= htmlspecialchars($setor) ?>">
        <h3 class="setor-titulo<?= $setor === 'Sem setor' ? ' sem-setor' : '' ?>">
          <span class="setor-left">
            <button type="button" class="setor-btn" title="Minimizar/expandir setor" aria-expanded="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <span class="nome-setor"><?= destacar($setor, $busca) ?></span>
          </span>
          <span class="count"><?= count($lista) ?> <?= count($lista) === 1 ? 'ramal' : 'ramais' ?></span>
        </h3>
        <div class="corpo">
        <table>
          <thead>
            <tr>
              <th>Ramal</th>
              <th>Nome</th>
              <th>E-mail</th>
              <?php if ($logado): ?><th></th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lista as $r): ?>
              <tr>
                <td class="ramal-col"><?= destacar($r['ramal'], $busca) ?></td>
                <td>
                  <div class="nome"><?= destacar($r['nome'], $busca) ?></div>
                  <?php if (!empty($r['cargo'])): ?>
                    <div class="cargo"><?= destacar($r['cargo'], $busca) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($r['email'])): ?>
                    <a class="email" href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= destacar($r['email'], $busca) ?></a>
                  <?php else: ?>
                    <span class="sem-email">—</span>
                  <?php endif; ?>
                </td>
                <?php if ($logado): ?>
                <td class="acoes">
                  <a class="editar" href="?editar=<?= urlencode($r['id']) ?>">Editar</a>
                  <form method="post" onsubmit="return confirm('Excluir o ramal <?= htmlspecialchars($r['ramal']) ?>?');" style="display:inline">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($r['id']) ?>">
                    <button type="submit" class="excluir">Excluir</button>
                  </form>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <footer>Total de ramais: <?= count($ramais) ?></footer>
</div>

<script>
(function () {
  var chave = 'setoresMinimizados';
  var minimizados = [];
  var inputBusca = document.querySelector('.busca input');
  var buscaAtiva = inputBusca ? inputBusca.value.trim() !== '' : false;
  try {
    minimizados = JSON.parse(localStorage.getItem(chave) || '[]');
  } catch (e) {}

  var grupos = Array.prototype.slice.call(document.querySelectorAll('.grupo'));

  function salvar() {
    try { localStorage.setItem(chave, JSON.stringify(minimizados)); } catch (e) {}
  }

  function aplicar(grupo, min) {
    var corpo = grupo.querySelector('.corpo');
    var btn = grupo.querySelector('.setor-btn');
    grupo.classList.toggle('minimizado', min);
    if (btn) btn.setAttribute('aria-expanded', min ? 'false' : 'true');
    if (corpo) corpo.style.maxHeight = min ? '0px' : corpo.scrollHeight + 'px';
  }

  grupos.forEach(function (grupo) {
    var setor = grupo.getAttribute('data-setor');
    var btn = grupo.querySelector('.setor-btn');

    if (!buscaAtiva && minimizados.indexOf(setor) !== -1) {
      aplicar(grupo, true);
    } else {
      aplicar(grupo, false);
    }

    if (btn) btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var min = !grupo.classList.contains('minimizado');
      aplicar(grupo, min);
      var idx = minimizados.indexOf(setor);
      if (min && idx === -1) minimizados.push(setor);
      if (!min && idx !== -1) minimizados.splice(idx, 1);
      salvar();
    });
  });

  var minTodos = document.getElementById('minTodos');
  var expTodos = document.getElementById('expTodos');
  if (minTodos) minTodos.addEventListener('click', function () {
    minimizados = grupos.map(function (g) { return g.getAttribute('data-setor'); });
    salvar();
    grupos.forEach(function (g) { aplicar(g, true); });
  });
  if (expTodos) expTodos.addEventListener('click', function () {
    minimizados = [];
    salvar();
    grupos.forEach(function (g) { aplicar(g, false); });
  });
})();
</script>

</body>
</html>
