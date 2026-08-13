<?php
require_once  __DIR__ .'/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';

$GLOBALS['EMAIL_SMTP'] = [
    'link_sistema' => 'autodocoficial.com',
    'host' => 'smtp.gmail.com',
    'porta' => 587,
    'usuario' => 'autodocva@gmail.com',
    'senha_app' => 'xxkr rfrx cqjz mtgu',
    'remetente_nome' => 'Autodoc Gerencial'
];

$config = ExSqlNET("
    SELECT *
    FROM config
    LIMIT 1
");
$asaasChave = $config[0]['AsaasApiKey'] ?? '';

$GLOBALS['global'] = [
    'limite_usuarios' => '1',  
    'valor_usuario_adicional' => '16.90',  
    'asaas_chave' => $asaasChave
    // 'asaas_chave' => '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjkyNzcxMzk2LWI0OGEtNGIwNS04MjhmLTIyOTRjMmFkY2E1NTo6JGFhY2hfMjVkMzY5YmMtNGVmZC00MmFmLWFjN2YtOTZmMGNlNDU1MGU5',  
];