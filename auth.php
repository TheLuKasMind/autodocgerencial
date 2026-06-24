<?php
// auth.php
require_once __DIR__ . '/gerencial/vendor/autoload.php';
require_once __DIR__ . '/gerencial/base/verificaPlano.php';
require_once __DIR__ . '/gerencial/base/ambiente.php';
require_once __DIR__ . '/gerencial/base/connection.php';   // ← Adicione isso

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['AdminGeral']) || $_SESSION['AdminGeral'] != 1) {
    header("Location: Login"); 
    exit;
}

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/gerencial/admin/oauth-credentials.json');
$client->addScope(Google\Service\Drive::DRIVE);

if ($DEBUG_LOCAL === 1){
    $client->setRedirectUri('http://localhost/auth.php');
}else{
    $client->setRedirectUri('https://autodocoficial.com/auth.php');
}

$client->setAccessType('offline');

$tokenPath = __DIR__ . '/gerencial/admin/token.json';

if (isset($_GET['code'])) {
    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));
        
        // Salva o token no banco também (opcional)
        ExSqlNET("UPDATE config SET GoogleDriveLink = ?, GoogleDriveValidade = NOW() WHERE id = 1", 
                null, [json_encode($accessToken)]);
        
        // echo "✅ Token salvo com sucesso!<br><br>Redirecionando...";
        // echo '<script>setTimeout(() => window.location.href = "gerencial/Backup", 2200);</script>';
        header('Location: gerencial/Backup');
        exit;
    } catch (Exception $e) {
        echo "Erro: " . htmlspecialchars($e->getMessage());
    }
    exit;
}

// Se não tem code, gera o link e salva no banco
$authUrl = $client->createAuthUrl();

// Salva o link no banco
ExSqlNET("
    INSERT INTO config (id, GoogleDriveLink, GoogleDriveValidade) 
    VALUES (1, ?, NOW()) 
    ON DUPLICATE KEY UPDATE 
        GoogleDriveLink = VALUES(GoogleDriveLink),
        GoogleDriveValidade = NOW()
", null, [$authUrl]);

header("Location: " . $authUrl);
exit;