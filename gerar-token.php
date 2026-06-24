<?php
// gerar-token.php  ← na raiz do projeto
require_once __DIR__ . '/gerencial/vendor/autoload.php';
require_once __DIR__ . '/gerencial/base/verificaPlano.php';
require_once __DIR__ . '/gerencial/base/ambiente.php';

$ambiente = $DEBUG_LOCAL; 

if ($ambiente == 1) {
    $URL_BASE = 'http://localhost';
} else {
    $URL_BASE = 'https://autodocoficial.com';
}

$URL_REDIRECT_GOOGLE = $URL_BASE . '/gerar-token.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['AdminGeral']) || $_SESSION['AdminGeral'] != 1) {
    header("Location: Login");
    exit;
}

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/gerencial/admin/oauth-credentials.json');
$client->addScope(Google\Service\Drive::DRIVE);
$client->setRedirectUri($URL_REDIRECT_GOOGLE);
$client->setAccessType('offline');

$tokenPath = __DIR__ . '/gerencial/admin/token.json';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerar Token Google Drive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        :root{
            --primary:#f97316;
            --primary-dark:#ea580c;
        }

        .box {
            background: var(--card);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 700px;
            margin: 40px auto;
            text-align: center;
        }

        .success { color: #22c55e; }
        .warning { color: #f59e0b; }

        pre {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            text-align: left;
            font-size: 13px;
            max-height: 300px;
            overflow: auto;
            margin: 20px 0;
        }

        .btn-google {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 16px 32px;
            font-size: 17px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .btn-google:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(249, 115, 22, 0.4);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/gerencial/base/navbarUser.php'; ?>

<div class="content">
    <div class="page-title">Gerar Token Google Drive</div>
    <div class="subtitle">Autorização para envio automático de backups</div>

    <div class="box">

<?php
if (isset($_GET['code'])) {
    // Callback do Google
    echo "<h2 class='success'>✅ Código recebido do Google!</h2>";
    
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        $salvo = file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));
        
        if ($salvo !== false) {
            echo '<h2 class="success">🎉 TOKEN SALVO COM SUCESSO!</h2>';
            echo '<p>Arquivo criado/atualizado em: <strong>gerencial/admin/token.json</strong></p>';
            
            if (!isset($accessToken['refresh_token'])) {
                echo '<p class="warning">⚠️ Não veio refresh_token. O token pode expirar em 1 hora.</p>';
            }
            
            echo '<p>Redirecionando para a tela de Backup...</p>';
            echo '<script>
                setTimeout(function() {
                    window.location.href = "/?url=gerencial/Backup";
                }, 2800);
            </script>';
        } else {
            echo '<h2 style="color:red;">❌ Erro ao salvar o arquivo token.json</h2>';
        }
    } catch (Exception $e) {
        echo '<h2 style="color:red;">Erro ao processar token:</h2>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} 
else {
    // Tela inicial
    if (file_exists($tokenPath)) {
        $tokenData = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($tokenData);

        if (!$client->isAccessTokenExpired()) {
            echo '<h2 class="success">✅ Token já está válido!</h2>';
            echo '<p>Redirecionando para Backup...</p>';
            echo '<script>setTimeout(() => window.location.href = "/?url=gerencial/Backup", 1800);</script>';
            exit;
        } else {
            echo '<h2 style="color:#f59e0b;">⚠️ Token expirado</h2>';
        }
    }

    $authUrl = $client->createAuthUrl();
    echo '<p>Clique no botão abaixo para autorizar o Google Drive:</p>';
    echo '<a href="' . htmlspecialchars($authUrl) . '" class="btn-google">🔑 Autorizar Google Drive</a>';
}
?>

    </div>
</div>

</body>
</html>