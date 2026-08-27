<?php
require_once __DIR__ . '/auth.php';
iniciarSessaoSeNecessario();
garantirUsuarios();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = (string)($_POST['senha'] ?? '');

    $usuarios = carregarUsuarios();
    $encontrado = null;
    foreach ($usuarios as $u) {
        if (strcasecmp($u['usuario'] ?? '', $usuario) === 0) {
            $encontrado = $u;
            break;
        }
    }

    $ok = false;
    if ($encontrado && !empty($encontrado['senha_hash']) && password_verify($senha, $encontrado['senha_hash'])) {
        // rehash se necessário
        if (password_needs_rehash($encontrado['senha_hash'], PASSWORD_DEFAULT)) {
            foreach ($usuarios as &$u) {
                if (($u['id'] ?? '') === $encontrado['id']) {
                    $u['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
                    break;
                }
            }
            unset($u);
            salvarUsuarios($usuarios);
            $encontrado['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        $ok = true;
    } else {
        // fallback legado: config.php (para migração de instalações antigas sem usuarios.json correto)
        $config = @include __DIR__ . '/config.php';
        if (is_array($config) && $usuario === ($config['usuario'] ?? '') && $senha === ($config['senha'] ?? '')) {
            // garante que o usuário exista em usuarios.json
            if (!$encontrado) {
                $usuarios[] = [
                    'id' => uniqid('u_', true),
                    'usuario' => $config['usuario'],
                    'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
                    'perfil' => 'admin',
                ];
                salvarUsuarios($usuarios);
                $encontrado = end($usuarios);
            }
            $ok = true;
        }
    }

    if ($ok && $encontrado) {
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = $encontrado['usuario'];
        $_SESSION['perfil'] = $encontrado['perfil'] ?? 'editor';
        $_SESSION['usuario_id'] = $encontrado['id'] ?? null;
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Ramais</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23dc2626' d='M6.62 10.79a15.15 15.15 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z'/%3E%3C/svg%3E">
<link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23dc2626' d='M6.62 10.79a15.15 15.15 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z'/%3E%3C/svg%3E">
<meta name="theme-color" content="#dc2626">
<style>
  :root {
    --azul: #2563eb;
    --azul-escuro: #1e40af;
    --cinza: #f3f4f6;
    --cinza-borda: #e5e7eb;
    --texto: #1f2937;
    --vermelho: #dc2626;
  }
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    background: var(--cinza);
    color: var(--texto);
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .card {
    background: #fff;
    border: 1px solid var(--cinza-borda);
    border-radius: 12px;
    padding: 32px;
    width: 100%;
    max-width: 340px;
  }
  .card h1 {
    font-size: 1.4rem;
    margin: 0 0 4px;
    text-align: center;
    color: var(--azul-escuro);
  }
  .card p.sub {
    text-align: center;
    color: #6b7280;
    margin: 0 0 24px;
    font-size: 0.9rem;
  }
  .campo { margin-bottom: 14px; display: flex; flex-direction: column; gap: 4px; }
  .campo label { font-size: 0.8rem; color: #6b7280; font-weight: 600; }
  .campo input {
    padding: 10px 12px;
    border: 1px solid var(--cinza-borda);
    border-radius: 8px;
    font-size: 1rem;
  }
  .campo input:focus { outline: 2px solid var(--azul); border-color: transparent; }
  button {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: 8px;
    background: var(--azul);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    margin-top: 6px;
  }
  button:hover { background: var(--azul-escuro); }
  .msg-erro {
    background: #fee2e2;
    color: var(--vermelho);
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 0.85rem;
    text-align: center;
  }
  .voltar {
    display: block;
    text-align: center;
    margin-top: 16px;
    font-size: 0.85rem;
    color: #6b7280;
    text-decoration: none;
  }
  .voltar:hover { color: var(--azul); }
</style>
</head>
<body>
  <div class="card">
    <h1>🔒 Login</h1>
    <p class="sub">Acesso restrito para cadastrar ramais</p>

    <?php if ($erro): ?>
      <div class="msg-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="campo">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" name="usuario" required autofocus>
      </div>
      <div class="campo">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>
      </div>
      <button type="submit">Entrar</button>
    </form>

    <a class="voltar" href="index.php">&larr; Voltar para a lista de ramais</a>
  </div>
</body>
</html>
