<?php
require_once __DIR__ . '/gerencial/vendor/autoload.php';
require_once  __DIR__ .'/gerencial/base/verificaPlano.php';
require_once  __DIR__ .'/gerencial/base/ambiente.php';

$ambiente = $DEBUG_LOCAL; 

if ($ambiente == 1) {
    $URL_BASE = 'http://localhost';
} else {
    $URL_BASE = 'https://autodocoficial.com';
}

$URL_BACKUP = $URL_BASE . '/?url=gerencial/Backup';
$URL_REDIRECT_GOOGLE = $URL_BASE . '/gerar-token.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

if (!isset($_SESSION['AdminGeral']) || $_SESSION['AdminGeral'] != 1) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/gerencial/admin/oauth-credentials.json');
$client->addScope(Google\Service\Drive::DRIVE);
$client->setRedirectUri($URL_REDIRECT_GOOGLE);
$client->setAccessType('offline');

$tokenPath = __DIR__ . '/gerencial/admin/token.json';

echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerar Token Google Drive - Autodoc</title>
    <style>
        body {font-family: Arial, sans-serif; padding: 50px; background: #f4f4f4;}
        .box {background: white; padding: 40px; border-radius: 12px; max-width: 800px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.1);}
        pre {background:#f8f8f8; padding:15px; overflow:auto; text-align:left; font-size:14px;}
        .success {color:green;}
        .warning {color:orange;}
    </style>
</head>
<body><div class="box">';

if (isset($_GET['code'])) {
    echo "<h2 class='success'>✅ Código recebido do Google!</h2>";
    
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        echo "<p><strong>Resposta do Google:</strong></p>";
        echo '<pre>' . htmlspecialchars(json_encode($accessToken, JSON_PRETTY_PRINT)) . '</pre>';

        // Salva o token (mesmo sem refresh_token)
        $salvo = file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));
        
        if ($salvo !== false) {
            echo '<h2 class="success">🎉 TOKEN SALVO COM SUCESSO!</h2>';
            echo '<p>Arquivo criado/atualizado em: <strong>gerencial/admin/token.json</strong></p>';
            
            if (!isset($accessToken['refresh_token'])) {
                echo '<p class="warning">⚠️ Atenção: Não veio refresh_token. O token pode expirar em 1 hora.</p>';
            }
            
            // Redireciona para a página de Backup
            echo '<script>
                setTimeout(function() {
                    window.location.href = "gerencial/Backup";
                }, 2500);
            </script>';
            
        } else {
            echo '<h2 style="color:red;">❌ Erro ao salvar o arquivo token.json</h2>';
        }
    } catch (Exception $e) {
        echo '<h2 style="color:red;">Erro ao processar token:</h2>';
        echo '<p>' . $e->getMessage() . '</p>';
    }
} 
else {
    // Tela inicial
    if (file_exists($tokenPath)) {
        $tokenData = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($tokenData);

        if ($client->isAccessTokenExpired()) {
            echo '<h2>Token expirado</h2>';
        } else {
            echo '<h2 class="success">✅ Token válido</h2>';
            echo '<p>Redirecionando para Backup...</p>';
            echo '<script>setTimeout(() => window.location.href = "gerencial/Backup", 1500);</script>';
            exit;
        }
    }

    $authUrl = $client->createAuthUrl();
    echo '<h2>Gerar Token Google Drive</h2>';
    echo '<a href="' . htmlspecialchars($authUrl) . '" style="font-size:20px; padding:15px 30px; background:#4285f4; color:white; text-decoration:none; border-radius:8px; display:inline-block;">🔑 Autorizar Acesso ao Google Drive</a>';
}
echo '<div style="
    background:#f3f4f6;
    padding:10px;
    border-radius:8px;
    margin-bottom:20px;
">
    Ambiente: <strong>' .
    ($ambiente == 1 ? 'LOCAL' : 'PRODUÇÃO')
    . '</strong>
</div>';