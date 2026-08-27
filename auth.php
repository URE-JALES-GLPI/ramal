<?php
/**
 * Helpers de autenticação e gerenciamento de usuários
 * Usa data/usuarios.json com senhas em hash (password_hash)
 * Perfis: admin (gerencia ramais + usuários/senhas) | editor (gerencia apenas ramais)
 */

function usuariosFile() {
    return __DIR__ . '/data/usuarios.json';
}

function carregarUsuarios() {
    $file = usuariosFile();
    if (!file_exists($file)) {
        return [];
    }
    $conteudo = @file_get_contents($file);
    $dados = json_decode((string)$conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarUsuarios($usuarios) {
    $file = usuariosFile();
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    file_put_contents($file, json_encode(array_values($usuarios), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/**
 * Garante que exista ao menos o admin inicial.
 * Se o arquivo não existir, cria. Se existir vazio, migra do config.php se possível.
 */
function garantirUsuarios() {
    $file = usuariosFile();
    $usuarios = carregarUsuarios();
    if (!empty($usuarios)) {
        // garante que todo usuário tenha campos necessários
        $alterado = false;
        foreach ($usuarios as &$u) {
            if (!isset($u['id'])) { $u['id'] = uniqid('u_', true); $alterado = true; }
            if (!isset($u['perfil'])) { $u['perfil'] = ($u['usuario'] === 'admin' ? 'admin' : 'editor'); $alterado = true; }
        }
        unset($u);
        // migração: se admin ainda está com senha antiga jales.123, atualiza para Ramais@Jales#2026
        foreach ($usuarios as &$u) {
            if (($u['usuario'] ?? '') === 'admin' && !empty($u['senha_hash'])) {
                if (password_verify('jales.123', $u['senha_hash']) && !password_verify('Ramais@Jales#2026', $u['senha_hash'])) {
                    $u['senha_hash'] = password_hash('Ramais@Jales#2026', PASSWORD_DEFAULT);
                    $alterado = true;
                }
                break;
            }
        }
        unset($u);
        if ($alterado) salvarUsuarios($usuarios);
        return $usuarios;
    }

    // tenta migrar do config.php (se existir)
    $config = [];
    $configFile = __DIR__ . '/config.php';
    if (file_exists($configFile)) {
        $cfg = @include $configFile;
        if (is_array($cfg) && !empty($cfg['usuario'])) {
            $config = $cfg;
        }
    }

    $usuarioAdmin = $config['usuario'] ?? 'admin';
    $senhaAdmin   = $config['senha']   ?? 'Ramais@Jales#2026';

    $usuarios = [[
        'id' => uniqid('u_', true),
        'usuario' => $usuarioAdmin,
        'senha_hash' => password_hash($senhaAdmin, PASSWORD_DEFAULT),
        'perfil' => 'admin',
    ]];
    salvarUsuarios($usuarios);
    return $usuarios;
}

function buscarUsuarioPorNome($nome) {
    $usuarios = carregarUsuarios();
    if (empty($usuarios)) {
        $usuarios = garantirUsuarios();
    }
    foreach ($usuarios as $u) {
        if (strcasecmp($u['usuario'] ?? '', $nome) === 0) {
            return $u;
        }
    }
    return null;
}

function buscarUsuarioPorId($id) {
    foreach (carregarUsuarios() as $u) {
        if (($u['id'] ?? '') === $id) return $u;
    }
    return null;
}

function usuarioExiste($nome, $ignorarId = null) {
    foreach (carregarUsuarios() as $u) {
        if (strcasecmp($u['usuario'] ?? '', $nome) === 0) {
            if ($ignorarId !== null && ($u['id'] ?? '') === $ignorarId) continue;
            return true;
        }
    }
    return false;
}

// Inicializa sessão se necessário (chamar antes de checar logado)
function iniciarSessaoSeNecessario() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function estaLogado() {
    iniciarSessaoSeNecessario();
    return isset($_SESSION['logado']) && $_SESSION['logado'] === true && !empty($_SESSION['usuario']);
}

function usuarioLogado() {
    iniciarSessaoSeNecessario();
    return $_SESSION['usuario'] ?? null;
}

function perfilLogado() {
    iniciarSessaoSeNecessario();
    return $_SESSION['perfil'] ?? null;
}

function ehAdmin() {
    return estaLogado() && (perfilLogado() === 'admin');
}

function requerLogin() {
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
}

function requerAdmin() {
    requerLogin();
    if (!ehAdmin()) {
        http_response_code(403);
        echo '<h1>Acesso negado</h1><p>Apenas administradores podem acessar esta página.</p><p><a href="index.php">Voltar</a></p>';
        exit;
    }
}
