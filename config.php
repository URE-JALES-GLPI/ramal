<?php
/**
 * Configuração de acesso (só usada na primeira execução, quando data/usuarios.json ainda não existe).
 * O sistema usa data/usuarios.json com senhas em hash.
 *
 * Senha inicial do admin, em ordem de prioridade:
 *   1. variável de ambiente RAMAL_ADMIN_SENHA
 *   2. config.local.php (não versionado), no formato: <?php return ['senha' => '...'];
 *   3. se nenhuma existir, uma senha aleatória é gerada e gravada em data/senha-inicial.php
 */
$local = file_exists(__DIR__ . '/config.local.php') ? (include __DIR__ . '/config.local.php') : [];

return [
    'usuario' => 'admin',
    'senha'   => getenv('RAMAL_ADMIN_SENHA') ?: ($local['senha'] ?? null),
];
