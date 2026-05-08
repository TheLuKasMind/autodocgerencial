<?php

require_once '../base/connection.php';
include '../base/baseFuncoes.php';
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
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

<link rel="stylesheet" href="../css/base.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../img/favicon.png">

<style>

    .box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 6px 14px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .titulo {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #334155;
    }

    input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        margin-bottom: 15px;
    }

    .msg {
        background: #22c55e;
        color: white;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

/* Caixa principal */
.box {
    background: #ffffff;
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    margin-bottom: 25px;
    font-family: 'Inter', sans-serif;
}

/* Títulos dentro das caixas */
.titulo {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #1e293b; /* Azul escuro moderno */
}

/* Labels dos campos */
label {
    font-size: 14px;
    color: #334155;
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
}

/* Inputs */
input[type="text"],
input[type="number"],
input[type="password"] {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    margin-bottom: 18px;
    font-size: 15px;
    color: #1e293b;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s ease;
}

/* Placeholder */
input::placeholder {
    color: #94a3b8;
    font-style: italic;
}

/* Efeito de foco */
input:focus {
    border-color: #22c55e;
    box-shadow: 0 0 6px rgba(34, 197, 94, 0.3);
    outline: none;
}

/* Mensagem de sucesso */
.msg {
    background: #22c55e;
    color: white;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
}

</style>
</head>
<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">

    <h1 class="page-title">Configurações</h1>

    <?php if ($msg): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- SENHA -->
        <div class="box">
            <div class="titulo">Alterar Minha Senha</div>

            <label>Nova Senha</label>
            <input type="password" name="senhaNova" placeholder="Digite a nova senha">
        </div>

        <!-- METAS -->
        <!-- <div class="box">
            <div class="titulo">Metas de Faturamento</div>

            <label>Meta Mensal (R$)</label>
            <input type="text" name="metaMensal" id="metaMensal"
                   value="<?= moeda($empresa['MetaMensal'] ?? 0) ?>">

            <label>Meta Diária (R$)</label>
            <input type="text" name="metaDiaria" id="metaDiaria"
                   value="<?= moeda($empresa['MetaDiaria'] ?? 0) ?>">
        </div> -->

        <!-- METAS -->
        <div class="box">
            <div class="titulo">Metas de Faturamento</div>

            <label>Meta Mensal (R$)</label>
            <input type="text" name="metaMensal" id="metaMensal"
                value="<?= moeda($empresa['MetaMensal'] ?? 0) ?>">

            <label>Dias para Alcançar a Meta</label>
            <input type="number" name="diasMeta" id="diasMeta"
                placeholder="Informe os dias">

            <label>Meta Diária (R$)</label>
            <input type="text" name="metaDiaria" id="metaDiaria"
                value="<?= moeda($empresa['MetaDiaria'] ?? 0) ?>">
        </div>

        <button type="submit" class="btn">💾 Salvar</button>

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