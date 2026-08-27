<?php
require_once __DIR__ . '/auth.php';
iniciarSessaoSeNecessario();
garantirUsuarios();
requerLogin();

$usuarioLogado = usuarioLogado();
$perfilLogado = perfilLogado();
$isAdmin = ehAdmin();

$erro = '';
$sucesso = '';

// Para não-admin, só permite trocar a própria senha. Redireciona se tentar acessar com ?editar de outro.
$usuarios = carregarUsuarios();

// Helpers para encontrar usuário atual completo
$meuUsuario = null;
foreach ($usuarios as $u) {
    if (($u['usuario'] ?? '') === $usuarioLogado) { $meuUsuario = $u; break; }
}

// Processa POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // Trocar própria senha (qualquer logado)
    if ($acao === 'trocar_minha_senha') {
        $senhaAtual = (string)($_POST['senha_atual'] ?? '');
        $novaSenha = (string)($_POST['nova_senha'] ?? '');
        $confirma = (string)($_POST['confirma_senha'] ?? '');

        if ($novaSenha === '' || $confirma === '') {
            $erro = 'Preencha a nova senha e a confirmação.';
        } elseif ($novaSenha !== $confirma) {
            $erro = 'A nova senha e a confirmação não coincidem.';
        } elseif (strlen($novaSenha) < 4) {
            $erro = 'A nova senha deve ter pelo menos 4 caracteres.';
        } else {
            // se não for admin trocando de outro, exige senha atual; admin trocando própria também exige
            $hashAtual = $meuUsuario['senha_hash'] ?? '';
            if ($hashAtual === '' || !password_verify($senhaAtual, $hashAtual)) {
                $erro = 'Senha atual incorreta.';
            } else {
                foreach ($usuarios as &$u) {
                    if (($u['id'] ?? '') === ($meuUsuario['id'] ?? '')) {
                        $u['senha_hash'] = password_hash($novaSenha, PASSWORD_DEFAULT);
                        break;
                    }
                }
                unset($u);
                salvarUsuarios($usuarios);
                $sucesso = 'Sua senha foi alterada com sucesso.';
                // recarrega
                $usuarios = carregarUsuarios();
                foreach ($usuarios as $u) if (($u['usuario'] ?? '') === $usuarioLogado) { $meuUsuario = $u; break; }
            }
        }
    }

    // Ações exclusivas de admin
    if ($isAdmin) {
        if ($acao === 'criar') {
            $novoUsuario = trim($_POST['novo_usuario'] ?? '');
            $novaSenha = (string)($_POST['nova_senha'] ?? '');
            $perfil = $_POST['perfil'] ?? 'editor';
            if (!in_array($perfil, ['admin','editor'], true)) $perfil = 'editor';

            if ($novoUsuario === '' || $novaSenha === '') {
                $erro = 'Preencha usuário e senha.';
            } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $novoUsuario)) {
                $erro = 'Usuário deve ter 3 a 30 caracteres, apenas letras, números, ponto, hífen e underline.';
            } elseif (strlen($novaSenha) < 4) {
                $erro = 'A senha deve ter pelo menos 4 caracteres.';
            } elseif (usuarioExiste($novoUsuario)) {
                $erro = "Já existe um usuário com o nome \"$novoUsuario\".";
            } else {
                $usuarios[] = [
                    'id' => uniqid('u_', true),
                    'usuario' => $novoUsuario,
                    'senha_hash' => password_hash($novaSenha, PASSWORD_DEFAULT),
                    'perfil' => $perfil,
                ];
                salvarUsuarios($usuarios);
                $sucesso = "Usuário \"$novoUsuario\" criado com sucesso (" . ($perfil === 'admin' ? 'Admin' : 'Editor - só ramais') . ").";
                $usuarios = carregarUsuarios();
            }
        }

        if ($acao === 'alterar_senha') {
            $id = $_POST['id'] ?? '';
            $novaSenha = (string)($_POST['nova_senha_admin'] ?? '');
            $confirma = (string)($_POST['confirma_senha_admin'] ?? '');
            if ($novaSenha === '' || $confirma === '') {
                $erro = 'Preencha a nova senha e a confirmação.';
            } elseif ($novaSenha !== $confirma) {
                $erro = 'A nova senha e a confirmação não coincidem.';
            } elseif (strlen($novaSenha) < 4) {
                $erro = 'A senha deve ter pelo menos 4 caracteres.';
            } else {
                $achou = false;
                foreach ($usuarios as &$u) {
                    if (($u['id'] ?? '') === $id) {
                        $u['senha_hash'] = password_hash($novaSenha, PASSWORD_DEFAULT);
                        $achou = true;
                        $alvoNome = $u['usuario'];
                        break;
                    }
                }
                unset($u);
                if ($achou) {
                    salvarUsuarios($usuarios);
                    $sucesso = "Senha do usuário \"$alvoNome\" alterada com sucesso.";
                    $usuarios = carregarUsuarios();
                    foreach ($usuarios as $u) if (($u['usuario'] ?? '') === $usuarioLogado) { $meuUsuario = $u; break; }
                } else {
                    $erro = 'Usuário não encontrado.';
                }
            }
        }

        if ($acao === 'alterar_perfil') {
            $id = $_POST['id'] ?? '';
            $novoPerfil = $_POST['perfil'] ?? 'editor';
            if (!in_array($novoPerfil, ['admin','editor'], true)) $novoPerfil = 'editor';
            $achou = false;
            foreach ($usuarios as &$u) {
                if (($u['id'] ?? '') === $id) {
                    // impede remover o último admin
                    if (($u['perfil'] ?? '') === 'admin' && $novoPerfil !== 'admin') {
                        $qtdAdmins = 0;
                        foreach ($usuarios as $ux) if (($ux['perfil'] ?? '') === 'admin') $qtdAdmins++;
                        if ($qtdAdmins <= 1) {
                            $erro = 'Não é possível remover o último administrador.';
                            break;
                        }
                    }
                    $u['perfil'] = $novoPerfil;
                    $achou = true;
                    $alvoNome = $u['usuario'];
                    break;
                }
            }
            unset($u);
            if ($erro === '' && $achou) {
                salvarUsuarios($usuarios);
                // se alterou o próprio perfil, atualiza sessão
                if (($meuUsuario['id'] ?? '') === $id) {
                    $_SESSION['perfil'] = $novoPerfil;
                    $perfilLogado = $novoPerfil;
                    $isAdmin = $novoPerfil === 'admin';
                    foreach ($usuarios as $u) if (($u['id'] ?? '') === $id) { $meuUsuario = $u; break; }
                }
                $sucesso = "Perfil de \"$alvoNome\" alterado para " . ($novoPerfil === 'admin' ? 'Administrador' : 'Editor') . ".";
                $usuarios = carregarUsuarios();
            } elseif ($erro === '' && !$achou) {
                $erro = 'Usuário não encontrado.';
            }
        }

        if ($acao === 'excluir') {
            $id = $_POST['id'] ?? '';
            $alvo = null;
            foreach ($usuarios as $u) if (($u['id'] ?? '') === $id) { $alvo = $u; break; }
            if (!$alvo) {
                $erro = 'Usuário não encontrado.';
            } elseif (($alvo['id'] ?? '') === ($meuUsuario['id'] ?? '')) {
                $erro = 'Você não pode excluir seu próprio usuário.';
            } else {
                if (($alvo['perfil'] ?? '') === 'admin') {
                    $qtdAdmins = 0;
                    foreach ($usuarios as $ux) if (($ux['perfil'] ?? '') === 'admin') $qtdAdmins++;
                    if ($qtdAdmins <= 1) {
                        $erro = 'Não é possível excluir o último administrador.';
                    }
                }
                if ($erro === '') {
                    $usuarios = array_values(array_filter($usuarios, fn($u) => ($u['id'] ?? '') !== $id));
                    salvarUsuarios($usuarios);
                    $sucesso = "Usuário \"{$alvo['usuario']}\" excluído.";
                    $usuarios = carregarUsuarios();
                }
            }
        }

        if ($acao === 'renomear') {
            $id = $_POST['id'] ?? '';
            $novoNome = trim($_POST['novo_nome'] ?? '');
            if ($novoNome === '') {
                $erro = 'Nome de usuário não pode ser vazio.';
            } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $novoNome)) {
                $erro = 'Usuário deve ter 3 a 30 caracteres, apenas letras, números, ponto, hífen e underline.';
            } elseif (usuarioExiste($novoNome, $id)) {
                $erro = "Já existe um usuário com o nome \"$novoNome\".";
            } else {
                $achou = false;
                foreach ($usuarios as &$u) {
                    if (($u['id'] ?? '') === $id) {
                        $antigo = $u['usuario'];
                        $u['usuario'] = $novoNome;
                        $achou = true;
                        if (($meuUsuario['id'] ?? '') === $id) {
                            $_SESSION['usuario'] = $novoNome;
                            $usuarioLogado = $novoNome;
                        }
                        break;
                    }
                }
                unset($u);
                if ($achou) {
                    salvarUsuarios($usuarios);
                    $sucesso = "Usuário \"$antigo\" renomeado para \"$novoNome\".";
                    $usuarios = carregarUsuarios();
                    foreach ($usuarios as $u) if (($u['id'] ?? '') === ($meuUsuario['id'] ?? '')) { $meuUsuario = $u; break; }
                    // se não achou por id (mudou nome), tenta por nome
                    if (!$meuUsuario || ($meuUsuario['usuario'] ?? '') !== $usuarioLogado) {
                        foreach ($usuarios as $u) if (($u['usuario'] ?? '') === $usuarioLogado) { $meuUsuario = $u; break; }
                    }
                } else {
                    $erro = 'Usuário não encontrado.';
                }
            }
        }
    }
}

// ordena por perfil (admin primeiro) e depois nome
usort($usuarios, function($a,$b){
    $pa = $a['perfil'] ?? 'editor';
    $pb = $b['perfil'] ?? 'editor';
    if ($pa !== $pb) return $pa === 'admin' ? -1 : 1;
    return strcasecmp($a['usuario'] ?? '', $b['usuario'] ?? '');
});
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuários - Ramais</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23dc2626' d='M6.62 10.79a15.15 15.15 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z'/%3E%3C/svg%3E">
<style>
  :root { --azul:#2563eb; --azul-escuro:#1e40af; --cinza:#f3f4f6; --cinza-borda:#e5e7eb; --texto:#1f2937; --vermelho:#dc2626; --verde:#16a34a; }
  *{box-sizing:border-box}
  body{font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--cinza);color:var(--texto);margin:0;padding:0 16px 40px}
  header{max-width:900px;margin:0 auto;padding:24px 0 12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
  header h1{margin:0;color:var(--azul-escuro);font-size:1.5rem}
  header a{color:var(--azul);text-decoration:none;font-weight:600;font-size:.9rem}
  header a:hover{text-decoration:underline}
  .container{max-width:900px;margin:0 auto}
  .card{background:#fff;border:1px solid var(--cinza-borda);border-radius:12px;padding:20px;margin-bottom:20px}
  .card h2{margin:0 0 14px;color:var(--azul-escuro);font-size:1.1rem}
  .card h3{margin:18px 0 10px;color:#374151;font-size:.95rem}
  .msg{padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:.9rem}
  .msg-erro{background:#fee2e2;color:var(--vermelho)}
  .msg-sucesso{background:#dcfce7;color:var(--verde)}
  label{font-size:.8rem;color:#6b7280;font-weight:600;display:block;margin-bottom:4px}
  input, select{padding:10px 12px;border:1px solid var(--cinza-borda);border-radius:8px;font-size:.95rem;width:100%}
  input:focus, select:focus{outline:2px solid var(--azul);border-color:transparent}
  .grid{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
  .campo{flex:1;min-width:160px;display:flex;flex-direction:column;gap:4px}
  button, .btn{padding:10px 16px;border:none;border-radius:8px;background:var(--azul);color:#fff;font-weight:600;cursor:pointer;font-size:.9rem;text-decoration:none;display:inline-block}
  button:hover, .btn:hover{background:var(--azul-escuro)}
  .btn-sec{background:#fff;color:var(--azul-escuro);border:1px solid var(--cinza-borda)}
  .btn-sec:hover{background:var(--cinza)}
  .btn-danger{background:#fee2e2;color:var(--vermelho)}
  .btn-danger:hover{background:#fecaca}
  .btn-warning{background:#fef3c7;color:#92400e}
  .btn-warning:hover{background:#fde68a}
  table{width:100%;border-collapse:collapse}
  th,td{text-align:left;padding:10px 8px;border-bottom:1px solid var(--cinza-borda)}
  th{color:#6b7280;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em}
  .badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700}
  .badge-admin{background:#dbeafe;color:#1e40af}
  .badge-editor{background:#fef3c7;color:#92400e}
  .acoes{display:flex;gap:6px;flex-wrap:wrap}
  .acoes form{display:inline}
  .acoes button{padding:6px 10px;font-size:.8rem;border-radius:6px}
  .hint{color:#6b7280;font-size:.8rem;margin-top:6px}
  .linha{border-top:1px solid var(--cinza-borda);margin:16px 0}
  details{margin-top:10px}
  details summary{cursor:pointer;color:var(--azul);font-weight:600;font-size:.85rem}
</style>
</head>
<body>
<header>
  <h1>👥 Usuários</h1>
  <div style="display:flex;gap:14px;align-items:center">
    <span style="font-size:.85rem;color:#6b7280">Logado como <strong><?= htmlspecialchars($usuarioLogado) ?></strong> (<?= $isAdmin ? 'Admin' : 'Editor' ?>)</span>
    <a href="index.php">← Voltar aos ramais</a>
    <a href="logout.php">Sair</a>
  </div>
</header>
<div class="container">
  <?php if ($erro): ?><div class="msg msg-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
  <?php if ($sucesso): ?><div class="msg msg-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

  <!-- TROCAR PRÓPRIA SENHA (todos) -->
  <div class="card">
    <h2>🔑 Alterar minha senha</h2>
    <p class="hint" style="margin-top:0">Você pode alterar sua própria senha aqui. <?php if ($isAdmin) echo 'Como admin, você também pode alterar a senha de qualquer usuário na lista abaixo.'; ?></p>
    <form method="post" class="grid">
      <input type="hidden" name="acao" value="trocar_minha_senha">
      <div class="campo">
        <label for="senha_atual">Senha atual</label>
        <input type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password">
      </div>
      <div class="campo">
        <label for="nova_senha">Nova senha</label>
        <input type="password" id="nova_senha" name="nova_senha" required autocomplete="new-password">
      </div>
      <div class="campo">
        <label for="confirma_senha">Confirmar nova senha</label>
        <input type="password" id="confirma_senha" name="confirma_senha" required autocomplete="new-password">
      </div>
      <div style="flex:0 0 auto"><button type="submit">Salvar nova senha</button></div>
    </form>
  </div>

  <?php if ($isAdmin): ?>
  <!-- CRIAR NOVO USUÁRIO (só admin) -->
  <div class="card">
    <h2>➕ Criar novo usuário</h2>
    <p class="hint" style="margin-top:0">Usuários <strong>Editor</strong> podem apenas cadastrar, editar e remover ramais. <strong>Admin</strong> pode também gerenciar usuários e senhas.</p>
    <form method="post" class="grid">
      <input type="hidden" name="acao" value="criar">
      <div class="campo">
        <label for="novo_usuario">Usuário</label>
        <input type="text" id="novo_usuario" name="novo_usuario" required placeholder="ex: joao.silva" pattern="[a-zA-Z0-9._-]{3,30}" maxlength="30">
      </div>
      <div class="campo">
        <label for="nova_senha2">Senha</label>
        <input type="password" id="nova_senha2" name="nova_senha" required placeholder="mín. 4 caracteres">
      </div>
      <div class="campo" style="max-width:180px">
        <label for="perfil">Perfil</label>
        <select id="perfil" name="perfil">
          <option value="editor" selected>Editor (só ramais)</option>
          <option value="admin">Admin (tudo)</option>
        </select>
      </div>
      <div style="flex:0 0 auto"><button type="submit">Criar usuário</button></div>
    </form>
  </div>

  <!-- LISTA DE USUÁRIOS (só admin) -->
  <div class="card">
    <h2>📋 Usuários cadastrados (<?= count($usuarios) ?>)</h2>
    <table>
      <thead>
        <tr><th>Usuário</th><th>Perfil</th><th style="width:55%">Ações</th></tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): $ehEu = ($u['id'] ?? '') === ($meuUsuario['id'] ?? ''); ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['usuario']) ?></strong> <?php if ($ehEu): ?><span style="color:#6b7280;font-size:.75rem">(você)</span><?php endif; ?></td>
          <td><span class="badge <?= ($u['perfil'] ?? 'editor') === 'admin' ? 'badge-admin' : 'badge-editor' ?>"><?= ($u['perfil'] ?? 'editor') === 'admin' ? 'Admin' : 'Editor' ?></span></td>
          <td>
            <div class="acoes">
              <!-- Alterar senha (admin) -->
              <details>
                <summary>Alterar senha</summary>
                <form method="post" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;align-items:flex-end">
                  <input type="hidden" name="acao" value="alterar_senha">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                  <div style="flex:1;min-width:130px"><label>Nova senha</label><input type="password" name="nova_senha_admin" required></div>
                  <div style="flex:1;min-width:130px"><label>Confirmar</label><input type="password" name="confirma_senha_admin" required></div>
                  <button type="submit" class="btn-warning">Salvar</button>
                </form>
              </details>

              <!-- Renomear -->
              <details>
                <summary>Renomear</summary>
                <form method="post" style="display:flex;gap:6px;margin-top:8px;align-items:flex-end">
                  <input type="hidden" name="acao" value="renomear">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                  <div style="flex:1"><label>Novo nome</label><input type="text" name="novo_nome" value="<?= htmlspecialchars($u['usuario']) ?>" required pattern="[a-zA-Z0-9._-]{3,30}"></div>
                  <button type="submit">Renomear</button>
                </form>
              </details>

              <!-- Perfil -->
              <form method="post">
                <input type="hidden" name="acao" value="alterar_perfil">
                <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                <input type="hidden" name="perfil" value="<?= ($u['perfil'] ?? 'editor') === 'admin' ? 'editor' : 'admin' ?>">
                <button type="submit" class="btn-sec" title="Alternar perfil"><?= ($u['perfil'] ?? 'editor') === 'admin' ? 'Tornar Editor' : 'Tornar Admin' ?></button>
              </form>

              <?php if (!$ehEu): ?>
              <form method="post" onsubmit="return confirm('Excluir o usuário <?= htmlspecialchars($u['usuario']) ?>?')">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                <button type="submit" class="btn-danger">Excluir</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="hint">Dica: o usuário <strong>admin</strong> inicial já está com a senha <code>Ramais@Jales#2026</code>. Altere se desejar e crie usuários “Editor” para quem vai apenas gerenciar ramais.</p>
  </div>
  <?php else: ?>
  <div class="card">
    <p style="color:#6b7280">Você tem perfil <strong>Editor</strong> — pode cadastrar, editar e remover ramais, mas não gerenciar usuários. Para criar usuários ou alterar senha de outros, peça a um administrador.</p>
    <p><a class="btn btn-sec" href="index.php">Voltar aos ramais</a></p>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
