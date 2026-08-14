<<<<<<< HEAD
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

## Atualizar depois de mudanças no código

```bash
cd /var/www/html/ramal
sudo git pull
```

(o arquivo `data/ramais.json` não é sobrescrito pelo git, ele fica de fora
do controle de versão — veja o `.gitignore`)

## Estrutura

- `index.php` — todo o site (lista, cadastro, edição, exclusão, busca)
- `data/ramais.json` — onde os ramais cadastrados ficam salvos (ignorado pelo git)
- `data/.htaccess` — bloqueia acesso direto ao json pelo navegador
=======
# ramal
Controle de ramais para URE
>>>>>>> c1bd74c5f1d457b40294c325ffc738045a4ab669
