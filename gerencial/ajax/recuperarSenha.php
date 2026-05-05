<?php

include '../base/baseFuncoes.php';
require_once '../base/connection.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

if ($email == '') {

    echo json_encode([
        "ok" => false
    ]);

    exit;
}

enviarRecuperacaoSenha($email);

echo json_encode([
    "ok" => true
]);