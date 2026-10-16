<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: Login');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$mensagem = '';
$tipoMensagem = '';

/* ================= SALVAR ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dadosCadastro = [
        'id' => $usuarioId,
        'EstadoCivil' => $_POST['EstadoCivil'] ?? '',
        'NumOab' => $_POST['OAB'] ?? '',
        'Rua' => $_POST['Rua'] ?? '',
        'NumeroEndereco' => $_POST['NumeroEndereco'] ?? '',
        'Cidade' => $_POST['Cidade'] ?? '',
        'UF' => strtoupper(trim($_POST['UF'] ?? '')),
        'CEP' => $_POST['CEP'] ?? '',
        'LogoBase64' => $_POST['LogoBase64'] ?? '',
        'Contato' => $_POST['Numero'] ?? '',
        'Documento' => $_POST['Documento'] ?? '',
        'Bairro' => $_POST['Bairro'] ?? ''
    ];

    $retorno = MeuCadastro($dadosCadastro, 'ATUALIZAR');
    if ($retorno === '') {
        $mensagem = 'Cadastro atualizado com sucesso!';
        $tipoMensagem = 'success';
    } else {
        $mensagem = 'Erro ao atualizar cadastro: ' . $retorno;
        $tipoMensagem = 'error';
    }
}

/* ================= CONSULTA ================= */
$dadosUsuario = ExSqlNET(
    "SELECT Nome, Email, EstadoCivil, NumOab, Rua, NumeroEndereco,
            Bairro, Cidade, UF, CEP, LogoBase64,
            Contato AS Numero, Documento
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
    <title>Meu Cadastro | Autodoc Gerencial</title>
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <style>
        .content{width:min(1180px,calc(100% - 40px));max-width:1180px;margin:30px auto 48px;padding:0}
        .page-title{font-size:28px;line-height:1.25;font-weight:750;letter-spacing:-.7px;color:#0f172a;margin:0 0 8px}
        .page-subtitle{font-size:14px;line-height:1.6;color:#64748b;margin:0 0 22px;padding-top:2px}
        .perfil-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 4px 18px rgba(15,23,42,.045)}
        .content .alert{padding:13px 16px;border-radius:11px;border:1px solid #e2e8f0;font-size:14px;margin-bottom:20px;line-height:1.5}
        .content .alert.success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .content .alert.error{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .tabs-config{display:flex;gap:9px;margin:0 0 24px;flex-wrap:wrap;border-bottom:1px solid #e2e8f0;padding-bottom:14px}
        .tab-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:10px 15px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#475569;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;transition:background .18s,border-color .18s,color .18s}
        .tab-btn:hover{border-color:#fdba74;background:#fff7ed;color:#9a3412}
        .tab-btn.active{background:#fff7ed;border-color:#fdba74;color:#c2410c;box-shadow:inset 0 -2px 0 #f97316}
        .tab-content{display:none}
        .tab-content.active{display:block;animation:fade .18s ease}
        @keyframes fade{from{opacity:.65;transform:translateY(3px)}to{opacity:1;transform:translateY(0)}}
        .section-heading{display:flex;align-items:center;gap:10px;margin:0 0 20px;color:#1e293b;font-size:16px;font-weight:750;line-height:1.4}
        .section-heading:before{content:"";display:block;width:4px;height:22px;border-radius:5px;background:#f97316;flex:0 0 4px}
        .section-description{margin:-10px 0 22px;color:#64748b;font-size:13px;line-height:1.6}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 18px}
        .form-group{display:flex;flex-direction:column;min-width:0}
        .form-group label,.upload-box>label{display:block;margin:0 0 7px;color:#334155;font-size:13px;font-weight:650}
        .form-group input:not([type="file"]),.form-group select{box-sizing:border-box;width:100%;min-height:43px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.4;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
        .form-group input:focus,.form-group select:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13)}
        .form-group input[readonly]{background:#f8fafc;color:#64748b;cursor:not-allowed}
        .upload-box{border:1px dashed #fdba74;border-radius:12px;padding:20px;background:#fffaf5;transition:border-color .18s,background .18s}
        .upload-box:hover{border-color:#f97316;background:#fff7ed}
        #LogoArquivo{display:block;box-sizing:border-box;width:100%;max-width:100%;margin-top:8px;padding:10px;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#475569;font-family:inherit;font-size:14px}
        .logo-preview{display:block;max-width:100%;max-height:110px;object-fit:contain;margin-top:18px;padding:8px;border:1px solid #e2e8f0;border-radius:10px;background:#fff}
        .upload-help{margin:10px 0 0;color:#64748b;font-size:13px;line-height:1.5}
        .footer-actions{display:flex;justify-content:flex-end;margin-top:24px;padding-top:20px;border-top:1px solid #f1f5f9}
        .content .btn-salvar{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:10px 18px;border:1px solid transparent;border-radius:10px;background:#f97316;color:#fff;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 3px 8px rgba(249,115,22,.18);transition:transform .15s,box-shadow .15s,background .15s}
        .content .btn-salvar:hover{background:#ea580c;box-shadow:0 5px 12px rgba(249,115,22,.23);transform:translateY(-1px)}
        @media(max-width:850px){.content{width:calc(100% - 28px);margin-top:22px}.perfil-card{padding:20px}}
        @media(max-width:560px){.content{width:calc(100% - 22px);margin:16px auto 30px}.page-title{font-size:23px}.page-subtitle{margin-bottom:17px}.perfil-card{padding:16px;border-radius:13px}.tabs-config{display:grid;grid-template-columns:1fr;gap:8px;margin-bottom:20px}.tab-btn{width:100%}.form-grid{grid-template-columns:1fr;gap:16px}.footer-actions{justify-content:stretch}.content .btn-salvar{width:100%}.upload-box{padding:15px}}
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">
    <h1 class="page-title">Meu Cadastro</h1>
    <p class="page-subtitle">Mantenha seus dados cadastrais, endereço e identidade visual atualizados.</p>

    <?php if ($mensagem): ?>
        <div class="alert <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="perfil-card">
        <form method="POST">
            <div class="tabs-config" role="tablist" aria-label="Seções do cadastro">
                <button type="button" class="tab-btn active" data-tab="tab-dados" role="tab" aria-selected="true">Dados Cadastrais</button>
                <button type="button" class="tab-btn" data-tab="tab-endereco" role="tab" aria-selected="false">Endereço</button>
                <button type="button" class="tab-btn" data-tab="tab-logo" role="tab" aria-selected="false">Logo</button>
            </div>

            <section class="tab-content active" id="tab-dados" role="tabpanel">
                <h2 class="section-heading">Informações pessoais</h2>
                <p class="section-description">Confira seus dados principais e atualize as informações que podem ser alteradas.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="Nome">Nome</label>
                        <input type="text" id="Nome" value="<?= htmlspecialchars($usuario['Nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="Documento">Documento</label>
                        <input type="text" name="Documento" id="Documento" value="<?= htmlspecialchars($usuario['Documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="Email">E-mail</label>
                        <input type="email" id="Email" value="<?= htmlspecialchars($usuario['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="EstadoCivil">Estado civil</label>
                        <select name="EstadoCivil" id="EstadoCivil">
                            <option value="0">Selecione</option>
                            <option value="1" <?= (($usuario['EstadoCivil'] ?? '') == 1) ? 'selected' : '' ?>>Solteiro(a)</option>
                            <option value="2" <?= (($usuario['EstadoCivil'] ?? '') == 2) ? 'selected' : '' ?>>Casado(a)</option>
                            <option value="3" <?= (($usuario['EstadoCivil'] ?? '') == 3) ? 'selected' : '' ?>>Divorciado(a)</option>
                            <option value="4" <?= (($usuario['EstadoCivil'] ?? '') == 4) ? 'selected' : '' ?>>Viúvo(a)</option>
                            <option value="5" <?= (($usuario['EstadoCivil'] ?? '') == 5) ? 'selected' : '' ?>>União estável</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="OAB">Número OAB</label>
                        <input type="text" name="OAB" id="OAB" value="<?= htmlspecialchars($usuario['NumOab'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="Numero">Contato</label>
                        <input type="text" name="Numero" id="Numero" value="<?= htmlspecialchars($usuario['Numero'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </section>

            <section class="tab-content" id="tab-endereco" role="tabpanel">
                <h2 class="section-heading">Endereço</h2>
                <p class="section-description">Informe seu endereço para manter o cadastro atualizado.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="CEP">CEP</label>
                        <input type="text" name="CEP" id="CEP" value="<?= htmlspecialchars($usuario['CEP'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="UF">UF</label>
                        <input type="text" maxlength="2" name="UF" id="UF" value="<?= htmlspecialchars($usuario['UF'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="Cidade">Cidade</label>
                        <input type="text" name="Cidade" id="Cidade" value="<?= htmlspecialchars($usuario['Cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="Bairro">Bairro</label>
                        <input type="text" name="Bairro" id="Bairro" value="<?= htmlspecialchars($usuario['Bairro'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="Rua">Rua</label>
                        <input type="text" name="Rua" id="Rua" value="<?= htmlspecialchars($usuario['Rua'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label for="NumeroEndereco">Número</label>
                        <input type="text" name="NumeroEndereco" id="NumeroEndereco" value="<?= htmlspecialchars($usuario['NumeroEndereco'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </section>

            <section class="tab-content" id="tab-logo" role="tabpanel">
                <h2 class="section-heading">Identidade visual</h2>
                <p class="section-description">Envie a logo que será usada pelo sistema, quando esse recurso estiver disponível.</p>
                <div class="upload-box">
                    <label for="LogoArquivo">Logo do sistema</label>
                    <input type="file" id="LogoArquivo" accept="image/*">
                    <input type="hidden" name="LogoBase64" id="LogoBase64" value="<?= htmlspecialchars($usuario['LogoBase64'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <p class="upload-help">Selecione uma imagem. A pré-visualização será exibida abaixo.</p>
                    <img id="previewLogo" src="<?= !empty($usuario['LogoBase64']) ? htmlspecialchars($usuario['LogoBase64'], ENT_QUOTES, 'UTF-8') : '' ?>" class="logo-preview" alt="Pré-visualização da logo" style="<?= empty($usuario['LogoBase64']) ? 'display:none;' : '' ?>">
                </div>
            </section>

            <div class="footer-actions">
                <button type="submit" class="btn-salvar">💾 Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* ================= ABAS ================= */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            const painel = document.getElementById(this.dataset.tab);
            if (painel) painel.classList.add('active');
        });
    });

    /* ================= LOGO ================= */
    document.getElementById('LogoArquivo').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Selecione um arquivo de imagem válido.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (event) {
            const base64 = event.target.result;
            document.getElementById('LogoBase64').value = base64;
            const preview = document.getElementById('previewLogo');
            preview.src = base64;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    const ufInput = document.getElementById('UF');
    ufInput?.addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 2);
    });
</script>
</body>
</html>
