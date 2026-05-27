<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;
$idUsuario = $_SESSION['usuario_id'] ?? 0;

$msg = "";

function moedaParaBanco($valor)
{
    if (!isset($valor) || $valor === '') {
        return 0;
    }

    $valor = preg_replace('/[^\d,]/', '', $valor);

    $valor = str_replace(',', '.', $valor);

    return (float)$valor;
}

/* ================= SALVAR ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $metaMensal = moedaParaBanco($_POST['metaMensal'] ?? null);
    $metaDiaria  = moedaParaBanco($_POST['metaDiaria'] ?? null);

    ExSqlNET("
        UPDATE empresa 
        SET MetaMensal = ?, MetaDiaria = ?
        WHERE id = ?
    ", null, [$metaMensal, $metaDiaria, $idEmpresa]);

    /* SENHA */
    if (!empty($_POST['senhaNova'])) {

        $senha = password_hash($_POST['senhaNova'], PASSWORD_DEFAULT);

        ExSqlNET("
            UPDATE user
            SET Senha = ?
            WHERE id = ?
        ", null, [$senha, $idUsuario]);

        $msg = "Configurações salvas e senha alterada com sucesso!";
    } else {
        $msg = "Configurações salvas com sucesso!";
    }
}

/* ================= BUSCAR DADOS ================= */

$empresa = ExSqlNET("
    SELECT MetaMensal, MetaDiaria
    FROM empresa
    WHERE id = ?
", null, [$idEmpresa]);

$empresa = $empresa[0] ?? null;

function moeda($v) {
    return number_format($v ?? 0, 2, ',', '.');
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Configurações</title>

<link rel="stylesheet" href="/gerencial/css/base.css?v=15">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../img/favicon.png">

<style>

    .content{
        padding:30px;
        max-width:1000px;
        margin:0 auto;
    }

    .page-title{
        font-size:30px;
        font-weight:700;
        color:#1e293b;
        margin-bottom:25px;
    }

    .msg{
        background:#16a34a;
        color:#fff;
        padding:14px 18px;
        border-radius:10px;
        margin-bottom:25px;
        font-weight:600;
    }

    .config-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:25px;
    }

    @media(max-width:900px){

        .config-grid{
            grid-template-columns:1fr;
        }

    }

    .box{
        background:#fff;
        border-radius:14px;
        padding:28px;
        border:1px solid #e2e8f0;
        box-shadow:0 4px 14px rgba(0,0,0,0.05);
    }

    .titulo{
        font-size:20px;
        font-weight:700;
        color:#1e293b;
        margin-bottom:25px;

        display:flex;
        align-items:center;
        gap:10px;
    }

    label{
        display:block;
        margin-bottom:7px;

        color:#334155;
        font-size:14px;
        font-weight:600;
    }

    .input-group{
        margin-bottom:20px;
    }

    input{
        width:100%;
        height:48px;

        border:1px solid #cbd5e1;
        border-radius:10px;

        padding:0 14px;

        font-size:15px;
        color:#1e293b;

        transition:.2s ease;
    }

    input:focus{
        outline:none;
        border-color:#f97316;
        box-shadow:0 0 0 3px rgba(249,115,22,.15);
    }

    input::placeholder{
        color:#94a3b8;
    }

    .info-box{
        background:#fff7ed;
        border:1px solid #fed7aa;
        color:#9a3412;

        padding:14px;
        border-radius:10px;

        font-size:14px;
        line-height:1.5;
    }

    .footer-actions{
        margin-top:25px;
    }

    .btn-salvar{
        height:52px;
        min-width:240px;

        border:none;
        border-radius:12px;

        background:#f97316;
        color:#fff;

        font-size:15px;
        font-weight:700;

        cursor:pointer;

        transition:.2s ease;
    }

    .btn-salvar:hover{
        background:#ea580c;
    }
</style>
</head>
<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">

    <h1 class="page-title">Configurações</h1>

    <?php if ($msg): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="config-grid">

            <!-- SENHA -->
            <div class="box">

                <div class="titulo">
                    🔒 Segurança da Conta
                </div>

                <div class="input-group">

                    <label>Nova Senha</label>

                    <input type="password"
                        name="senhaNova"
                        placeholder="Digite a nova senha">

                </div>

                <div class="info-box">
                    Recomendamos utilizar letras, números
                    e caracteres especiais.
                </div>

            </div>


            <!-- METAS -->
            <div class="box">

                <div class="titulo">
                    🎯 Metas de Faturamento
                </div>

                <div class="input-group">

                    <label>Meta Mensal (R$)</label>

                    <input type="text"
                        name="metaMensal"
                        id="metaMensal"
                        value="<?= moeda($empresa['MetaMensal'] ?? 0) ?>">

                </div>

                <div class="input-group">

                    <label>Dias para Alcançar a Meta</label>

                    <input type="number"
                        name="diasMeta"
                        id="diasMeta"
                        placeholder="Ex: 22">

                </div>

                <div class="input-group">

                    <label>Meta Diária (R$)</label>

                    <input type="text"
                        name="metaDiaria"
                        id="metaDiaria"
                        value="<?= moeda($empresa['MetaDiaria'] ?? 0) ?>">

                </div>

            </div>

        </div>

        <div class="footer-actions">

            <button type="submit" class="btn-salvar">
                💾 Salvar Configurações
            </button>

        </div>

    </form>

</div>

</body>

<!-- <script>
    //======================================================================
    function formatarMoeda(campo) {
        let v = campo.value.replace(/\D/g, '');

        v = (v / 100).toFixed(2) + '';
        v = v.replace('.', ',');
        v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        campo.value = v;
    }

    //======================================================================
    document.getElementById('metaDiaria')?.addEventListener('input', function () {
        formatarMoeda(this);
    });
    //======================================================================
    document.getElementById('metaMensal')?.addEventListener('input', function () {
        formatarMoeda(this);
    });
    //======================================================================
</script> -->

<script>
    //======================================================================
    function formatarMoeda(campo) {
        let v = campo.value.replace(/\D/g, '');

        v = (v / 100).toFixed(2) + '';
        v = v.replace('.', ',');
        v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        campo.value = v;
    }

    //======================================================================
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

    diasMetaInput?.addEventListener('blur', calcularMetaDiaria);
    diasMetaInput?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.key === 'Tab') {
            calcularMetaDiaria();
        }
    });

    //======================================================================
    function moedaParaNumero(valor) {
        if (!valor) return 0;
        valor = valor.replace(/\./g,'').replace(',', '.');
        return parseFloat(valor) || 0;
    }

    function numeroParaMoeda(valor) {
        return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calcularMetaDiaria() {
        const dias = parseInt(diasMetaInput.value) || 0;
        const metaMensal = moedaParaNumero(metaMensalInput.value);

        if (dias > 0 && metaMensal > 0) {
            const diaria = metaMensal / dias;
            metaDiariaInput.value = numeroParaMoeda(diaria);
        }
    }
</script>
</html>