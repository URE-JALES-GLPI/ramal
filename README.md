# Ramais

Sitezinho simples para cadastrar RAMAL | NOME. PHP puro, sem banco de dados
(guarda tudo em `data/ramais.json`).

## Instalar na VM Ubuntu

Pré-requisito: Apache + PHP.

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php -y
```

Clone direto dentro do `/var/www/html`:

```bash
cd /var/www/html
sudo git clone SEU_LINK_DO_REPOSITORIO ramal
sudo chown -R www-data:www-data /var/www/html/ramal
sudo chmod -R 755 /var/www/html/ramal
sudo chmod -R 775 /var/www/html/ramal/data
```

Acesse: `http://IP_DA_VM/ramal`

## Habilitar os arquivos .htaccess (importante)

O projeto usa `.htaccess` para bloquear o acesso direto ao `config.php` (login/senha)
e ao `data/ramais.json`. Para isso funcionar, o Apache precisa permitir
`AllowOverride`. Rode:

```bash
sudo a2enmod rewrite
sudo sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
sudo systemctl restart apache2
```

## Login

- Usuário: `admin` — Senha: `Ramais@Jales#2026`

Qualquer pessoa pode **ver** a lista de ramais. Só quem fizer login consegue
**cadastrar, editar ou excluir**.

O sistema agora usa `data/usuarios.json` com senhas em hash (`password_hash`).
Existem dois perfis:
- **Admin**: gerencia ramais e também gerencia usuários/senhas em `usuarios.php`
- **Editor**: gerencia apenas ramais (cadastrar/alterar/remover)

O usuário `admin` pode em `usuarios.php`:
- alterar a própria senha (e a de qualquer usuário)
- criar novos usuários “Editor” (só ramais) ou “Admin”
- renomear, trocar perfil e excluir usuários

Qualquer usuário logado pode alterar a própria senha em `usuarios.php` → “Alterar minha senha”.

> Compatibilidade: `config.php` ainda existe para migração inicial, mas o login principal é via `usuarios.json`.

## Atualizar depois de mudanças no código

```bash
cd /var/www/html/ramal
sudo git pull
```

(o arquivo `data/ramais.json` não é sobrescrito pelo git, ele fica de fora
do controle de versão — veja o `.gitignore`)

## Estrutura

- `index.php` — lista, cadastro, edição, exclusão, busca (cadastro/edição/exclusão exigem login)
- `login.php` / `logout.php` — tela de login e logout
- `usuarios.php` — gerenciamento de usuários e senhas (só Admin; todos podem trocar própria senha)
- `auth.php` — helpers de autenticação, hash de senhas e perfis (admin/editor)
- `config.php` — usuário/senha legado (usado só na migração inicial)
- `.htaccess` — bloqueia acesso direto ao config.php/auth.php
- `data/ramais.json` — onde os ramais cadastrados ficam salvos (ignorado pelo git)
- `data/usuarios.json` — usuários e hashes de senha (ignorado pelo git)
- `data/buscas.json` — log de pesquisas (ignorado pelo git)
- `data/.htaccess` — bloqueia acesso direto aos json pelo navegador

## Autor

Desenvolvido por **Leonardo Poiatti Fação**.
