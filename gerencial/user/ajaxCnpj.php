<?php
require_once '../base/baseFuncoes.php';
require_once '../base/connection.php';

header('Content-Type: application/json');

$cnpj = $_GET['cnpj'] ?? '';

if (!$cnpj) {
    echo json_encode(['error' => 'CNPJ não informado']);
    exit;
}

function buscarDadosCNPJ($cnpj) {
    $url = "https://open.cnpja.com/office/$cnpj";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // evita travar
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => curl_error($ch)];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Erro ao decodificar JSON'];
    }

    return $data;
}

echo json_encode(buscarDadosCNPJ($cnpj));
exit;