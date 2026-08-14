<?php
/**
 * Ramais - cadastro simples de RAMAL | NOME
 * Armazena tudo em data/ramais.json (nao precisa de banco de dados)
 */

session_start();
$logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;

$dataFile = __DIR__ . '/data/ramais.json';

// garante que a pasta/arquivo de dados existe
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0775, true);
}
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

function carregarRamais($dataFile) {
    $conteudo = file_get_contents($dataFile);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarRamais($dataFile, $dados) {
    file_put_contents($dataFile, json_encode(array_values($dados), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$ramais = carregarRamais($dataFile);
$erro = '';
$sucesso = '';

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

  <div class="card">
    <form class="busca" method="get">
      <input type="text" name="busca" placeholder="🔍 Buscar por ramal, nome, cargo, setor ou e-mail..."
             value="<?= htmlspecialchars($busca) ?>"
             onchange="this.form.submit()">
    </form>

    <?php if (empty($ramais)): ?>
      <div class="vazio">Nenhum ramal cadastrado ainda.</div>
    <?php else: ?>
      <?php foreach ($grupos as $setor => $lista): ?>
      <div class="grupo">
        <h3 class="setor-titulo<?= $setor === 'Sem setor' ? ' sem-setor' : '' ?>">
          <span><?= htmlspecialchars($setor) ?></span>
          <span class="count"><?= count($lista) ?> <?= count($lista) === 1 ? 'ramal' : 'ramais' ?></span>
        </h3>
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
                <td class="ramal-col"><?= htmlspecialchars($r['ramal']) ?></td>
                <td>
                  <div class="nome"><?= htmlspecialchars($r['nome']) ?></div>
                  <?php if (!empty($r['cargo'])): ?>
                    <div class="cargo"><?= htmlspecialchars($r['cargo']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($r['email'])): ?>
                    <a class="email" href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a>
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
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <footer>Total de ramais: <?= count($ramais) ?></footer>
</div>

</body>
</html>
