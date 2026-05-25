<?php
include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../frmLogin.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$mensagem = '';

/* ================= SALVAR ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dadosCadastro = [
        'id' => $usuarioId,
        'EstadoCivil' => $_POST['EstadoCivil'] ?? '',
        'NumOab' => $_POST['OAB'] ?? '',
        'Rua' => $_POST['Rua'] ?? '',
        'NumeroEndereco' => $_POST['NumeroEndereco'] ?? '',
        'Cidade' => $_POST['Cidade'] ?? '',
        'UF' => $_POST['UF'] ?? '',
        'CEP' => $_POST['CEP'] ?? '',
        'LogoBase64' => $_POST['LogoBase64'] ?? '',
        'Contato' => $_POST['Numero'] ?? '',
        'Documento' => $_POST['Documento'] ?? '',
        'Bairro' => $_POST['Bairro'] ?? ''
    ];

    $retorno = MeuCadastro($dadosCadastro, "ATUALIZAR");

    if ($retorno === "") {
        $mensagem = 'Cadastro atualizado com sucesso!';
    } else {
        $mensagem = 'Erro ao atualizar cadastro: ' . $retorno;
    }
}

/* ================= CONSULTA ================= */

$dadosUsuario = ExSqlNET(
    "SELECT Nome, Email, EstadoCivil, NumOab, Rua, NumeroEndereco,
            Bairro, Cidade, UF, CEP, LogoBase64,
            Contato As Numero, Documento
    FROM user
    WHERE id = ?",
    null,
    [$usuarioId]
);

if (!$dadosUsuario) {
    die('Usuário não encontrado.');
}

$usuario = $dadosUsuario[0];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Cadastro</title>

    <link rel="stylesheet" href="../css/base.css?v=15">
    <link rel="icon" href="../img/favicon.png">

    <style>


        .content{
            padding:30px;
        }

        .page-title{
            font-size:30px;
            font-weight:800;
            color:#0f172a;
            margin-bottom:25px;
        }

        .perfil-card{
            background:#fff;
            border-radius:18px;
            padding:30px;
            border:1px solid #e2e8f0;
            box-shadow:0 10px 30px rgba(15,23,42,.05);
        }

        .success-msg{
            background:#dcfce7;
            color:#166534;
            padding:14px 16px;
            border-radius:12px;
            margin-bottom:25px;
            font-weight:600;
            border:1px solid #bbf7d0;
        }

        .tabs-config{
            display:flex;
            gap:10px;
            margin-bottom:30px;
            flex-wrap:wrap;
        }

        .tab-btn{
            border:none;
            background:#e2e8f0;
            color:#334155;
            padding:12px 18px;
            border-radius:12px;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
            transition:.2s ease;
        }

        .tab-btn:hover{
            background:#cbd5e1;
        }

        .tab-btn.active{
            background:linear-gradient(135deg,#f97316,#ea580c);
            color:#fff;
            box-shadow:0 8px 18px rgba(249,115,22,.25);
        }

        .tab-content{
            display:none;
            animation:fade .2s ease;
        }

        .tab-content.active{
            display:block;
        }

        @keyframes fade{
            from{
                opacity:0;
                transform:translateY(5px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .form-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
            gap:22px;
        }

        .form-group{
            display:flex;
            flex-direction:column;
        }

        .form-group label{
            font-size:13px;
            font-weight:700;
            color:#475569;
            margin-bottom:8px;
            text-transform:uppercase;
            letter-spacing:.4px;
        }

        .form-group input,
        .form-group select{
            width:100%;
            height:48px;
            border:1px solid #cbd5e1;
            border-radius:12px;
            padding:0 14px;
            background:#fff;
            color:#0f172a;
            font-size:15px;
            transition:.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus{
            outline:none;
            border-color:#f97316;
            box-shadow:0 0 0 4px rgba(249,115,22,.12);
        }

        .form-group input[readonly]{
            background:#f8fafc;
            color:#64748b;
            cursor:not-allowed;
        }

        .upload-box{
            border:2px dashed #cbd5e1;
            border-radius:14px;
            padding:20px;
            background:#f8fafc;
            transition:.2s ease;
        }

        .upload-box:hover{
            border-color:#f97316;
            background:#fff7ed;
        }

        #LogoArquivo{
            margin-top:12px;
        }

        .logo-preview{
            margin-top:20px;
            max-height:90px;
            border-radius:8px;
            border:1px solid #ddd;
            padding:5px;
            background:#fafafa;
        }

        .footer-actions{
            margin-top:30px;
            display:flex;
            justify-content:flex-end;
        }

        .btn-salvar{
            background:linear-gradient(135deg,#f97316,#ea580c);
            color:#fff;
            border:none;
            border-radius:14px;
            padding:15px 28px;
            font-size:15px;
            font-weight:700;
            cursor:pointer;
            transition:.2s ease;
            box-shadow:0 10px 25px rgba(249,115,22,.22);
        }

        .btn-salvar:hover{
            transform:translateY(-1px);
            box-shadow:0 16px 30px rgba(249,115,22,.30);
        }

        .btn-salvar:active{
            transform:scale(.98);
        }

        @media(max-width:768px){

            .content{
                padding:18px;
            }

            .perfil-card{
                padding:22px;
            }

            .form-grid{
                grid-template-columns:1fr;
                gap:18px;
            }

            .footer-actions{
                justify-content:stretch;
            }

            .btn-salvar{
                width:100%;
            }
        }

    </style>

</head>

<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">
        Meu Cadastro
    </div>

    <div class="perfil-card">

        <?php if ($mensagem): ?>
            <div class="success-msg">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- ABAS -->

            <div class="tabs-config">

                <button type="button"
                        class="tab-btn active"
                        data-tab="tab-dados">

                    Dados Cadastrais

                </button>

                <button type="button"
                        class="tab-btn"
                        data-tab="tab-endereco">

                    Endereço

                </button>

                <button type="button"
                        class="tab-btn"
                        data-tab="tab-logo">

                    Logo

                </button>

            </div>

            <!-- DADOS -->

            <div class="tab-content active" id="tab-dados">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Nome</label>

                        <input type="text"
                            value="<?= htmlspecialchars($usuario['Nome'] ?? '') ?>"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label>Documento</label>

                        <input type="text"
                            name="Documento"
                            value="<?= htmlspecialchars($usuario['Documento'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Email</label>

                        <input type="email"
                            value="<?= htmlspecialchars($usuario['Email'] ?? '') ?>"
                            readonly>
                    </div>

                    <div class="form-group">

                        <label>Estado Civil</label>

                        <select name="EstadoCivil">

                            <option value="0">Selecione</option>

                            <option value="1" <?= (($usuario['EstadoCivil'] ?? '') == 1) ? 'selected' : '' ?>>
                                Solteiro(a)
                            </option>

                            <option value="2" <?= (($usuario['EstadoCivil'] ?? '') == 2) ? 'selected' : '' ?>>
                                Casado(a)
                            </option>

                            <option value="3" <?= (($usuario['EstadoCivil'] ?? '') == 3) ? 'selected' : '' ?>>
                                Divorciado(a)
                            </option>

                            <option value="4" <?= (($usuario['EstadoCivil'] ?? '') == 4) ? 'selected' : '' ?>>
                                Viúvo(a)
                            </option>

                            <option value="5" <?= (($usuario['EstadoCivil'] ?? '') == 5) ? 'selected' : '' ?>>
                                União estável
                            </option>

                        </select>

                    </div>

                    <div class="form-group">
                        <label>Número OAB</label>

                        <input type="text"
                            name="OAB"
                            value="<?= htmlspecialchars($usuario['NumOab'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Contato</label>

                        <input type="text"
                            name="Numero"
                            value="<?= htmlspecialchars($usuario['Numero'] ?? '') ?>">
                    </div>

                </div>

            </div>

            <!-- ENDEREÇO -->

            <div class="tab-content" id="tab-endereco">

                <div class="form-grid">

                    <div class="form-group">
                        <label>CEP</label>

                        <input type="text"
                            name="CEP"
                            id="CEP"
                            value="<?= htmlspecialchars($usuario['CEP'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>UF</label>

                        <input type="text"
                            maxlength="2"
                            name="UF"
                            value="<?= htmlspecialchars($usuario['UF'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Cidade</label>

                        <input type="text"
                            name="Cidade"
                            value="<?= htmlspecialchars($usuario['Cidade'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Bairro</label>

                        <input type="text"
                            name="Bairro"
                            value="<?= htmlspecialchars($usuario['Bairro'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Rua</label>

                        <input type="text"
                            name="Rua"
                            value="<?= htmlspecialchars($usuario['Rua'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Número</label>

                        <input type="text"
                            name="NumeroEndereco"
                            value="<?= htmlspecialchars($usuario['NumeroEndereco'] ?? '') ?>">
                    </div>

                </div>

            </div>

            <!-- LOGO -->

            <div class="tab-content" id="tab-logo">

                <div class="upload-box">

                    <label>Logo do Sistema</label>

                    <input type="file"
                        id="LogoArquivo"
                        accept="image/*">

                    <input 
                        type="hidden" 
                        name="LogoBase64" 
                        id="LogoBase64"
                        value="<?= htmlspecialchars($usuario['LogoBase64'] ?? '') ?>"
                    >

                    <img 
                        id="previewLogo"
                        src="<?= !empty($usuario['LogoBase64']) ? $usuario['LogoBase64'] : '' ?>"
                        class="logo-preview"
                        style="<?= empty($usuario['LogoBase64']) ? 'display:none;' : '' ?>"
                    >

                </div>

            </div>

            <div class="footer-actions">

                <button type="submit" class="btn-salvar">
                    💾 Salvar Alterações
                </button>

            </div>

        </form>

    </div>

</div>

<script>

    /* ================= ABAS ================= */

    document.querySelectorAll('.tab-btn').forEach(btn => {

        btn.addEventListener('click', function(){

            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
            });

            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            this.classList.add('active');

            document
                .getElementById(this.dataset.tab)
                .classList.add('active');

        });

    });

    /* ================= LOGO ================= */

    document
        .getElementById('LogoArquivo')
        .addEventListener('change', function(e) {

        const file = e.target.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function(event) {

            const base64 = event.target.result;

            document.getElementById('LogoBase64').value = base64;

            const preview = document.getElementById('previewLogo');

            preview.src = base64;
            preview.style.display = 'block';
        };

        reader.readAsDataURL(file);

    });

</script>

</body>
</html>