<?php
include '../base/baseFuncoes.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';

$naoExcluirCliente = $_POST['naoExcluirCliente'] ?? '0';

if (empty($id)) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'ID do cliente não informado'
    ]);
    exit;
}

if ($naoExcluirCliente == '1'){
    $retorno = Asaas_ExcluirCobranca(($_SESSION['id_CobrancaAsaasQrCode']));
    $_SESSION['id_CobrancaAsaasQrCode'] = '';
}else{
    $retorno = Asaas_ExcluirCliente($id);
}

echo json_encode([
    'sucesso' => true,
    'retorno' => $retorno
]);

?>