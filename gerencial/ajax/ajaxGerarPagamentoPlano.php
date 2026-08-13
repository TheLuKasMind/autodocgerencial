<?php

include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require_once '../base/globals.php';


$config = $GLOBALS['global'];

$idPlano = (int)$_POST['idPlano'];

$totalUsuarios =  max(1, (int)($_POST['totalUsuarios'] ?? 1));

$sql = "
    SELECT id, Nome, Valor
    FROM planos
    WHERE id = ?
    LIMIT 1
";

// $sql = " 
//     SELECT id, Nome, 5 As Valor
//     FROM planos
//     WHERE id = ?
//     LIMIT 1
// ";

$plano = ExSqlNET($sql,null,[$idPlano]);

if(empty($plano)){
    die(json_encode([
        'sucesso' => false,
        'mensagem' => 'Plano não informado'
    ]));
}

function atualizaIdAsaas(){
    if ($_SESSION['id_Asaas'] <> ''){
        $sql2 = "UPDATE empresa SET id_Asaas = ? WHERE id = ?";
        ExSqlNET($sql2, null, [$_SESSION['id_Asaas'], $_SESSION['idEmpresa']]);
    }
}

$plano = $plano[0];


$valorUsuarioAdicional = $config['valor_usuario_adicional'];

$valorAdicional = ($totalUsuarios - 1) * $valorUsuarioAdicional;

$valorPlano = $plano['Valor'] + $valorAdicional;


if (isset($_POST['id_Asaas']) && $_POST['id_Asaas'] <> '' ) {
    $cliente['body']['id'] = $_POST['id_Asaas'];
   
} else { // cria cliente
    // dados do cadastro
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $documento = preg_replace('/\D/','',$_POST['documento']);

    $cliente = Asaas_CriaCliente(
        $nome,
        $documento,
        $email
    );
    $_SESSION['id_Asaas'] = $cliente['body']['id'] ;
    if (isset($_SESSION['idEmpresa']) && $_SESSION['idEmpresa'] <> '' ){
        atualizaIdAsaas();
    }

}

// cria cobrança
$cobranca = Asaas_CriaCobranca(
    $cliente['body']['id'],
    $valorPlano,
    "PIX"
);

// Pra excluir depois, caso o usuário cancele a cobrança
$_SESSION['id_CobrancaAsaasQrCode'] = $cobranca['body']['id'];

// qr code
$qrCode = Asaas_CobrancaQrCode(
    $cobranca['body']['id']
);

echo json_encode([
    'sucesso' => true,
    'valor' => number_format($valorPlano,2,',','.'),
    'qrCode' => $qrCode['body']['encodedImage'],
    'idCobranca' => $cobranca['body']['id'],
    'id_Asaas' => $cliente['body']['id']
]);
