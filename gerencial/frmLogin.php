<?php 
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'base/baseFuncoes.php'; 
require_once 'base/connection.php'; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$modoCadastro = isset($_GET['cadastro']);

$erro = '';
$sucesso = '';

/* ================= PLANOS ================= */

$sqlPlanos = "
    SELECT id, Nome, Valor, Periodo
    FROM planos
    WHERE Status = 1
    ORDER BY Valor ASC
";

$listaPlanos = ExSqlNET($sqlPlanos);


/* ================= LOGIN ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$modoCadastro) {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } else {

        $sql = "
            SELECT 
                u.id, 
                u.nome, 
                u.senha, 
                u.tipo, 
                u.idEmpresa, 
                u.AdminGeral,
                e.Status,
                e.ValidadePlano,
                e.Email As EmpresaEmail,
                u.Email As UserEmail,
                u.Cargo As Cargo,
                u.NaoVerDadosFinanceiro,
                e.LimiteUsuarios
            FROM user u
            INNER JOIN empresa e ON e.id = u.idEmpresa
            WHERE u.email = ?
              AND u.inativo = 0
            LIMIT 1
        ";

        $usuarios = ExSqlNET($sql, null, [$email]);

        if (empty($usuarios)) {
            $erro = 'E-mail ou senha inválidos.';
        } else {

            $usuario = $usuarios[0];

            if (!password_verify($senha, $usuario['senha'])) {
                $erro = 'E-mail ou senha inválidos.';
            } else {

                /* ===== VERIFICA STATUS ===== */

                if ($usuario['Status'] != 'ATIVA') { 
                    $erro = "Cadastro da empresa ainda não aprovado.";
                } else {

                    /* ===== VERIFICA VALIDADE ===== */

                    $dataHoje = date('Y-m-d');

                    if (!empty($usuario['ValidadePlano']) &&
                        $usuario['ValidadePlano'] != '0000-00-00' &&
                        strtotime($usuario['ValidadePlano']) < strtotime($dataHoje)) {

                        $erro = "Plano contratado está VENCIDO. Entre em contato com o suporte.";

                    } else {

                        /* ===== LOGIN OK ===== */

                        $_SESSION['usuario_id']   = $usuario['id'];
                        $_SESSION['usuario_nome'] = $usuario['nome'];
                        $_SESSION['usuario_tipo'] = $usuario['tipo'];
                        $_SESSION['AdminGeral'] = $usuario['AdminGeral'];
                        $_SESSION['idEmpresa'] = $usuario['idEmpresa'];
                        $_SESSION['usuario_cargo'] = $usuario['Cargo'];
                        
                        $_SESSION['usuario_config_NaoVerDadosFinanceiro'] = $usuario['NaoVerDadosFinanceiro'];
                        
                        if ($usuario['EmpresaEmail'] == $usuario['UserEmail']){
                            $_SESSION['UserAdmin'] = 1;
                        }else{
                            $_SESSION['UserAdmin'] = 0;
                        }
                        
                        $_SESSION['empresa_limiteUsuarios'] = $usuario['LimiteUsuarios'];

                        /* ===== PERMISSÕES DE ACESSO A TELAS ===== */
                        $sql = "SELECT pagina FROM userpermissoes WHERE idEmpresa = ? AND idUsuario = ?";
                        $permissoes = ExSqlNET($sql,null,[$_SESSION['idEmpresa'],$_SESSION['usuario_id']]);
                        $_SESSION['permissoes'] = [];
                        if (!empty($permissoes)) {
                            foreach ($permissoes as $permissao) {
                                $_SESSION['permissoes'][] =
                                $permissao['pagina'];
                            }
                        }

                        header("Location: user/frmHome.php");
                        exit;
                    }
                }
            }
        }
    }
}


/* ================= CADASTRO ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $modoCadastro) {

    global $dbGeralNET;

    $empresaNome = trim($_POST['empresaNome'] ?? '');
    $documento   = trim($_POST['documento'] ?? '');
    $telefone    = trim($_POST['telefone'] ?? '');
    $emailEmp    = trim($_POST['emailEmpresa'] ?? '');
    $plano       = $_POST['plano'] ?? null;

    $nomeUser  = trim($_POST['nome'] ?? '');
    $emailUser = trim($_POST['email'] ?? '');
    $senhaUser = $_POST['senha'] ?? '';

    $documentoVerificar = preg_replace('/\D/', '', $_POST['documento'] ?? '');
    if (!empty($documentoVerificar)) {

        $sqlVerifica = "SELECT id FROM empresa WHERE documento = ? LIMIT 1";
        $existe = ExSqlNET($sqlVerifica, null, [$documento]);

        if (!empty($existe)) {
            $erro = "Já existe uma empresa cadastrada com este CNPJ/CPF.";
        }
    }

    $emailVerificar = trim($_POST['emailEmpresa'] ?? '');
    if (!empty($emailVerificar)) {

        $sqlVerificaEmail = "SELECT id FROM empresa WHERE email = ? LIMIT 1";
        $existeEmail = ExSqlNET($sqlVerificaEmail, null, [$emailVerificar]);

        if (!empty($existeEmail)) {
            $erro = "Já existe uma empresa cadastrada com este e-mail.";
        }
    }

    if ($empresaNome == '' || $nomeUser == '' || $emailUser == '' || $senhaUser == '') {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {

        // Verifica documento duplicado
        if (!empty($documento)) {

            $sqlVerifica = "SELECT id FROM empresa WHERE documento = ? LIMIT 1";
            $existe = ExSqlNET($sqlVerifica, null, [$documento]);

            if (!empty($existe)) {
                $erro = "Já existe uma empresa cadastrada com este CNPJ/CPF.";
            }
        }

        // Só continua se não tiver erro
        if ($erro == '') {

            $senhaHash = password_hash($senhaUser, PASSWORD_DEFAULT);

            try {

                $dbGeralNET->beginTransaction();

                // INSERE EMPRESA
                $stmt = $dbGeralNET->prepare("
                    INSERT INTO empresa 
                    (nome, documento, telefone, email, plano, status)
                    VALUES (?, ?, ?, ?, ?, 'PENDENTE')
                ");

                $stmt->execute([
                    $empresaNome,
                    $documento,
                    $telefone,
                    $emailEmp,
                    $plano
                ]);

                $idEmpresa = $dbGeralNET->lastInsertId();

                // INSERE USUÁRIO ADMIN
                $stmt = $dbGeralNET->prepare("
                    INSERT INTO user 
                    (nome, email, senha, tipo, idEmpresa, inativo)
                    VALUES (?, ?, ?, 2, ?, 1)
                ");

                $stmt->execute([
                    $nomeUser,
                    $emailUser,
                    $senhaHash,
                    $idEmpresa
                ]);

                $dbGeralNET->commit();

                header("Location: frmLogin.php?sucesso=1");
                exit;

            } catch (Exception $e) {

                $dbGeralNET->rollBack();
                $erro = "Erro ao cadastrar, contato o adminstrado. Erro:" . $e;
            }
        }
    }
}


if (isset($_GET['sucesso'])) {
    $sucesso = "Cadastro realizado com sucesso! Sua solicitação será analisada e você receberá um e-mail quando for aprovado.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autodoc Gerencial</title>
    <link rel="icon" href="img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

    .modal-recuperar{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(15,23,42,.55);
        backdrop-filter:blur(6px);
        z-index:9999;
        align-items:center;
        justify-content:center;
        padding:20px;
        animation:fadeIn .25s ease;
    }

    .modal-box{
        width:100%;
        max-width:420px;
        background:white;
        border-radius:28px;
        padding:35px;
        position:relative;
        box-shadow:
            0 30px 60px rgba(0,0,0,.20),
            0 10px 25px rgba(0,0,0,.08);
        animation:modalUp .25s ease;
    }

    .modal-close{
        position:absolute;
        top:16px;
        right:16px;
        width:38px;
        height:38px;
        border:none;
        border-radius:12px;
        background:#f8fafc;
        color:#64748b;
        font-size:18px;
        cursor:pointer;
        transition:.2s ease;
    }

    .modal-close:hover{
        background:#fff7ed;
        color:#ea580c;
        transform:rotate(90deg);
    }

    .modal-icon{
        width:78px;
        height:78px;
        border-radius:22px;
        background:linear-gradient(135deg,#fff7ed,#ffedd5);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:34px;
        margin:0 auto 22px auto;
        box-shadow:
            inset 0 0 0 1px #fed7aa;
    }

    .modal-box h3{
        margin:0;
        text-align:center;
        font-size:28px;
        font-weight:800;
        color:#111827;
    }

    .modal-text{
        margin-top:14px;
        text-align:center;
        color:#64748b;
        line-height:1.6;
        font-size:15px;
    }

    .modal-input-group{
        margin-top:28px;
    }

    .modal-input-group input{
        width:100%;
        height:60px;
        border-radius:18px;
        border:2px solid #e2e8f0;
        padding:0 18px;
        font-size:15px;
        font-weight:600;
        outline:none;
        transition:.25s ease;
        background:#fff;
    }

    .modal-input-group input:focus{
        border-color:#f97316;
        box-shadow:
            0 0 0 5px rgba(249,115,22,.12);
    }

    .msg-recuperar{
        margin-top:18px;
        padding:14px;
        border-radius:14px;
        background:#f0fdf4;
        color:#166534;
        font-size:14px;
        line-height:1.5;
        display:none;
        border:1px solid #bbf7d0;
    }

    @keyframes fadeIn{
        from{
            opacity:0;
        }
        to{
            opacity:1;
        }
    }

    @keyframes modalUp{
        from{
            opacity:0;
            transform:translateY(25px) scale(.96);
        }
        to{
            opacity:1;
            transform:translateY(0) scale(1);
        }
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #f4f6f8;
    }

    .login-wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }

    .login-info {
        flex: 1.2;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        padding: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-info h1 {
        font-size: 36px;
        margin-bottom: 15px;
    }

    .login-info p {
        font-size: 18px;
        opacity: .9;
    }

    .login-form {
        flex: 1;
        background: white;
        padding: 60px 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-form h2 {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 10px;
        letter-spacing: -1px;
        position: relative;
    }

    .login-form h2::after {
        content: "";
        width: 70px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        display: block;
        margin-top: 12px;
    }

    h3 {
        margin-top: 25px;
        margin-bottom: 10px;
        color: #374151;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: #475569;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        letter-spacing: .3px;
    }

    .form-group input {
        width: 100%;
        height: 58px;
        padding: 0 18px;
        border-radius: 16px;
        border: 2px solid #dbe3ec;
        background: #ffffff;
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        transition: .28s ease;
        outline: none;
        box-shadow:
            0 4px 10px rgba(15, 23, 42, .04);
    }

    .form-group input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .form-group input:hover {
        border-color: #fb923c;
        box-shadow:
            0 8px 18px rgba(249, 115, 22, .10);
    }

    .form-group input:focus {
        border-color: #f97316;
        background: #fff;
        transform: translateY(-1px);
        box-shadow:
            0 0 0 5px rgba(249, 115, 22, .14),
            0 14px 30px rgba(249, 115, 22, .18);
    }

    .btn-login {
        width: 100%;
        padding: 13px 18px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .2px;
        cursor: pointer;
        transition: .25s ease;
        box-shadow:
            0 10px 20px rgba(249, 115, 22, .18),
            0 4px 8px rgba(249, 115, 22, .10);
        margin-top: 12px;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #fb923c, #f97316);
        box-shadow:
            0 14px 28px rgba(249, 115, 22, .24),
            0 8px 14px rgba(249, 115, 22, .16);
    }

    .btn-login:active {
        transform: scale(.98);
    }

    .planos {
        display: flex;
        gap: 18px;
        margin-top: 18px;
    }

    .plano {
        flex: 1;
        position: relative;
        padding: 24px 20px;
        border-radius: 22px;
        background: white;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        text-align: center;
        transition: .28s ease;
        overflow: hidden;
        box-shadow:
            0 10px 25px rgba(15, 23, 42, .05);
    }

    .plano::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: transparent;
        transition: .28s ease;
    }

    .plano input {
        display: none;
    }

    .plano strong {
        display: block;
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 14px;
    }

    .plano .valor {
        font-size: 34px;
        font-weight: 900;
        color: #ea580c;
        line-height: 1;
    }

    .plano .valor small {
        font-size: 15px;
        font-weight: 600;
        color: #64748b;
    }

    .plano .periodo {
        display: inline-block;
        margin-top: 18px;
        padding: 8px 14px;
        border-radius: 999px;
        background: #fff7ed;
        color: #ea580c;
        font-size: 13px;
        font-weight: 700;
    }

    .plano:hover {
        transform: translateY(-6px);
        border-color: #fdba74;
        box-shadow:
            0 20px 45px rgba(249, 115, 22, .16);
    }

    .plano.selected {
        border-color: #f97316;
        background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
        box-shadow:
            0 22px 50px rgba(249, 115, 22, .22);
    }

    .plano.selected::before {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .plano.selected strong {
        color: #c2410c;
    }

    .login-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
    }

    .box-msg {
        background: #ecfeff;
        border-left: 4px solid #06b6d4;
        padding: 12px;
        border-radius: 8px;
        margin-top: 15px;
        color: #0c4a6e;
    }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-wrapper input {
        width: 100%;
        padding-right: 45px;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        cursor: pointer;
        color: #6b7280;
        display: flex;
        align-items: center;
    }

    .toggle-password:hover {
        color: #f97316;
    }

    @media (max-width:900px) {
        .login-wrapper {
            flex-direction: column;
        }

        .login-info {
            padding: 40px;
            text-align: center;
        }

        .planos {
            flex-direction: column;
        }
    }

    .login-links {
        margin-top: 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .link-button {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 11px 14px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: .25s ease;
        cursor: pointer;
    }

    .primary-link {
        background: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
    }

    .primary-link:hover {
        background: #ffedd5;
        border-color: #fb923c;
        transform: translateY(-1px);
    }

    .secondary-link {
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .secondary-link:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .back-login-wrapper {
        margin-top: 18px;
    }

    .back-login-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: .25s ease;
    }

    .back-login-btn:hover {
        background: #fff7ed;
        border-color: #fdba74;
        color: #ea580c;
        transform: translateY(-1px);
    }

    .back-login-btn span {
        font-size: 16px;
    }

    .login-form {
        flex: 1;
        background: white;
        padding: 70px 90px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }

    .login-form::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .login-subtitle {
        color: #64748b;
        font-size: 15px;
        line-height: 1.5;
        margin-bottom: 35px;
    }


    /* Autofill Chrome/Edge/Opera */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    textarea:-webkit-autofill,
    select:-webkit-autofill{
        -webkit-text-fill-color:#0f172a !important;
        font-weight:700 !important;
        font-size:15px !important;

        transition: background-color 9999s ease-in-out 0s;

        -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
        box-shadow: 0 0 0px 1000px #ffffff inset !important;
    }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <div class="login-info">
            <div style="text-align: center;">
                <!-- Logo -->
                <a href="index.php">
                    <img src="img/logoo.png" alt="Autodoc Gerencial" style="width: 256px; 
                        height: 256px; 
                        object-fit: contain; 
                        border-radius: 28px; 
                        background: white; 
                        padding: 20px; 
                        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.28);
                        margin-bottom: 30px;
                        transition: .25s ease;
                        cursor: pointer;">
                </a>

                <!-- Título -->
                <h1 style="font-size: 37px; margin-bottom: 10px; color: white;">
                    Autodoc Gerencial
                </h1>

                <!-- Subtítulo -->
                <p style="font-size: 17.5px; opacity: 0.93; max-width: 390px; margin: 0 auto; line-height: 1.4;">
                    Gestão simples, moderna e eficiente para despachantes e empresas automotivas.
                </p>
            </div>
        </div>


        <div class="login-form">

            <?php if (!$modoCadastro): ?>

            <h2>Acesso ao sistema</h2>

            <p class="login-subtitle">
                Entre com suas credenciais para acessar a plataforma.
            </p>

            <form method="post">

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" autocomplete="email" required>
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <div class="password-wrapper">
                        <input type="password" name="senha" autocomplete="current-password"id="senhaLogin" id="senhaLogin" required>
                        <span class="toggle-password" onclick="toggleSenha('senhaLogin', this)"></span>
                    </div>
                </div>

                <?php if ($erro): ?>
                <div class="login-error"><?= $erro ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                <div class="box-msg"><?= $sucesso ?></div>
                <?php endif; ?>

                <button type="submit" class="btn-login">Entrar</button>

                <div class="login-links">

                    <a href="?cadastro=1" class="link-button primary-link">
                        Criar conta
                    </a>

                    <button type="button" class="link-button secondary-link" onclick="abrirRecuperar()" formnovalidate>
                        Esqueceu sua senha?
                    </button>

                </div>

                <div id="modalRecuperar" class="modal-recuperar">

                    <div class="modal-box">

                        <button type="button"
                                class="modal-close"
                                onclick="fecharRecuperar()">
                            ✕
                        </button>

                        <div class="modal-icon">
                            🔐
                        </div>

                        <h3>Recuperar senha</h3>

                        <p class="modal-text">
                            Informe seu e-mail para receber as instruções de redefinição de senha.
                        </p>

                        <div class="modal-input-group">
                            <input type="email"
                                id="emailRecuperar"
                                name="emailRecuperar"
                                placeholder="Digite seu e-mail">
                        </div>

                        <button type="button"
                                onclick="enviarRecuperacao()"
                                class="btn-login">
                            Enviar recuperação
                        </button>

                        <div id="msgRecuperar" class="msg-recuperar"></div>

                    </div>

                </div>
                </div>

            </form>

            <?php else: ?>

            <h2>Criar Conta</h2>

            <form method="post">

                <h3>Dados da Empresa</h3>

                <div class="form-group">
                    <label>Nome da Empresa *</label>
                    <input name="empresaNome" required>
                </div>

                <div class="form-group">
                    <label>CNPJ / CPF</label>
                    <input name="documento" id="documento" oninput="mascararDocumento(this)">
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <!-- <input name="telefone"> -->
                    <input type="text" id="telefone" name="telefone" oninput="formatarTelefone(this)">
                </div>

                <div class="form-group">
                    <label>E-mail Empresa</label>
                    <input name="emailEmpresa">
                </div>

                <h3>Plano</h3>

                <div class="planos">

                    <?php foreach ($listaPlanos as $i => $pl): ?>

                    <label class="plano <?= $i == 0 ? 'selected' : '' ?>">
                        <input type="radio" name="plano" value="<?= $pl['id'] ?>" <?= $i == 0 ? 'checked' : '' ?>>
                        <strong><?= $pl['Nome'] ?></strong>
                        <div class="valor">
                            R$ <?= number_format($pl['Valor'],2,',','.') ?>
                        </div>
                        <div class="periodo">
                            <?= $pl['Periodo'] ?>
                        </div>
                    </label>

                    <?php endforeach; ?>

                </div>

                <h3>Usuário Administrador</h3>

                <div class="form-group">
                    <label>Nome *</label>
                    <input name="nome" required>
                </div>

                <div class="form-group">
                    <label>E-mail *</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Senha *</label>
                    <div class="password-wrapper">
                        <input type="password" name="senha" id="senhaCadastro" required>
                        <span class="toggle-password" onclick="toggleSenha('senhaCadastro', this)"></span>
                    </div>
                </div>

                <?php if ($erro): ?>
                <div class="login-error"><?= $erro ?></div>
                <?php endif; ?>

                <button class="btn-login">Cadastrar</button>

                <!-- <div style="margin-top:15px;text-align:center;">
                    <a href="frmLogin.php">Voltar para login</a>
                </div> -->

                <div class="back-login-wrapper">
                    <a href="frmLogin.php" class="back-login-btn">
                        <span>←</span>
                        Voltar para login
                    </a>
                </div>

            </form>

            <?php endif; ?>

        </div>
    </div>

    <script>
    window.onclick = function(event) {
        let modal = document.getElementById("modalRecuperar");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    document.querySelectorAll('.plano').forEach(p => {
        p.addEventListener('click', () => {
            document.querySelectorAll('.plano').forEach(x => x.classList.remove('selected'));
            p.classList.add('selected');
            p.querySelector('input').checked = true;
        });
    });

    function mascararDocumento(campo) {
        let valor = campo.value.replace(/\D/g, '');

        if (valor.length <= 11) {
            // CPF: 000.000.000-00
            valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
            valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
            valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            // CNPJ: 00.000.000/0000-00
            valor = valor.replace(/^(\d{2})(\d)/, "$1.$2");
            valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            valor = valor.replace(/\.(\d{3})(\d)/, ".$1/$2");
            valor = valor.replace(/(\d{4})(\d)/, "$1-$2");
        }

        campo.value = valor;
    }

    function formatarTelefone(input) {
        let telefone = input.value.replace(/\D/g, '');

        if (telefone.length > 11) {
            telefone = telefone.slice(0, 11);
        }

        if (telefone.length > 10) {
            // Celular
            telefone = telefone.replace(/^(\d{2})(\d{5})(\d{0,4})/, "($1) $2-$3");
        } else if (telefone.length > 6) {
            // Fixo
            telefone = telefone.replace(/^(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
        } else if (telefone.length > 2) {
            telefone = telefone.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
        } else {
            telefone = telefone.replace(/^(\d*)/, "($1");
        }

        input.value = telefone;
    }

    const eyeOpen = `
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
    fill="none" stroke="currentColor" stroke-width="2"
    stroke-linecap="round" stroke-linejoin="round"
    viewBox="0 0 24 24">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    </svg>
    `;

    const eyeClosed = `
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
    fill="none" stroke="currentColor" stroke-width="2"
    stroke-linecap="round" stroke-linejoin="round"
    viewBox="0 0 24 24">
        <path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.77 21.77 0 015.06-5.94"/>
        <path d="M1 1l22 22"/>
        <path d="M9.9 9.9A3 3 0 0012 15a3 3 0 002.1-.9"/>
    </svg>
    `;

    function toggleSenha(idCampo, elemento) {
        const input = document.getElementById(idCampo);

        if (input.type === "password") {
            input.type = "text";
            elemento.innerHTML = eyeOpen;
        } else {
            input.type = "password";
            elemento.innerHTML = eyeClosed;
        }
    }

    // inicia todos como olho fechado
    document.querySelectorAll(".toggle-password").forEach(el => {
        el.innerHTML = eyeClosed;
    });


    function abrirRecuperar(){
        const modal = document.getElementById("modalRecuperar");
        const inputEmail = document.getElementById("emailRecuperar");
        modal.style.display = "flex";
        setTimeout(() => {
            inputEmail.focus();
        }, 150);
    }

    function fecharRecuperar(){
        document.getElementById("modalRecuperar").style.display = "none";
        document.getElementById("msgRecuperar").style.display = "none";
        document.getElementById("emailRecuperar").value = "";
    }

    function enviarRecuperacao(){
        const email = document.getElementById("emailRecuperar").value;
        if(email.trim() === ""){
            alert("Digite seu e-mail.");
            return;
        }
        fetch("ajax/recuperarSenha.php",{
            method:"POST",
            headers:{
                'Content-Type':'application/x-www-form-urlencoded'
            },
            body:"email="+encodeURIComponent(email)
        })
        .then(r => r.json())
        .then(d => {
            const msg = document.getElementById("msgRecuperar");
            msg.style.display = "block";
            msg.innerHTML =
            "Se o e-mail existir no sistema, você receberá instruções para redefinir sua senha.";
        })
        .catch(err => {
            console.log(err);
        });
    }

    window.onload = function() {
        if (!window.location.hash) {
            window.location = window.location + '#loaded';
            window.location.reload(true);
        }
    };

    window.addEventListener("click", function(event){

        const modal = document.getElementById("modalRecuperar");

        if(event.target === modal){
            fecharRecuperar();
        }
    });

    document.addEventListener("keydown", function(e){

        if(e.key === "Escape"){
            fecharRecuperar();
        }
    });

    </script>

</body>
<?php include 'base/footer-new.php'; ?>

</html>