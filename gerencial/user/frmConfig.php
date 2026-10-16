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

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;
$idUsuario = $_SESSION['usuario_id'] ?? 0;
$msg = '';
$tipoMsg = 'success';

function moedaParaBanco($valor)
{
    if (!isset($valor) || $valor === '') {
        return 0;
    }

    $valor = preg_replace('/[^\d,]/', '', (string)$valor);
    $valor = str_replace(',', '.', $valor);

    return (float)$valor;
}

/* ================= SALVAR ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metaMensal = moedaParaBanco($_POST['metaMensal'] ?? null);
    $metaDiaria = moedaParaBanco($_POST['metaDiaria'] ?? null);

    ExSqlNET(
        'UPDATE empresa SET MetaMensal = ?, MetaDiaria = ? WHERE id = ?',
        null,
        [$metaMensal, $metaDiaria, $idEmpresa]
    );

    if (!empty($_POST['senhaNova'])) {
        $senha = password_hash($_POST['senhaNova'], PASSWORD_DEFAULT);
        ExSqlNET('UPDATE user SET Senha = ? WHERE id = ? AND idEmpresa = ?', null, [$senha, $idUsuario, $idEmpresa]);
        $msg = 'Configurações salvas e senha alterada com sucesso!';
    } else {
        $msg = 'Configurações salvas com sucesso!';
    }
}

/* ================= BUSCAR DADOS ================= */
$empresa = ExSqlNET(
    'SELECT MetaMensal, MetaDiaria FROM empresa WHERE id = ?',
    null,
    [$idEmpresa]
);
$empresa = $empresa[0] ?? null;

function moeda($v)
{
    return number_format((float)($v ?? 0), 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <style>
        .content{width:min(1180px,calc(100% - 40px));max-width:1180px;margin:30px auto 48px;padding:0}
        .page-title{font-size:28px;line-height:1.25;font-weight:750;letter-spacing:-.7px;color:#0f172a;margin:0 0 10px}
        .page-subtitle{font-size:14px;line-height:1.6;color:#64748b;margin:0 0 22px;padding-top:2px}
        .config-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;align-items:start}
        .content .box{min-width:0;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 4px 18px rgba(15,23,42,.045);overflow:hidden}
        .titulo{display:flex;align-items:center;gap:10px;margin:0 0 22px;color:#1e293b;font-size:16px;font-weight:750;line-height:1.4}
        .titulo:before{content:"";display:block;width:4px;height:22px;border-radius:5px;background:#f97316;flex:0 0 4px}
        .input-group{margin-bottom:18px;min-width:0}
        .input-group:last-child{margin-bottom:0}
        .content label{display:block;margin:0 0 7px;color:#334155;font-size:13px;font-weight:650}
        .content input:not([type="checkbox"]){box-sizing:border-box;width:100%;min-height:43px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.4;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
        .content input:not([type="checkbox"]):focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13)}
        .content input::placeholder{color:#94a3b8}
        .info-box{margin-top:4px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;padding:13px 15px;border-radius:10px;font-size:13px;line-height:1.6}
        .footer-actions{display:flex;justify-content:flex-end;margin-top:20px}
        .content .btn-salvar{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:11px 18px;border:1px solid transparent;border-radius:10px;background:#f97316;color:#fff;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 3px 8px rgba(249,115,22,.18);transition:transform .15s,box-shadow .15s,background .15s}
        .content .btn-salvar:hover{background:#ea580c;box-shadow:0 5px 12px rgba(249,115,22,.23);transform:translateY(-1px)}
        .content .msg{padding:13px 16px;border-radius:11px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;font-size:14px;margin-bottom:20px;line-height:1.5}
        @media(max-width:850px){.content{width:calc(100% - 28px);margin-top:22px}.content .box{padding:20px}}
        @media(max-width:650px){.content{width:calc(100% - 22px);margin:16px auto 30px}.page-title{font-size:23px;margin-bottom:8px}.page-subtitle{margin-bottom:18px}.config-grid{grid-template-columns:1fr;gap:14px}.content .box{padding:17px;border-radius:13px}.titulo{margin-bottom:18px}.footer-actions{justify-content:stretch}.content .btn-salvar{width:100%}}
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">
    <h1 class="page-title">Configurações</h1>
    <p class="page-subtitle">Gerencie a segurança da sua conta e as metas de faturamento da empresa.</p>

    <?php if ($msg !== ''): ?>
        <div class="msg" role="status"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="config-grid">
            <section class="box">
                <h2 class="titulo">Segurança da Conta</h2>
                <div class="input-group">
                    <label for="senhaNova">Nova Senha</label>
                    <input type="password" name="senhaNova" id="senhaNova" placeholder="Digite a nova senha" autocomplete="new-password">
                </div>
                <div class="info-box">Para manter sua conta protegida, recomendamos utilizar uma senha com letras, números e caracteres especiais. Deixe o campo em branco para manter a senha atual.</div>
            </section>

            <section class="box">
                <h2 class="titulo">Metas de Faturamento</h2>
                <div class="input-group">
                    <label for="metaMensal">Meta Mensal (R$)</label>
                    <input type="text" name="metaMensal" id="metaMensal" inputmode="decimal" value="<?= htmlspecialchars(moeda($empresa['MetaMensal'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" placeholder="0,00">
                </div>
                <div class="input-group">
                    <label for="diasMeta">Dias para Alcançar a Meta</label>
                    <input type="number" name="diasMeta" id="diasMeta" min="1" step="1" placeholder="Ex.: 22">
                </div>
                <div class="input-group">
                    <label for="metaDiaria">Meta Diária (R$)</label>
                    <input type="text" name="metaDiaria" id="metaDiaria" inputmode="decimal" value="<?= htmlspecialchars(moeda($empresa['MetaDiaria'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" placeholder="0,00">
                </div>
                <div class="info-box">Informe a meta mensal e a quantidade de dias úteis para calcular uma sugestão de meta diária.</div>
            </section>
        </div>

        <div class="footer-actions">
            <button type="submit" class="btn-salvar">💾 Salvar Configurações</button>
        </div>
    </form>
</div>

<script>
    function formatarMoeda(campo) {
        let v = campo.value.replace(/\D/g, '');
        v = (Number(v) / 100).toFixed(2);
        v = v.replace('.', ',');
        v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        campo.value = v;
    }

    const metaMensalInput = document.getElementById('metaMensal');
    const metaDiariaInput = document.getElementById('metaDiaria');
    const diasMetaInput = document.getElementById('diasMeta');

    metaMensalInput?.addEventListener('input', function () {
        formatarMoeda(this);
        calcularMetaDiaria();
    });

    metaDiariaInput?.addEventListener('input', function () {
        formatarMoeda(this);
    });

    diasMetaInput?.addEventListener('input', calcularMetaDiaria);

    function moedaParaNumero(valor) {
        if (!valor) return 0;
        valor = valor.replace(/\./g, '').replace(',', '.');
        return parseFloat(valor) || 0;
    }

    function numeroParaMoeda(valor) {
        return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calcularMetaDiaria() {
        const dias = parseInt(diasMetaInput.value, 10) || 0;
        const metaMensal = moedaParaNumero(metaMensalInput.value);
        if (dias > 0 && metaMensal > 0) {
            metaDiariaInput.value = numeroParaMoeda(metaMensal / dias);
        }
    }
</script>
</body>
</html>
