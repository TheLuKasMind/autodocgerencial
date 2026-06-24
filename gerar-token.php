<?php
// gerar-token.php
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
    <title>Autorização Google Drive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        .box {
            max-width: 650px;
            margin: 40px auto;
            background: #fff;
            padding: 45px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        .success { color: #22c55e; }
        .warning { color: #f59e0b; }
        .btn-auth {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/gerencial/base/navbarUser.php'; ?>

<div class="content">
    <div class="page-title">Autorização Google Drive</div>
    <div class="subtitle">Necessário para enviar backups automaticamente</div>

    <div class="box">

<?php
if (isset($_GET['code'])) {
    echo "<h2 class='success'>✅ Código recebido com sucesso!</h2>";
    
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));
        
        echo '<h2 class="success">🎉 Token salvo com sucesso!</h2>';
        echo '<p>Redirecionando para a tela de Backup...</p>';
        
        echo '<script>
            setTimeout(function() {
                window.location.href = "/?url=gerencial/Backup";
            }, 2500);
        </script>';
        
    } catch (Exception $e) {
        echo '<p style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} 
else {
    // Tela inicial
    if (file_exists($tokenPath)) {
        $tokenData = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($tokenData);

        if (!$client->isAccessTokenExpired()) {
            echo '<h2 class="success">✅ Token já está válido!</h2>';
            echo '<script>setTimeout(() => window.location.href = "/?url=gerencial/Backup", 1800);</script>';
            exit;
        }
    }

    $authUrl = $client->createAuthUrl();
    echo '<a href="' . htmlspecialchars($authUrl) . '" class="btn-auth">🔑 Autorizar Acesso ao Google Drive</a>';
}
?>

    </div>
</div>

</body>
</html>