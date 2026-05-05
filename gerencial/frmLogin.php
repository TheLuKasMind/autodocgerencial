<?php 
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
                u.Email As UserEmail
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
                        
                        if ($usuario['EmpresaEmail'] == $usuario['UserEmail']){
                            $_SESSION['UserAdmin'] = 1;
                        }else{
                            $_SESSION['UserAdmin'] = 0;
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
        margin-bottom: 20px;
    }

    h3 {
        margin-top: 25px;
        margin-bottom: 10px;
        color: #374151;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        font-size: 14px;
        color: #374151;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
    }

    .btn-login {
        width: 100%;
        padding: 12px;
        background: #f97316;
        border: none;
        color: white;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-login:hover {
        background: #ea580c;
    }

    .planos {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .plano {
        flex: 1;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        text-align: center;
    }

    .plano input {
        display: none;
    }

    .plano.selected {
        border-color: #f97316;
        background: #fff7ed;
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

        .login-form {
            padding: 40px;
        }

        .planos {
            flex-direction: column;
        }
    }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <!--<div class="login-info">-->
        <!--    <h1>Autodoc Gerencial</h1>-->
        <!--    <p>Sistema completo para gestão de despachantes e serviços.</p>-->
        <!--</div>-->
        
        <div class="login-info">
            <div style="text-align: center;">
                <!-- Logo -->
                <img src="img/logoo.png" 
                     alt="Autodoc Gerencial" 
                     style="width: 256px; 
                            height: 256px; 
                            object-fit: contain; 
                            border-radius: 28px; 
                            background: white; 
                            padding: 20px; 
                            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.28);
                            margin-bottom: 30px;">
        
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

            <form method="post">

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <div class="password-wrapper">
                        <input type="password" name="senha" id="senhaLogin" required>
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

                <div style="margin-top:15px;text-align:center;">
                    <a href="?cadastro=1">Criar conta</a>
                </div>

                <div style="margin-top:10px;text-align:center;">
                    <a href="javascript:void(0)" onclick="abrirRecuperar()">Esqueceu sua senha?</a>
                </div>

                <div id="modalRecuperar" style="
                    display:none;
                    position:fixed;
                    top:0;
                    left:0;
                    width:100%;
                    height:100%;
                    background:rgba(0,0,0,0.5);
                    align-items:center;
                    justify-content:center;
                ">
                
                <div style="
                    background:white;
                    padding:30px;
                    border-radius:10px;
                    width:350px;
                ">
                
                <h3>Recuperar senha</h3>
                
                <input type="email" id="emailRecuperar" placeholder="Digite seu e-mail"
                style="width:100%;padding:10px;margin-top:10px;">
                
                <button type="button" onclick="enviarRecuperacao()" class="btn-login"
                    style="margin-top:15px;">
                    Enviar
                </button>
                
                <div id="msgRecuperar" style="margin-top:10px;font-size:14px;"></div>
                
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
                    <input name="documento" id= "documento" oninput="mascararDocumento(this)">
                </div>

                <div class="form-group">
                    <label>Telefone</label>
                    <!-- <input name="telefone"> -->
                  <input type="text"
                        id="telefone"
                        name="telefone"
                        oninput="formatarTelefone(this)">
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

                        <strong><?= $pl['Nome'] ?></strong><br>

                        R$ <?= number_format($pl['Valor'],2,',','.') ?><br>

                        <small><?= $pl['Periodo'] ?></small>

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

                <div style="margin-top:15px;text-align:center;">
                    <a href="frmLogin.php">Voltar para login</a>
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
        document.getElementById("modalRecuperar").style.display="flex";
    }
    
    function enviarRecuperacao(){
    
        const email = document.getElementById("emailRecuperar").value;
    
        fetch("ajax/recuperarSenha.php",{
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"email="+encodeURIComponent(email)
        })
        .then(r=>r.json())
        .then(d=>{
    
            document.getElementById("msgRecuperar").innerHTML =
            "Se o e-mail existir no sistema, você receberá instruções para redefinir sua senha. Verifique sua caixa de email.";
    
        })
        .catch(err=>{
            console.log(err);
        });
    }


    </script>

</body>
<?php include 'base/footer-new.php'; ?>
</html>

