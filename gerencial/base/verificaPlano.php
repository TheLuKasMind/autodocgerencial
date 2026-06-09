<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;

if ($idEmpresa) {

    $sql = "SELECT ValidadePlano FROM empresa WHERE id = ?";
    $empresa = ExSqlNET($sql, null, [$idEmpresa]);
    $empresa = $empresa[0] ?? null;

    if ($empresa && $empresa['ValidadePlano'] < date('Y-m-d')) {
        session_destroy();
        header("Location: ../frmLogin.php?erro=plano_vencido");
        exit;
    }
}