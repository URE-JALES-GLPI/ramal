<?php
/**
 * Importação única dos ramais.
 * Roda no servidor: sudo -u www-data php import_ramais.php
 *
 * ATENÇÃO: este script SUBSTITUI todos os ramais existentes pelos da lista
 * abaixo. Antes de apagar, faz um backup de data/ramais.json em
 * data/ramais.json.backup_<datahora>. Depois pode apagar este arquivo.
 */

$dataFile = __DIR__ . '/data/ramais.json';

$novos = [
    ['ramal' => '1001', 'nome' => 'Geraldo Niza da Silva',                          'cargo' => 'Chefe de Departamento – Dirigente Regional de Ensino', 'setor' => 'GABINETE', 'email' => 'jal@educacao.sp.gov.br'],
    ['ramal' => '1003', 'nome' => 'Fabiana Ribeiro Vieira',                          'cargo' => 'Assessoria Técnica', 'setor' => 'GABINETE', 'email' => 'jal.asure@educacao.sp.gov.br'],
    ['ramal' => '1042', 'nome' => 'Portaria',                                        'cargo' => '', 'setor' => 'PROTOCOLO', 'email' => ''],
    ['ramal' => '1037', 'nome' => 'Rafael',                                          'cargo' => 'Estagiário', 'setor' => 'PROTOCOLO', 'email' => ''],
    ['ramal' => '1021', 'nome' => 'Adimara Aparecida Martins de Souza',              'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1019', 'nome' => 'Errivaine Aparecida Ferreira Gomes',              'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1016', 'nome' => 'Everson Maciel Jorge',                            'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1015', 'nome' => 'Francisco de Assis Leonel Teixeira',              'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1013', 'nome' => 'João José Gimenez',                               'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1022', 'nome' => 'Maria Aparecida Sanches Cardoso Neves',           'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1023', 'nome' => 'Marlene Medaglia Cavalheiro Jacomassi',           'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1012', 'nome' => 'Meire Aparecida Bueno de Magalhãis de Souza',     'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1017', 'nome' => 'Neuza Takaki',                                    'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1014', 'nome' => 'Renata Fernandes Crespo Cintra',                  'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1018', 'nome' => 'Silvio Salvador Furlan',                          'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1020', 'nome' => 'Sonia Pinatto Soares',                            'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '1063', 'nome' => 'Ana Paula de Oliveira Rubio',                     'cargo' => 'CEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1081', 'nome' => 'Adinéia da Silva Mastelari',                      'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1074', 'nome' => 'Alessandra Almeida Dalben Scapin',                'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1079', 'nome' => 'Carla Renata de Oliveira Lançoni Junqueira',      'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1075', 'nome' => 'Caroline Figueiredo',                             'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1072', 'nome' => 'Cyntia Gutierrez Freitas Umiji',                  'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1065', 'nome' => 'Djane Zambon Viola',                              'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1064', 'nome' => 'Elisangela Critina Talhare Santos',               'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1071', 'nome' => 'Fernanda Machado Pinheiro',                       'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1080', 'nome' => 'João Paulo Lisboa Companeri',                     'cargo' => 'Analista Sociocultural', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1073', 'nome' => 'João Pedro Strabelli',                            'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1078', 'nome' => 'Luciana Cristina Soares Jardim',                  'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1070', 'nome' => 'Paula Carolina Lopes Custodio Queiróz',           'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1076', 'nome' => 'Rosilaine Sanches Martins',                       'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1061', 'nome' => 'Sandra Regina Alves de Souza',                    'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1068', 'nome' => 'Vinicius Gomes Tabet',                            'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1077', 'nome' => 'Vivien dos Santos Carneiro Lopes',                'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1051', 'nome' => 'Janice Alves Carvalho Brasil',                    'cargo' => 'Chefe de Serviço', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '1055', 'nome' => 'Gislaine Cândido Matias Pietrobon',               'cargo' => 'Chefe de Seção', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sefrep@educacao.sp.gov.br'],
    ['ramal' => '1056', 'nome' => 'Maria Cecília da Conceição dos Santos',           'cargo' => 'Chefe de Seção', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.seap@educacao.sp.gov.br'],
    ['ramal' => '1057', 'nome' => 'Eliana Leite',                                    'cargo' => 'PEB II – Assistência Técnica', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '1008', 'nome' => 'Juliana Carla',                                   'cargo' => 'Analista Administrativo', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '1010', 'nome' => 'Glaucia Quenia de Leão',                          'cargo' => 'PEB I – Assistência Técnica', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '1026', 'nome' => 'Roberta Prandi Franco',                           'cargo' => 'Chefe de Serviço', 'setor' => 'SEGRE – Serviço de Gestão da Rede Escolar', 'email' => 'jal.segre@educacao.sp.gov.br'],
    ['ramal' => '1002', 'nome' => 'Sidnéia dos Santos Rodrigues',                    'cargo' => 'Chefe de Seção', 'setor' => 'SEGRE – Serviço de Gestão da Rede Escolar', 'email' => 'jal.semat@educacao.sp.gov.br'],
    ['ramal' => '1027', 'nome' => 'Bruna Sabatin Rodrigues',                         'cargo' => 'Chefe de Seção', 'setor' => 'SEGRE – Serviço de Gestão da Rede Escolar', 'email' => 'jal.sevesc@educacao.sp.gov.br'],
    ['ramal' => '1028', 'nome' => 'Cristian Akio Sawata',                            'cargo' => 'Chefe de Serviço', 'setor' => 'SEINTEC – Serviço de Informações Educacionais e Tecnologia', 'email' => 'jal.seintec@educacao.sp.gov.br'],
    ['ramal' => '1029', 'nome' => 'Leonardo Poiatti Fação',                          'cargo' => 'Técnico Field Service', 'setor' => 'SEINTEC – Serviço de Informações Educacionais e Tecnologia', 'email' => ''],
    ['ramal' => '1030', 'nome' => 'Aryan Ferrari',                                   'cargo' => 'Técnico Field Service', 'setor' => 'SEINTEC – Serviço de Informações Educacionais e Tecnologia', 'email' => ''],
    ['ramal' => '1049', 'nome' => 'Marineide Pereira dos Santos',                    'cargo' => 'Chefe de Serviço', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.seom@educacao.sp.gov.br'],
    ['ramal' => '1045', 'nome' => 'Paulo Henrique Rico',                             'cargo' => 'Chefe de Seção', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.sefisc@educacao.sp.gov.br'],
    ['ramal' => '1043', 'nome' => 'Luiz Fortunato Belão',                            'cargo' => 'Assistente II', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.sefis@educacao.sp.gov.br'],
    ['ramal' => '1040', 'nome' => 'Renan Vinicius Chiuchi Oliveira',                 'cargo' => 'Analista Administrativo', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.sefisc@educacao.sp.gov.br'],
    ['ramal' => '1031', 'nome' => 'Gabriel Hiroyuki Fukusawa',                       'cargo' => 'Chefe de Serviço', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.seafin@educacao.sp.gov.br'],
    ['ramal' => '1032', 'nome' => 'Carla Franciele de Souza',                        'cargo' => 'Chefe de Seção', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.sefin@educacao.sp.gov.br'],
    ['ramal' => '1048', 'nome' => 'Thaís Delmondes',                                 'cargo' => 'Chefe de Seção', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.secomse@educacao.sp.gov.br'],
    ['ramal' => '1050', 'nome' => 'Kleyton Giovani dos Santos Gerolim',              'cargo' => 'Oficial Administrativo', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.secomse@educacao.sp.gov.br'],
    ['ramal' => '1039', 'nome' => 'Robson José Gonzales',                            'cargo' => 'Oficial Administrativo', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.secomse@educacao.sp.gov.br'],
];

// backup do arquivo atual antes de substituir
if (is_file($dataFile)) {
    $backup = $dataFile . '.backup_' . date('Ymd_His');
    copy($dataFile, $backup);
    echo "Backup salvo em: $backup\n";
}

// monta a lista nova
$lista = [];
foreach ($novos as $n) {
    $lista[] = [
        'id' => uniqid(),
        'ramal' => $n['ramal'],
        'nome' => $n['nome'],
        'setor' => $n['setor'],
        'cargo' => $n['cargo'],
        'email' => $n['email'],
    ];
}

file_put_contents($dataFile, json_encode($lista, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Cadastrados: " . count($lista) . " ramais (lista antiga substituída)\n";
