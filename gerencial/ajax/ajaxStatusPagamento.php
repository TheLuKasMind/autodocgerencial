<?php

include '../base/baseFuncoes.php';
include '../base/ambiente.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$liberarSistema = $_POST['liberarSistema'] ?? '0';

if(empty($id)){
    echo json_encode([
        "erro" => true,
        "mensagem" => "ID da cobrança não informado"
    ]);
    exit;
}

if ($liberarSistema == '1'){
    LiberarSistema($_SESSION['idEmpresa'] ?? 0);
    $status = "true";
}else{
    $retorno = Asaas_PagamentoStatus($id);
    $status = $retorno['body']['status'] ?? null;
}

if ($DEBUG_LOCAL == 1){
    $status = "RECEIVED";
}

echo json_encode([
    "status" => $status
]);

exit;