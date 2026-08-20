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

- Usuário: `admin`

Qualquer pessoa pode **ver** a lista de ramais. Só quem fizer login consegue
**cadastrar, editar ou excluir**. A senha fica no arquivo `config.php`
(pode trocar lá quando quiser).

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
- `config.php` — usuário e senha do login
- `.htaccess` — bloqueia acesso direto ao config.php
- `data/ramais.json` — onde os ramais cadastrados ficam salvos (ignorado pelo git)
- `data/.htaccess` — bloqueia acesso direto ao json pelo navegador

## Autor

Desenvolvido por **Leonardo Poiatti Fação**.
