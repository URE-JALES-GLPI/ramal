<?php
session_start();
$config = require __DIR__ . '/config.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');

    if ($usuario === $config['usuario'] && $senha === $config['senha']) {
        $_SESSION['logado'] = true;
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
