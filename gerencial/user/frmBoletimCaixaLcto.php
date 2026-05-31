<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';
date_default_timezone_set('America/Sao_Paulo');

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
    // $forcli     = $_POST['forcli'] ?? 0;
    $forcli = !empty($_POST['forcli']) ? $_POST['forcli'] : 0;
    
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
    
    if (!isset($forcli) || $forcli === "" || $forcli === "undefined") {
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

        /* 
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.4);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-box {
            background: #fff;
            width: 90%;
            max-width: 700px;
            border-radius: 12px;
            padding: 20px;
        }

        .modal table tr {
            cursor: pointer;
        }

        .modal table tr:hover {
            background: #fff7ed;
        } */

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

            <div class="alert">
                Informe corretamente os dados do lançamento.  
                Entradas e saídas impactam o caixa.
            </div>

        <form method="post">

            <div class="form-grid">

                <button type="button" class="btn-secondary" onclick="abrirModalDespesas()">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Selecionar Despesa / Conta
                </button>

                <button type="button" class="btn-secondary" onclick="abrirModalForcli()">
                    <i class="fa-solid fa-user"></i>
                    Selecionar Cliente
                </button>

                <div>
                    <label>Data</label>
                    <input 
                        type="date" 
                        name="data"
                        value="<?= $Alterando 
                            ? date('Y-m-d', strtotime($dados['Data'])) 
                            : date('Y-m-d') 
                        ?>"
                    >
                </div>

                <!-- <div>
                    <label>Código</label>
                    <input type="text" id="codigo" name="codigo" readonly>
                </div> -->

                <div>
                    <label>Descrição</label>
                    <input type="text" id="descricao" name="descricao">
                </div>

                <div>
                    <label>Valor</label>
                    <input type="text" id="valor" name="valor">
                </div>

                <div>
                    <label>Cliente</label>
                    <input type="text" id="nomeForcli" name="nomeForcli" readonly>
                </div>

                <div>
                  <!--  <label>CodCliente</label> -->
                    <input type="hidden" id="forcli" name="forcli">
                </div>

            </div>

            <div id="modalDespesas" class="modal">
                <div class="modal-box">

                    <h3>Selecionar Despesa</h3>

                    <input type="text" id="modalBuscaDespesa" placeholder="Buscar despesa..." style="margin-bottom:10px;">

                <div class="modal-table-wrapper">
                   <table id="tabelaDespesas">
                        <thead>
                            <tr>
                                <!-- <th>Código</th> -->
                                <th>Despesa</th>
                                <th>Valor</th>
                            </tr>
                        </thead>

                        <!--<tbody>-->
                        <!--<?php foreach ($despesas as $d): ?>-->
                        <!--    <tr onclick="selecionarDespesa(-->
                        <!--         '<?= $d['id'] ?>',-->
                        <!--        '<?= htmlspecialchars($d['Descricao'], ENT_QUOTES) ?>',-->
                        <!--        '<?= $d['ValorBase'] ?>',-->
                        <!--        '<?= $d['id'] ?>'-->
                        <!--    )">-->
                        <!--        <td><?= $d['id'] ?></td>-->
                        <!--        <td><?= $d['Descricao'] ?></td>-->
                        <!--        <td>R$ <?= number_format($d['ValorBase'], 2, ',', '.') ?></td>-->
                        <!--    </tr>-->
                        <!--<?php endforeach; ?>-->
                        <!--</tbody>-->
                        
                        <tbody id="modalBodyDespesa"></tbody>
                        
                    </table>
                </div>
    
                    <button class="btn-secondary" onclick="fecharModalDespesas()">Fechar</button>
                </div>
            </div>
            
            <div id="modalForcli" class="modal">
                <div class="modal-box">

                    <h3>Selecionar Cliente</h3>
                    
                    <input type="text" id="modalBuscaForcli" placeholder="Buscar cliente..." style="margin-bottom:10px;">
                    
                <div class="modal-table-wrapper">
                    <table id="tabelaForcli">
                        <thead>
                            <tr>
                                <!-- <th>Código</th> -->
                                <th>Nome</th>
                                <th>RazaoSocial</th>
                                <th>Documento</th>
                            </tr>
                        </thead>

                        <!--<tbody>-->
                        <!--<?php foreach ($forcli as $f): ?>-->
                        <!--    <tr onclick="selecionarForcli(-->
                        <!--        '<?= $f['Codigo'] ?>',-->
                        <!--        '<?= htmlspecialchars($f['Nome'], ENT_QUOTES) ?>',-->
                        <!--        '<?= htmlspecialchars($f['RazaoSocial'], ENT_QUOTES) ?>',-->
                        <!--        '<?= htmlspecialchars($f['Documento'], ENT_QUOTES) ?>'-->
                        <!--    )">-->
                                <!-- <td><?= $f['Codigo'] ?></td> -->
                        <!--        <td><?= $f['Nome'] ?></td>-->
                        <!--        <td><?= $f['RazaoSocial'] ?></td>-->
                        <!--        <td><?= $f['Documento'] ?></td>-->
                        <!--    </tr>-->
                        <!--<?php endforeach; ?>-->
                        <!--</tbody>-->
                        
                        <tbody id="modalBodyForcli"></tbody>
                        
                    </table>
                </div>
                    
                    <button class="btn-secondary" onclick="fecharModalForcli()">Fechar</button>
                </div>
            </div>

            <input type="hidden" id="tipodespesa" name="tipodespesa">
            <div class="actions">
                <button class="btn" name="salvar" id="salvar">Salvar Lançamento</button>
               <a href= "/gerencial/BoletimCaixa" class="btn-secondary">Voltar</a>
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


//=====================================================================
document.getElementById('valor').addEventListener('blur', function () {
    const valorPago = document.getElementById('valorPago');
    if (valorPago.value === '') {
        valorPago.value = this.value;
    }
});

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

// window.addEventListener('click', function(e){

//     let modalDespesas = document.getElementById('modalDespesas');
//     let modalForcli = document.getElementById('modalForcli');

//     if (e.target === modalDespesas) {
//         fecharModalDespesas();
//     }

//     if (e.target === modalForcli) {
//         fecharModalForcli();
//     }

// });

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
                <tr onclick="selecionarForcli(${c.Id}, '${c.Nome.replace(/'/g,"\\'")}')">
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

function selecionarDespesa(codigo, descricao, valor) {

    document.getElementById('tipodespesa').value = codigo;
    document.getElementById('descricao').value = descricao;

    let v = parseFloat(valor)
        .toFixed(2)
        .replace('.', ',')
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    document.getElementById('valor').value = v;

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


</script>