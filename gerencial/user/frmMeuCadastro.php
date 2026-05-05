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

// SALVAR
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

// CONSULTA
$dadosUsuario = ExSqlNET(
    "SELECT Nome, Email, EstadoCivil, NumOab, Rua, NumeroEndereco, Bairro, Cidade, UF, CEP, LogoBase64, Contato As Numero,Documento
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
    <link rel="stylesheet" href="../css/base.css">
    <link rel="icon" href="../img/favicon.png">

    <style>
        .perfil-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdcdc;
            border-radius: 8px;
            font-size: 14px;
        }

        .logo-preview {
            margin-top: 10px;
            max-height: 90px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fafafa;
        }

        .success-msg {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">
   <div class="page-title">Meu Cadastro</div>

    <div class="perfil-card">

        <?php if ($mensagem): ?>
            <div class="success-msg"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" value="<?= htmlspecialchars($usuario['Nome'] ?? '') ?>" >
                </div>

                <div class="form-group">
                    <label>Documento</label>
                    <input type="text" name="Documento" value="<?= htmlspecialchars($usuario['Documento'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= htmlspecialchars($usuario['Email'] ?? '') ?>" >
                </div>

                <div class="form-group">
                    <label>Estado Civil</label>
                    <select name="EstadoCivil" id="EstadoCivil">
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
                    <input type="text" name="OAB" value="<?= htmlspecialchars($usuario['NumOab'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="CEP" id="CEP" value="<?= htmlspecialchars($usuario['CEP'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>UF</label>
                    <input type="text" maxlength="2" name="UF" value="<?= htmlspecialchars($usuario['UF'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="Cidade" value="<?= htmlspecialchars($usuario['Cidade'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" name="Bairro" id="Bairro" value="<?= htmlspecialchars($usuario['Bairro'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Rua</label>
                    <input type="text" name="Rua" value="<?= htmlspecialchars($usuario['Rua'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Número Endereço</label>
                    <input type="text" name="NumeroEndereco" value="<?= htmlspecialchars($usuario['NumeroEndereco'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Contato</label>
                    <input type="text" name="Numero" value="<?= htmlspecialchars($usuario['Numero'] ?? '') ?>">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Logo</label>

                    <input type="file" id="LogoArquivo" accept="image/*">

                    <input 
                        type="hidden" 
                        name="LogoBase64" 
                        id="LogoBase64"
                        value="<?= htmlspecialchars($usuario['LogoBase64'] ?? '') ?>"
                    >

                    <br><br>

                    <img 
                        id="previewLogo"
                        src="<?= !empty($usuario['LogoBase64']) ? $usuario['LogoBase64'] : '' ?>"
                        class="logo-preview"
                        style="<?= empty($usuario['LogoBase64']) ? 'display:none;' : '' ?>"
                    >
                </div>

            </div>

            <br>
            <button type="submit" class="btn">Salvar</button>

        </form>
    </div>
</div>

</body>
</html>

<script>
document.getElementById('LogoArquivo').addEventListener('change', function(e) {
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