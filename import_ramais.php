<?php
/**
 * Importação única dos ramais.
 * Roda no servidor: php import_ramais.php
 * Pula ramais que já existem (por número). Depois pode apagar este arquivo.
 */

$dataFile = __DIR__ . '/data/ramais.json';

$novos = [
    ['ramal' => '1',   'nome' => 'Geraldo Niza da Silva',                          'cargo' => 'Chefe de Departamento – Dirigente Regional de Ensino', 'setor' => 'GABINETE', 'email' => 'jal@educacao.sp.gov.br'],
    ['ramal' => '3',   'nome' => 'Fabiana Ribeiro Vieira',                          'cargo' => 'Assessoria Técnica', 'setor' => 'GABINETE', 'email' => 'jal.asure@educacao.sp.gov.br'],
    ['ramal' => '1042','nome' => 'Portaria',                                        'cargo' => '', 'setor' => 'PROTOCOLO', 'email' => ''],
    ['ramal' => '1037','nome' => 'Rafael',                                          'cargo' => 'Estagiário', 'setor' => 'PROTOCOLO', 'email' => ''],
    ['ramal' => '21',  'nome' => 'Adimara Aparecida Martins de Souza',              'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '19',  'nome' => 'Errivaine Aparecida Ferreira Gomes',              'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '16',  'nome' => 'Everson Maciel Jorge',                            'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '15',  'nome' => 'Francisco de Assis Leonel Teixeira',              'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '13',  'nome' => 'João José Gimenez',                               'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '22',  'nome' => 'Maria Aparecida Sanches Cardoso Neves',           'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '23',  'nome' => 'Marlene Medaglia Cavalheiro Jacomassi',           'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '12',  'nome' => 'Meire Aparecida Bueno de Magalhãis de Souza',     'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '17',  'nome' => 'Neuza Takaki',                                    'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '14',  'nome' => 'Renata Fernandes Crespo Cintra',                  'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '18',  'nome' => 'Silvio Salvador Furlan',                          'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '20',  'nome' => 'Sonia Pinatto Soares',                            'cargo' => 'Supervisor', 'setor' => 'ESE – Equipe de Supervisão de Ensino', 'email' => 'jal.ese@educacao.sp.gov.br'],
    ['ramal' => '63',  'nome' => 'Ana Paula de Oliveira Rubio',                     'cargo' => 'CEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '1081','nome' => 'Adinéia da Silva Mastelari',                      'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '74',  'nome' => 'Alessandra Almeida Dalben Scapin',                'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '79',  'nome' => 'Carla Renata de Oliveira Lançoni Junqueira',      'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '75',  'nome' => 'Caroline Figueiredo',                             'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '72',  'nome' => 'Cyntia Gutierrez Freitas Umiji',                  'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '65',  'nome' => 'Djane Zambon Viola',                              'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '64',  'nome' => 'Elisangela Critina Talhare Santos',               'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '71',  'nome' => 'Fernanda Machado Pinheiro',                       'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '80',  'nome' => 'João Paulo Lisboa Companeri',                     'cargo' => 'Analista Sociocultural', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '73',  'nome' => 'João Pedro Strabelli',                            'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '78',  'nome' => 'Luciana Cristina Soares Jardim',                  'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '70',  'nome' => 'Paula Carolina Lopes Custodio Queiróz',           'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '76',  'nome' => 'Rosilaine Sanches Martins',                       'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '61',  'nome' => 'Sandra Regina Alves de Souza',                    'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '68',  'nome' => 'Vinicius Gomes Tabet',                            'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '77',  'nome' => 'Vivien dos Santos Carneiro Lopes',                'cargo' => 'PEC', 'setor' => 'EEC – Equipe de Especialistas em Currículo', 'email' => 'jal.eec@educacao.sp.gov.br'],
    ['ramal' => '51',  'nome' => 'Janice Alves Carvalho Brasil',                    'cargo' => 'Chefe de Serviço', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '55',  'nome' => 'Gislaine Cândido Matias Pietrobon',               'cargo' => 'Chefe de Seção', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sefrep@educacao.sp.gov.br'],
    ['ramal' => '56',  'nome' => 'Maria Cecília da Conceição dos Santos',           'cargo' => 'Chefe de Seção', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.seap@educacao.sp.gov.br'],
    ['ramal' => '57',  'nome' => 'Eliana Leite',                                    'cargo' => 'PEB II – Assistência Técnica', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '8',   'nome' => 'Juliana Carla',                                   'cargo' => 'Analista Administrativo', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '10',  'nome' => 'Glaucia Quenia de Leão',                          'cargo' => 'PEB I – Assistência Técnica', 'setor' => 'SEPES – Serviço de Pessoas', 'email' => 'jal.sepes@educacao.sp.gov.br'],
    ['ramal' => '26',  'nome' => 'Roberta Prandi Franco',                           'cargo' => 'Chefe de Serviço', 'setor' => 'SEGRE – Serviço de Gestão da Rede Escolar', 'email' => 'jal.segre@educacao.sp.gov.br'],
    ['ramal' => '2',   'nome' => 'Sidnéia dos Santos Rodrigues',                    'cargo' => 'Chefe de Seção', 'setor' => 'SEGRE – Serviço de Gestão da Rede Escolar', 'email' => 'jal.semat@educacao.sp.gov.br'],
    ['ramal' => '27',  'nome' => 'Bruna Sabatin Rodrigues',                         'cargo' => 'Chefe de Seção', 'setor' => 'SEGRE – Serviço de Gestão da Rede Escolar', 'email' => 'jal.sevesc@educacao.sp.gov.br'],
    ['ramal' => '28',  'nome' => 'Cristian Akio Sawata',                            'cargo' => 'Chefe de Serviço', 'setor' => 'SEINTEC – Serviço de Informações Educacionais e Tecnologia', 'email' => 'jal.seintec@educacao.sp.gov.br'],
    ['ramal' => '29',  'nome' => 'Leonardo Poiatti Fação',                          'cargo' => 'Técnico Field Service', 'setor' => 'SEINTEC – Serviço de Informações Educacionais e Tecnologia', 'email' => ''],
    ['ramal' => '30',  'nome' => 'Aryan Ferrari',                                   'cargo' => 'Técnico Field Service', 'setor' => 'SEINTEC – Serviço de Informações Educacionais e Tecnologia', 'email' => ''],
    ['ramal' => '49',  'nome' => 'Marineide Pereira dos Santos',                    'cargo' => 'Chefe de Serviço', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.seom@educacao.sp.gov.br'],
    ['ramal' => '45',  'nome' => 'Paulo Henrique Rico',                             'cargo' => 'Chefe de Seção', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.sefisc@educacao.sp.gov.br'],
    ['ramal' => '43',  'nome' => 'Luiz Fortunato Belão',                            'cargo' => 'Assistente II', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.sefis@educacao.sp.gov.br'],
    ['ramal' => '40',  'nome' => 'Renan Vinicius Chiuchi Oliveira',                 'cargo' => 'Analista Administrativo', 'setor' => 'SEOM – Serviço de Obras e Manutenção Escolar', 'email' => 'jal.sefisc@educacao.sp.gov.br'],
    ['ramal' => '31',  'nome' => 'Gabriel Hiroyuki Fukusawa',                       'cargo' => 'Chefe de Serviço', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.seafin@educacao.sp.gov.br'],
    ['ramal' => '32',  'nome' => 'Carla Franciele de Souza',                        'cargo' => 'Chefe de Seção', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.sefin@educacao.sp.gov.br'],
    ['ramal' => '48',  'nome' => 'Thaís Delmondes',                                 'cargo' => 'Chefe de Seção', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.secomse@educacao.sp.gov.br'],
    ['ramal' => '50',  'nome' => 'Kleyton Giovani dos Santos Gerolim',              'cargo' => 'Oficial Administrativo', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.secomse@educacao.sp.gov.br'],
    ['ramal' => '39',  'nome' => 'Robson José Gonzales',                            'cargo' => 'Oficial Administrativo', 'setor' => 'SEAFIN – Serviços de Administração e Finanças', 'email' => 'jal.secomse@educacao.sp.gov.br'],
];

$existentes = is_file($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
if (!is_array($existentes)) $existentes = [];

$numerosExistentes = [];
foreach ($existentes as $r) $numerosExistentes[] = $r['ramal'];

$adicionados = 0;
$pulados = [];
foreach ($novos as $n) {
    if (in_array($n['ramal'], $numerosExistentes)) {
        $pulados[] = $n['ramal'];
        continue;
    }
    $existentes[] = [
        'id' => uniqid(),
        'ramal' => $n['ramal'],
        'nome' => $n['nome'],
        'setor' => $n['setor'],
        'cargo' => $n['cargo'],
        'email' => $n['email'],
    ];
    $numerosExistentes[] = $n['ramal'];
    $adicionados++;
}

file_put_contents($dataFile, json_encode(array_values($existentes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Adicionados: $adicionados\n";
if ($pulados) echo "Ja existiam (pulados): " . implode(', ', $pulados) . "\n";
echo "Total agora: " . count($existentes) . "\n";
