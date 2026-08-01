<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';
date_default_timezone_set('America/Sao_Paulo');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];
$msgRetorno = "";
$tipoMsg = "";

$despesas = ExSqlNET("SELECT *
    FROM tipodespesa
    WHERE inativo = 0
    AND idEmpresa = ". $idEmpresa ."
    ORDER BY descricao ASC
");

$forcli = ExSqlNET("
    SELECT *
    FROM forcli
    WHERE inativo = 0
    AND idEmpresa = ". $idEmpresa ."
    ORDER BY Nome ASC
");

$Alterando = false;

$msgRetorno = $_SESSION['mensagem_error'] ?? "";
$tipoMsg = !empty($msgRetorno) ? "error" : "";

unset($_SESSION['mensagem_error']);

/* =========================
   RECEBER POST
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {

    // $data       = $_POST['data'] ?? date('Y-m-d H:i:s');
    $dataPost = $_POST['data'] ?? date('Y-m-d');
    $data = $dataPost . ' ' . date('H:i:s');

    $descricao  = $_POST['descricao'] ?? '';
    $valor      = $_POST['valor'] ?? 0;
    $forcli     = $_POST['forcli'] ?? 0;
    $dados['idForcli'] = (int)$forcli;

    $tipodespesa = $_POST['tipodespesa'] ?? null;
    
    if (empty($tipodespesa)) {
        $msgRetorno = "Informe o tipo de despesa/conta!";
        $tipoMsg = "error";
        $_SESSION['mensagem_error'] = $msgRetorno;
        header('Location: /gerencial/BoletimCaixa/Lancamento');
        exit;
    }

    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);

    // $dadosTipoDepesa = ExSqlNET("SELECT * FROM tipodespesa WHERE idEmpresa = $idEmpresa AND id = $tipodespesa");
    
    $dadosTipoDepesa = ExSqlNET(
        "SELECT * FROM tipodespesa WHERE idEmpresa = ? AND id = ?",
        null,
        [$idEmpresa, $tipodespesa]
    );


    $dadosTipoDepesa = $dadosTipoDepesa[0] ?? null;

    $tipoMov = 'AJUSTE';

    if (!empty($dadosTipoDepesa)) {

        $acao = (int) $dadosTipoDepesa['Acao'];

        if ($acao == 1) {
            $tipoMov = 'ENTRADA';
        } elseif ($acao == -1) {
            $tipoMov = 'SAIDA';
        }
    }
    
    $dados['idEmpresa'] = $idEmpresa;
    $dados['TipoDespesa'] = $tipodespesa;
    $dados['Descricao'] = $descricao;
    $dados['Valor'] = $valor;
    $dados['Data'] = $data;
    // $dados['idForcli'] = $forcli;
    
    // if (!isset($forcli) || $forcli === "" || $forcli === "undefined") {
    if (!isset($forcli) || $forcli === "") {
        $dados['idForcli'] = 0;
    } else {
        $dados['idForcli'] = intval($forcli);
    }
    
    $dados['idServProd'] = 0;
    $dados['DataPgto'] = $data;
    $dados['ValorPgto'] = $valor;
    $dados['UserAlt'] = $_SESSION['usuario_id'];
    $dados['TipoMov'] = $tipoMov;
    $dados['CaixaGeral'] = 0;
    $dados['ControleOrigem'] = 0;

    $dados['idUser'] = $_SESSION['usuario_id']  ?? 0;

    $retorno = MovimentoCC($dados, "CADASTRAR");
    if ($retorno === "") {
        $msgRetorno = "Lançamento cadastrado com sucesso!";
        $tipoMsg = "success";
        // $_SESSION['mensagem_sucesso'] = $msgRetorno;
        header('Location: /gerencial/BoletimCaixa');
        exit;
    } else {
        $msgRetorno = "Erro ao cadastrar lançamento no boletim de caixa! Erro -> ". $retorno;
        $tipoMsg = "error";
        $_SESSION['mensagem_error'] = $msgRetorno;
    }

}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Novo Lançamento de Caixa - Autodoc</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- CSS BASE DO SISTEMA -->
<link rel="stylesheet" href="/gerencial/css/base.css?v=15">

    <style>
        body {
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: #f8fafc;
        }

        .content {
            margin-left: 240px;
            padding: 30px;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0,0,0,.05);
            max-width: 700px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
            display: block;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            background: #f97316;
            color: #fff;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary {
            background: #fff;
            color: #f97316;
            border: 1px solid #f97316;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .alert {
            background: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            color: #9a3412;
            margin-bottom: 20px;
        }

        /* ================= MODAL ================= */

        .modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(3px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 9999;
        overflow-y: auto;
        }

        .modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,.15);
            display: flex;
            flex-direction: column;
            animation: fadeIn .2s ease;
        }

        @keyframes fadeIn {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-box h3 {
            margin: 0 0 16px 0;
            font-size: 18px;
            color: #1e293b;
        }

        /* Scroll interno da tabela */
        .modal-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 600px;
        }

        .modal-box thead {
            background: #f97316;
            color: #fff;
        }

        .modal-box th,
        .modal-box td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .modal-box tbody {
            display: block;
            overflow-y: auto;
            max-height: 300px;
        }

        .modal-box thead,
        .modal-box tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .modal-box tbody tr {
            cursor: pointer;
            transition: background .15s ease;
        }

        .modal-box tbody tr:hover {
            background: #fff7ed;
        }

        /* Botão fechar */
        .modal-box .btn-secondary {
            margin-top: 18px;
            align-self: flex-end;
        }

            /* Scroll horizontal se precisar */
        .modal-box {
            overflow: hidden;
        }

        .modal-table-wrapper {
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
            border-radius: 10px;
        }

        /* Tabela normal */
        .modal-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .modal-box thead {
            background: #f97316;
            color: #fff;
            position: sticky;
            top: 0;
        }

        .modal-box th,
        .modal-box td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .modal-box tbody tr:hover {
            background: #fff7ed;
            cursor: pointer;
        }

        .modal-table-wrapper{
            max-height:350px;
            overflow-y:auto;
            border:1px solid #e5e7eb;
            border-radius:8px;
        }

        /* ================= RESPONSIVO ================= */

        @media (max-width: 768px) {

            .modal {
                padding: 10px;
            }

            .modal-box {
                padding: 18px;
                border-radius: 14px;
            }

            .modal-box table {
                font-size: 13px;
                min-width: unset;
            }

            .modal-box tbody {
                max-height: 250px;
            }
        }

        .modal table,
        .modal thead,
        .modal tbody,
        .modal tr,
        .modal th,
        .modal td {
            display: revert !important;
        }

        .modal td::before {
            content: none !important;
        }

        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        hr {
            margin: 24px 0;
        }

        h4 {
            margin-top: 24px;
            margin-bottom: 12px;
            color: #333;
        }



        .pagamento-box {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn{
            font-family: 'Inter', Arial, sans-serif;
        }

        .card{
            max-width:750px;
            padding:30px;
        }

        .lancamento-grid{
            display:grid;
            gap:20px;
        }

        .campo-full{
            width:100%;
        }

        .campo-full label,
        .campo-valor label{
            display:block;
            margin-bottom:8px;
            font-weight:600;
            color:#444;
        }

        .campo-full input,
        .campo-valor input{
            width:100%;
            padding:14px;
            border:1px solid #e5e7eb;
            border-radius:10px;
            font-size:15px;
            box-sizing:border-box;
        }

        .campo-click{
            cursor:pointer;
        }

        .campo-click:hover{
            border-color:#f97316;
        }

        .campo-valor input{
            font-size:34px;
            font-weight:bold;
            color:#f97316;
            background:#fff7ed;
            border:2px solid #fed7aa;
            text-align:center;
            padding:20px;
        }

        .actions{
            margin-top:30px;
            display:flex;
            gap:12px;
        }

        .btn{
            background:#f97316;
            color:white;
            border:none;
            padding:14px 22px;
            border-radius:10px;
            font-weight:600;
            cursor:pointer;
        }

        .btn:hover{
            opacity:.95;
        }

        .btn-secondary{
            background:white;
            color:#f97316;
            border:1px solid #f97316;
            padding:14px 22px;
            border-radius:10px;
            text-decoration:none;
            font-weight:600;
            cursor:pointer;
        }

        .btn-secondary:hover{
            background:#fff7ed;
        }


        .modal-box h3{
            margin-bottom:15px;
        }

        #modalBuscaDespesa,
        #modalBuscaForcli{
            margin-bottom:15px;
        }


        @media(max-width:768px){

            .card{
                padding:20px;
            }

            .campo-valor input{
                font-size:28px;
            }

            .actions{
                flex-direction:column;
            }

        }

    </style>
</head>

<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">
    <div class="page-title">Novo Lançamento de Caixa</div>

        <?php
            if ($msgRetorno): ?>
            <div class="alert <?= $tipoMsg ?>">
                <?= htmlspecialchars($msgRetorno) ?>
            </div>
            <?php endif;

            if ($tipoMsg === 'success') {
                $_POST = [];
            }
        ?>
    <div class="card">

        <form method="post">

            <div class="lancamento-grid">

                <div class="campo-full">
                    <label>Conta / Tipo de Despesa *</label>

                    <input
                        type="text"
                        id="nomeDespesa"
                        placeholder="Clique para selecionar a conta"
                        readonly
                        onclick="abrirModalDespesas()"
                        class="campo-click"
                    >

                    <input type="hidden" id="tipodespesa" name="tipodespesa">
                </div>


                <div class="campo-valor">
                    <label>Valor *</label>

                    <input
                        type="text"
                        id="valor"
                        name="valor"
                        placeholder="0,00"
                        autocomplete="off"
                    >
                </div>


                <div class="campo-full">
                    <label>Cliente (Opcional)</label>

                    <input
                        type="text"
                        id="nomeForcli"
                        placeholder="Clique para selecionar o cliente"
                        readonly
                        onclick="abrirModalForcli()"
                        class="campo-click"
                    >

                    <input type="hidden" id="forcli" name="forcli">
                </div>


                <div class="campo-full">
                    <label>Descrição</label>

                    <input
                        type="text"
                        id="descricao"
                        name="descricao"
                        placeholder="Descrição do lançamento"
                    >
                </div>


                <div>
                    <label>Data</label>

                    <input
                        type="date"
                        name="data"
                        value="<?= date('Y-m-d') ?>"
                    >
                </div>

            </div>


            <!-- MODAL DESPESAS -->

            <div id="modalDespesas" class="modal">

                <div class="modal-box">

                    <h3>Selecionar Conta</h3>

                    <input
                        type="text"
                        id="modalBuscaDespesa"
                        placeholder="Pesquisar conta..."
                    >

                    <div class="modal-table-wrapper">

                        <table id="tabelaDespesas">

                            <thead>
                                <tr>
                                    <th>Conta</th>
                                    <th>Valor Base</th>
                                </tr>
                            </thead>

                            <tbody id="modalBodyDespesa"></tbody>

                        </table>

                    </div>

                    <button
                        type="button"
                        class="btn-secondary"
                        onclick="fecharModalDespesas()"
                    >
                        Fechar
                    </button>

                </div>

            </div>



            <!-- MODAL CLIENTE -->

            <div id="modalForcli" class="modal">

                <div class="modal-box">

                    <h3>Selecionar Cliente</h3>

                    <input
                        type="text"
                        id="modalBuscaForcli"
                        placeholder="Pesquisar cliente..."
                    >

                    <div class="modal-table-wrapper">

                        <table id="tabelaForcli">

                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Razão Social</th>
                                    <th>Documento</th>
                                </tr>
                            </thead>

                            <tbody id="modalBodyForcli"></tbody>

                        </table>

                    </div>

                    <button
                        type="button"
                        class="btn-secondary"
                        onclick="fecharModalForcli()"
                    >
                        Fechar
                    </button>

                </div>

            </div>


            <div class="actions">

                <button class="btn" name="salvar">
                    Salvar Lançamento
                </button>

                <a href="/gerencial/BoletimCaixa"
                    class="btn-secondary">

                    Voltar

                </a>

            </div>

        </form>

    </div>
</div>

</body>
</html>

<script>
    const clientes = <?= json_encode($forcli) ?>;
    const despesas = <?= json_encode($despesas) ?>;
</script>

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
document.getElementById('valorPagamento')?.addEventListener('input', function () {
    formatarMoeda(this);
});
//======================================================================
document.getElementById('valor')?.addEventListener('input', function () {
    formatarMoeda(this);
});

function abrirModalDespesas() {
    document.getElementById('modalDespesas').style.display = 'flex';
}

function fecharModalDespesas() {
    document.getElementById('modalDespesas').style.display = 'none';
    document.getElementById('modalBuscaDespesa').value = '';
}



//====================================================================
function abrirModalForcli() {
    document.getElementById('modalForcli').style.display = 'flex';
}

function fecharModalForcli() {
    document.getElementById('modalForcli').style.display = 'none';
    document.getElementById('modalBuscaForcli').value = '';
}


function filtrarTabela(inputId, tabelaId) {

    let input = document.getElementById(inputId);
    let filtro = input.value.toLowerCase();

    let tabela = document.getElementById(tabelaId);
    let linhas = tabela.querySelectorAll("tbody tr");

    linhas.forEach(function(linha){

        let textoLinha = linha.textContent.toLowerCase();

        if (textoLinha.includes(filtro)) {
            linha.style.display = "";
        } else {
            linha.style.display = "none";
        }

    });

}


document.getElementById("modalBuscaForcli").addEventListener("input", function () {
    carregarClientes(this.value.toLowerCase());
});

function carregarClientes(filtro = "") {

    let body = document.getElementById("modalBodyForcli");
    body.innerHTML = "";

    clientes
        .filter(c => c.Nome.toLowerCase().includes(filtro))
        .forEach(c => {

            body.innerHTML += `
                <tr onclick="selecionarForcli(${c.id}, '${c.Nome.replace(/'/g,"\\'")}')">
                    <td>${c.Nome}</td>
                    <td>${c.RazaoSocial ?? ''}</td>
                    <td>${c.Documento ?? ''}</td>
                </tr>
            `;

        });

}


function selecionarForcli(id, nome) {

    document.getElementById("forcli").value = id;
    document.getElementById("nomeForcli").value = nome;

    fecharModalForcli();
}


function abrirModalForcli() {

    document.getElementById('modalForcli').style.display = 'flex';

    carregarClientes("");

    document.getElementById('modalBuscaForcli').focus();
}

document.getElementById("modalBuscaDespesa").addEventListener("input", function () {
    carregarDespesas(this.value.toLowerCase());
});

function carregarDespesas(filtro = "") {

    let body = document.getElementById("modalBodyDespesa");
    body.innerHTML = "";

    despesas
        .filter(d => d.Descricao.toLowerCase().includes(filtro))
        .forEach(d => {

            let valor = parseFloat(d.ValorBase ?? 0).toFixed(2);
            
            body.innerHTML += `
                <tr onclick="selecionarDespesa(
                    ${d.id},
                    '${d.Descricao.replace(/'/g,"\\'")}',
                    ${d.ValorBase ?? 0}
                )">
            
                    <td>${d.Descricao}</td>
                    <td>R$ ${valor}</td>
                </tr>
            `;
        });
}

function abrirModalDespesas() {

    document.getElementById('modalDespesas').style.display = 'flex';

    carregarDespesas("");

    document.getElementById('modalBuscaDespesa').focus();
}

let ultimoValorAutomatico = 0;

function selecionarDespesa(codigo, descricao, valor) {

    document.getElementById("tipodespesa").value = codigo;
    document.getElementById("descricao").value = descricao;

    const campoValor = document.getElementById("valor");

    const valorTela = parseFloat(
        campoValor.value.replace(/\./g, "").replace(",", ".")
    ) || 0;

    // Só altera se estiver vazio, zero ou ainda com o último valor automático
    if (valorTela === 0 || valorTela === ultimoValorAutomatico) {

        campoValor.value = Number(valor).toLocaleString("pt-BR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        ultimoValorAutomatico = Number(valor);
    }

    fecharModalDespesas();
}

// ========================= FECHAR AO CLICAR FORA + LIMPAR =========================

document.getElementById('modalDespesas').addEventListener('click', function(e) {
    if (e.target === this) {
        
        // fecha
        this.style.display = 'none';

        // limpa busca
        document.getElementById('modalBuscaDespesa').value = '';

        // limpa tabela renderizada
        document.getElementById('modalBodyDespesa').innerHTML = '';

        // recarrega lista completa
        carregarDespesas('');
    }
});

document.getElementById('modalForcli').addEventListener('click', function(e) {
    if (e.target === this) {

        // fecha
        this.style.display = 'none';

        // limpa busca
        document.getElementById('modalBuscaForcli').value = '';

        // limpa tabela renderizada
        document.getElementById('modalBodyForcli').innerHTML = '';

        // recarrega lista completa
        carregarClientes('');
    }
});

// ========================= ESC =========================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {

        const modalDespesas = document.getElementById('modalDespesas');
        const modalForcli = document.getElementById('modalForcli');

        if (modalDespesas.style.display === 'flex') {
            modalDespesas.style.display = 'none';
            document.getElementById('modalBuscaDespesa').value = '';
            document.getElementById('modalBodyDespesa').innerHTML = '';
            carregarDespesas('');
        }

        if (modalForcli.style.display === 'flex') {
            modalForcli.style.display = 'none';
            document.getElementById('modalBuscaForcli').value = '';
            document.getElementById('modalBodyForcli').innerHTML = '';
            carregarClientes('');
        }
    }
});

window.addEventListener('load', function () {
    setTimeout(() => {
        const campoValor = document.getElementById('valor');
        campoValor.focus();
        campoValor.select();
    }, 50);
});
</script>