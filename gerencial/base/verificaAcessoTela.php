<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connection.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

$idUsuario = $_SESSION['usuario_id'];
$idEmpresa = $_SESSION['idEmpresa'];

if (isset($_SESSION['AdminGeral']) && $_SESSION['AdminGeral'] == 1) {
    return;
}


$paginasLivres = [
    'frmHome.php',
    'frmMeuCadastro.php'
];

$mapaPermissoes = [

    'frmLancamento.php' => 'frmLancamentoLista.php',

    'frmMulta.php' => 'frmMultaLista.php',

    'frmBoletimCaixa.php' => 'frmBoletimCaixaLcto.php',

    'frmServProd.php' => 'frmServProdLista.php',

    'frmForcli.php' => 'frmForcliLista.php',

    'frmDespesas.php' => 'frmDespesasLista.php'
];

if (isset($mapaPermissoes[$currentPage])) {
    $currentPage = $mapaPermissoes[$currentPage];
}

if (in_array($currentPage, $paginasLivres)) {
    return;
}

$sqlTotal = "SELECT COUNT(*) AS total FROM userpermissoes WHERE idEmpresa = ? AND idUsuario = ?";

$totalPermissoes = ExSqlNET($sqlTotal, null,
    [
        $idEmpresa,
        $idUsuario
    ]
);

if (($totalPermissoes[0]['total'] ?? 0) > 0) {

    $sql = "SELECT 1 FROM userpermissoes WHERE idEmpresa = ? AND idUsuario = ? AND pagina = ?  LIMIT 1";
    $permissao = ExSqlNET($sql,null,
        [
            $idEmpresa,
            $idUsuario,
            $currentPage
        ]
    );

    if (empty($permissao)) {
        echo "
        <script>
            alert('Você não possui acesso a esta tela.');
            window.location.href='../user/frmHome.php';
        </script>
        ";
        exit;
    }
}

function podeAcessar($pagina){
    if (
        isset($_SESSION['AdminGeral']) &&
        $_SESSION['AdminGeral'] == 1
    ){
        return true;
    }
    if (
        !isset($_SESSION['permissoes']) ||
        empty($_SESSION['permissoes'])
    ){
        return true;
    }

    return in_array($pagina, $_SESSION['permissoes']);
}
?>