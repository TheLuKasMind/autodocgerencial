<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';
require_once  __DIR__ .'/../base/ambiente.php';

$ambiente = $DEBUG_LOCAL; 
$tokenValido = false;
$tokenExpiraEm = null;

$arquivoToken = __DIR__ . '/token.json'; // ajuste o caminho

if (file_exists($arquivoToken)) {
    $token = json_decode(file_get_contents($arquivoToken), true);
    if (
        isset($token['access_token']) &&
        isset($token['created']) &&
        isset($token['expires_in'])
    ) {
        $tokenExpiraEm = $token['created'] + $token['expires_in'];
        if ($tokenExpiraEm > time()) {
            $tokenValido = true;
        }
    }
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_backup'])) {

    try {

        $senhaHash = '$2y$10$IR3ZfjgRqA7ADQn9rGy8/OQqugD5gl7NzGJwxxtMKXB0nQtB/j8gS';

        $senhaDigitada = $_POST['senha_backup'] ?? '';

        if (!password_verify($senhaDigitada, $senhaHash)) {

            $_SESSION['mensagem_erro'] = 'Senha inválida.';
            header('Location: Backup');
            exit;
        }

        $pdo = $dbGeralNET;

        $dataBackup = date('Y-m-d_H-i-s');      

        $pastaTemp = __DIR__ . '/backups_temp/';

        if (!is_dir($pastaTemp)) {
            mkdir($pastaTemp, 0777, true);
        }

        $arquivoSql = $pastaTemp . "backup_{$dataBackup}.sql";

        $sqlDump = "";
        $sqlDump .= "-- BACKUP COMPLETO\n";
        $sqlDump .= "-- DATA: " . date('d/m/Y H:i:s') . "\n\n";

        // LISTA TABELAS
        $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tabelas as $tabela) {

            $sqlDump .= "\n\n-- TABELA {$tabela}\n\n";

            // ESTRUTURA
            $createTable = $pdo->query("SHOW CREATE TABLE `$tabela`")->fetch();

            $sqlDump .= "DROP TABLE IF EXISTS `$tabela`;\n";
            $sqlDump .= $createTable['Create Table'] . ";\n\n";

            // DADOS
            $dados = $pdo->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dados as $linha) {

                $colunas = array_map(fn($c) => "`$c`", array_keys($linha));

                $valores = array_map(function ($valor) use ($pdo) {

                    if ($valor === null) {
                        return "NULL";
                    }

                    return $pdo->quote($valor);

                }, array_values($linha));

                $sqlDump .= "INSERT INTO `$tabela` (" .
                    implode(',', $colunas) .
                    ") VALUES (" .
                    implode(',', $valores) .
                    ");\n";
            }

            // SQL INDIVIDUAL DA TABELA
            $arquivoTabela = $pastaTemp . "{$tabela}.sql";

            $sqlTabela = "";
            $sqlTabela .= "-- TABELA {$tabela}\n\n";

            $sqlTabela .= "DROP TABLE IF EXISTS `$tabela`;\n";
            $sqlTabela .= $createTable['Create Table'] . ";\n\n";

            foreach ($dados as $linha) {

                $colunas = array_map(fn($c) => "`$c`", array_keys($linha));

                $valores = array_map(function ($valor) use ($pdo) {

                    if ($valor === null) {
                        return "NULL";
                    }

                    return $pdo->quote($valor);

                }, array_values($linha));

                $sqlTabela .= "INSERT INTO `$tabela` (" .
                    implode(',', $colunas) .
                    ") VALUES (" .
                    implode(',', $valores) .
                    ");\n";
            }

            file_put_contents($arquivoTabela, $sqlTabela);

        }

        // SALVA SQL
        file_put_contents($arquivoSql, $sqlDump);

        // ZIP
        $dataBackup = date('d_m_Y');

        $arquivoZip = $pastaTemp .
            "AutoDoc_Backup_{$dataBackup}.zip";

        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive não habilitado na hospedagem');
        }

        $zip = new ZipArchive();

        if ($zip->open($arquivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Erro ao criar ZIP');
        }

        // SQL
        $zip->addFile($arquivoSql, basename($arquivoSql));

        foreach (glob($pastaTemp . '*.sql') as $sqlTabela) {

            if (basename($sqlTabela) != basename($arquivoSql)) {

                $zip->addFile(
                    $sqlTabela,
                    'tabelas/' . basename($sqlTabela)
                );
            }
        }

        $zip->close();


        // LIMPA BUFFER
        if (ob_get_length()) {
            ob_end_clean();
        }

        //FAZ O ENVIO PRO DRIVE
        require_once 'googleDriveUpload.php';

        $resultado = enviarBackupGoogleDrive($arquivoZip);
        // echo '<pre>';
        // var_dump($resultado);
        // echo '</pre>';
        // exit;
        if ($resultado) {
            logBackup('Backup enviado com sucesso.');
            $_SESSION['mensagem_sucesso'] = '✅ Backup gerado e enviado para o Google Drive com sucesso.';
        } else {
            logBackup('Falha ao enviar backup.');
            $_SESSION['mensagem_erro'] = '❌ Falha ao enviar o backup para o Google Drive.';
        }

        // header('Location: Backup');
        // exit;

        /*
        // DOWNLOAD DESABILITADO
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($arquivoZip) . '"');
        header('Content-Length: ' . filesize($arquivoZip));
        header('Pragma: public');
        readfile($arquivoZip);
        */

        // LIMPA TEMP
        @unlink($arquivoSql);

        foreach (glob($pastaTemp . '*.sql') as $sqlTabela) {

            if (basename($sqlTabela) != basename($arquivoSql)) {
                @unlink($sqlTabela);
            }
        }

        @unlink($arquivoZip);
        header('Location: Backup');
        exit;

    } catch (Exception $e) {

        echo "<pre>";
        echo "ERRO:\n";
        echo $e->getMessage();
        echo "</pre>";
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Backup do Sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="stylesheet" href="/gerencial/css/home.css?v=15">

    <style>

        .backup-wrapper{
            max-width:700px;
            margin:0 auto;
        }

        .backup-card{
            background:#fff;
            border-radius:18px;
            padding:35px;
            box-shadow:0 10px 30px rgba(0,0,0,0.06);
            border:1px solid #fed7aa;
            text-align:center;
        }

        .backup-icon{
            width:90px;
            height:90px;
            margin:0 auto 20px;
            border-radius:50%;
            background:linear-gradient(135deg,#f97316,#ea580c);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:42px;
            color:#fff;
            box-shadow:0 12px 25px rgba(249,115,22,.3);
        }

        .backup-title{
            font-size:28px;
            font-weight:700;
            color:#222;
            margin-bottom:10px;
        }

        .backup-desc{
            font-size:15px;
            color:#666;
            line-height:1.7;
            margin-bottom:30px;
        }

        .backup-info{
            background:#fff7ed;
            border:1px solid #fdba74;
            color:#9a3412;
            border-radius:12px;
            padding:18px;
            text-align:left;
            margin-bottom:30px;
        }

        .backup-info ul{
            margin:10px 0 0 20px;
            padding:0;
        }

        .backup-info li{
            margin-bottom:8px;
        }

        .btn-backup{
            background:linear-gradient(135deg,#f97316,#ea580c);
            color:#fff;
            border:none;
            border-radius:14px;
            padding:18px 28px;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            transition:.25s ease;
            min-width:320px;
            box-shadow:0 12px 25px rgba(249,115,22,.25);
        }

        .btn-backup:hover{
            transform:translateY(-2px) scale(1.01);
            box-shadow:0 18px 35px rgba(249,115,22,.35);
        }

        .btn-backup:active{
            transform:scale(.98);
        }

        @media(max-width:700px){

            .backup-card{
                padding:25px;
            }

            .btn-backup{
                width:100%;
                min-width:100%;
            }
        }

        @keyframes loadingMove {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        .btn-auth {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 16px 28px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .25s ease;
            min-width: 320px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            box-shadow:
                0 8px 20px rgba(34, 197, 94, 0.25),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .btn-auth:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #16a34a, #15803d);
            box-shadow:
                0 15px 35px rgba(34, 197, 94, 0.35),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .btn-auth:active {
            transform: translateY(-1px);
        }

        .btn-auth:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }


        @media(max-width:700px) {
            .btn-auth {
                width: 100%;
                min-width: 100%;
            }
        }

    </style>
</head>

<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">Backup do Sistema</div>

    <?php if(isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert error">
            <?= $_SESSION['mensagem_erro'] ?>
        </div>
    <?php unset($_SESSION['mensagem_erro']); endif; ?>

    <?php if(isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert success">
            <?= $_SESSION['mensagem_sucesso'] ?>
        </div>
    <?php unset($_SESSION['mensagem_sucesso']); endif; ?>

    <div class="backup-wrapper">

        <div class="backup-card">

            <div class="backup-icon">
                💾
            </div>

            <div class="backup-title">
                Backup Completo do Sistema
            </div>

            <div class="backup-desc">
                Gere um backup completo do banco de dados do sistema.
                O backup será gerado e enviado automaticamente para o Google Drive.
            </div>

            <div class="backup-info">
                <strong>O backup irá incluir:</strong>
                <ul>
                    <li>Estrutura completa das tabelas</li>
                    <li>Todos os registros do sistema</li>
                    <li>Arquivo SQL completo</li>
                    <li>Arquivos CSV separados por tabela</li>
                    <li>Compactação automática em ZIP</li>
                </ul>
            </div>

            <form method="POST" id="formBackup">
                <div id="loadingBackup" style="display:none; margin-top:20px;">
                    <div style="font-weight:600; color:#ea580c;">
                        ⏳ Gerando backup e enviando para o Google Drive...
                    </div>

                    <div style="
                        margin-top:12px;
                        height:10px;
                        background:#eee;
                        border-radius:20px;
                        overflow:hidden;
                    ">
                        <div style="
                            height:100%;
                            width:100%;
                            background:linear-gradient(90deg,#f97316,#ea580c);
                            animation: loadingMove 1s linear infinite;
                        "></div>
                    </div>
                </div>

                <div style="margin-bottom:20px; text-align:left;">
                    <label style="font-weight:600;">Senha de Segurança</label>
                    <input type="password"  name="senha_backup" id="senha_backup" placeholder="Digite a senha do backup" required
                        style="
                            width:100%;
                            margin-top:8px;
                            padding:12px;
                            border-radius:10px;
                            border:1px solid #d6d6d6;
                            font-size:15px;
                        ">

                </div>
                <input type="hidden" name="gerar_backup" value="1">

                <?php if ($tokenValido): ?>
                    <div class="alert success" style="margin-bottom:15px;">
                        ✅ Google Drive autenticado

                        <?php if ($tokenExpiraEm): ?>
                            <br>
                            <small>
                                Token válido até:
                                <?= date('d/m/Y H:i:s', $tokenExpiraEm) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                <?php else: ?>

                    <div class="alert error" style="margin-bottom:15px;">
                        ⚠️ Google Drive não autenticado ou token expirado.
                    </div>

                <?php endif; ?>
                    <?php
                    if (!$tokenValido):

                        if ($DEBUG_LOCAL == 1) {
                            $redirectUrl = 'http://localhost/auth.php';
                        } else {
                            $redirectUrl = 'http://autodocoficial.com/auth.php';
                        }
                    ?>

                        <button
                            type="button"
                            class="btn-auth"
                            onclick="window.location.href='<?= $redirectUrl ?>'">
                            🔐 Autenticar com Google Drive
                        </button>

                    <?php endif; ?>

                <button type="submit"
                        name="gerar_backup"
                        class="btn-backup"
                        id="btnBackup"
                        <?= !$tokenValido ? 'disabled' : '' ?>>
                    ☁️ Gerar e Enviar Backup
                </button>
            </form>

        </div>

    </div>

</div>

<script>
document.getElementById('formBackup').addEventListener('submit', function() {
    document.getElementById('loadingBackup').style.display = 'block';
    const btn = document.getElementById('btnBackup');
    btn.disabled = true;
    btn.innerHTML = '⏳ Processando...';
});

</script>

</body>
</html>
