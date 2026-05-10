
<?php
include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require_once '../base/verificaPlano.php';
require_once 'docModels/modelProcuracao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$msgRetorno = '';
$tipoMsg = '';

$clientes = ExSqlNET("
    SELECT id, Nome, Documento
    FROM forcli
    WHERE idEmpresa = ?
    AND Inativo = 0
    ORDER BY Nome
", null, [$_SESSION['idEmpresa']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerarDocumento'])) {

    $tipoDocumento = $_POST['tipoDocumento'] ?? '';
    $clienteId = (int)($_POST['cliente_id'] ?? 0);

    if ($tipoDocumento == 'procuracao') { 

        if ($clienteId <= 0) {
            die('Cliente não informado.');
        }

        // ================= CLIENTE =================
        $dadosCliente = ExSqlNET(
        "SELECT Nome,Documento, Rua AS Endereco,NumeroEndereco,Bairro,Cidade,UF, Profissao,
            CASE 
                WHEN EstadoCivil = 1 THEN 'Solteiro(a)'
                WHEN EstadoCivil = 2 THEN 'Casado(a)'
                WHEN EstadoCivil = 3 THEN 'Divorciado(a)'
                WHEN EstadoCivil = 4 THEN 'Viúvo(a)'
                WHEN EstadoCivil = 5 THEN 'União estável'
            ELSE 'EstadoCivil Não informado'
            END AS EstadoCivil
            FROM forcli
            WHERE id = ? AND idEmpresa = ?",
            null,
            [$clienteId, $_SESSION['idEmpresa']]
        );

        if (!$dadosCliente) {
            die('Cliente não encontrado.');
        }

        $cliente = $dadosCliente[0];

        // ================= USUÁRIO LOGADO (OUTORGADO) =================
        $dadosUsuario = ExSqlNET(
            "SELECT Nome, Documento, Rua As Endereco, Bairro, NumeroEndereco, CEP, Cidade, UF, NumOab, Contato, LogoBase64,
            CASE 
                WHEN EstadoCivil = 1 THEN 'Solteiro(a)'
                WHEN EstadoCivil = 2 THEN 'Casado(a)'
                WHEN EstadoCivil = 3 THEN 'Divorciado(a)'
                WHEN EstadoCivil = 4 THEN 'Viúvo(a)'
                WHEN EstadoCivil = 5 THEN 'União estável'
                ELSE 'EstadoCivil Não informado'
            END AS EstadoCivil
            FROM user WHERE id = ?",
            null,
            [$_SESSION['usuario_id']]
        );

        if (!$dadosUsuario) {
            die('Usuário não encontrado.');
        }
        $usuario = $dadosUsuario[0];

        // ================= CLIENTE PDF =================
        
        $clientePdf = [
            'Nome' => $cliente['Nome'] ?? '',
            'CpfCnpj' => $cliente['Documento'] ?? '',
            'Profissao' => $cliente['Profissao'] ?? 'Profissão não informada',
            'EstadoCivil' => $cliente['EstadoCivil'] ?? '',
            'Endereco' => $cliente['Endereco'] ?? '',
            'Cidade' => $cliente['Cidade'] ?? 'Cidade não informada',
            'Bairro' => $cliente['Bairro'] ?? 'Bairro não informado',
            'NumeroEndereco' => $cliente['NumeroEndereco'] ?? 'Número Endereço não informado',
            'Estado' => $cliente['UF'] ?? 'UF não informada'
        ];

        // ================= OUTORGADO PDF =================

        $outorgadoPdf = [
            'Nome' => $usuario['Nome'] ?? '',
            'OAB' => $usuario['NumOab'] ?? '',
            'CPF' => $usuario['Documento'] ?? '',
            'Endereco' => $usuario['Endereco'] ?? '',
            'CEP' => $usuario['CEP'] ?? '',
            'EstadoCivil' => $usuario['EstadoCivil'] ?? '',
            'Cidade' => $usuario['Cidade'] ?? '',
            'Estado' => $usuario['Estado'] ?? '',
            'Bairro' => $usuario['Bairro'] ?? '',
            'NumeroEndereco' => $usuario['NumeroEndereco'] ?? '',
            'Contato' => $usuario['Contato'] ?? '',
            'LogoBase64' => $usuario['LogoBase64'] ?? ''
        ];

        // ================= GERAR PDF =================
        ModelProcuracao::gerarProcuracaoPDF($clientePdf, $outorgadoPdf);
        exit;

    } else {

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geração de Documentos - Autodoc</title>
    <link rel="icon" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/base.css?v=15">

    <style>

        .document-selector {
            position: relative;
        }

        .document-selector select {
            background: linear-gradient(135deg, #fff7ed, #ffffff);
            border: 1px solid #fdba74;
            font-weight: 600;
            color: #9a3412;
        }

        .dynamic-fields {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 15px;
        }

        .fade-in {
            animation: fadeIn .25s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-box-doc {
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
            padding: 14px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 14px;
        }


        
        /* ===== MODAL PADRÃO DO SISTEMA ===== */
        
        .modal-bg {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(3px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.15s ease;
        }
        
        .modal {
            background: #ffffff;
            width: 650px;
            max-width: 95%;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.18);
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: modalUp 0.18s ease;
        }
        
        .modal h3 {
            margin: 0 0 12px 0;
            font-size: 18px;
            color: #333;
        }
        
        .modal-search {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #dcdcdc;
            font-size: 14px;
            outline: none;
            transition: border 0.2s, box-shadow 0.2s;
        }
        
        .modal-search:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234,88,12,0.15);
        }
        
        .modal table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }
        
        .modal thead {
            background: #fff7ed;
        }
        
        .modal th {
            text-align: left;
            padding: 10px;
            font-weight: 600;
            color: #9a3412;
        }
        
        .modal td {
            padding: 9px 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        
        .modal tbody {
            overflow-y: auto;
        }
        
        .modal tbody tr {
            transition: background 0.15s;
        }
        
        .modal tbody tr:hover {
            background: #fff7ed;
        }
        
        
        .modal button {
            margin-top: 15px;
            align-self: flex-end;
        }
        
        
        @keyframes modalUp {
            from {
                transform: translateY(15px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    
    
    
        .modal-table-wrapper{
            max-height: 380px;
            overflow-y: auto;
            margin-top: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        .modal-table-wrapper thead th{
            position: sticky;
            top: 0;
            background: #fff7ed;
            z-index: 2;
        }
        
        .modal-table-wrapper::-webkit-scrollbar{
            width: 8px;
        }
        
        .modal-table-wrapper::-webkit-scrollbar-track{
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .modal-table-wrapper::-webkit-scrollbar-thumb{
            background: #d6d6d6;
            border-radius: 10px;
        }
        
        .modal-table-wrapper::-webkit-scrollbar-thumb:hover{
            background: #bdbdbd;
        }
    </style>
</head>
<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">
    <form method="POST" target="_blank" id="formDocumentos">

        <div class="page-title">Geração de Documentos</div>
        <div class="page-subtitle">
            Gere documentos personalizados de forma rápida e profissional.
        </div>

        <?php if ($msgRetorno): ?>
            <div class="alert <?= $tipoMsg ?>">
                <?= htmlspecialchars($msgRetorno) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="form-grid">

                <div class="document-selector">
                    <label>Tipo de Documento</label>
                    <select name="tipoDocumento" id="tipoDocumento" onchange="atualizarCamposDocumento()">
                        <option value="">Selecione...</option>
                        <option value="procuracao">Procuração</option>
                    </select>
                </div>

                </div>

            <div id="camposDinamicos" class="dynamic-fields"></div>
        </div>

        <div class="card">
            <div class="actions">
                <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
                <button type="submit" name="gerarDocumento" class="btn">
                    Gerar Documento PDF
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    let tipoModal = '';
    let clientes = <?= json_encode($clientes ?? []) ?>;

    function atualizarCamposDocumento() {
        const tipo = document.getElementById('tipoDocumento').value;
        const container = document.getElementById('camposDinamicos');

        container.innerHTML = '';

        if (tipo === 'procuracao') {
            container.innerHTML = `
                <div class="fade-in">
                    <div class="section-title">Dados do Cliente / Outorgante</div>

                    <div class="form-grid">
                        <div>
                            <label>Cliente / Outorgante</label>
                            <input type="hidden" name="cliente_id" id="cliente_id">
                            <input type="text"
                                   id="cliente_nome"
                                   placeholder="Clique para buscar cliente..."
                                   onclick="abrirModal('cliente')"
                                   readonly>
                        </div>
                    </div>

                    
                </div>
            `;
        }
    }

    function abrirModal(tipo) {
        tipoModal = tipo;
        document.getElementById('modalBg').style.display = 'flex';
        document.getElementById('modalBusca').value = '';
        carregarModal('');
        setTimeout(() => {
            document.getElementById('modalBusca').focus();
        }, 50);
    }

    function fecharModal() {
        document.getElementById('modalBg').style.display = 'none';
    }

    function carregarModal(filtro = '') {
        let head = document.getElementById('modalHead');
        let body = document.getElementById('modalBody');

        head.innerHTML = '';
        body.innerHTML = '';

        filtro = filtro.toLowerCase();
        let html = '';

        if (tipoModal === 'cliente') {
            document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';
            head.innerHTML = '<th>Nome</th><th>Documento</th>';

            (clientes || [])
                .filter(c => (c.Nome || '').toLowerCase().includes(filtro))
                .forEach(c => {
                    let nomeSeguro = (c.Nome || '').replace(/'/g, "\'");
                    html += `
                        <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
                            <td>${c.Nome}</td>
                            <td>${c.Documento || ''}</td>
                        </tr>`;
                });
        }

        body.innerHTML = html;
    }

    function selecionarCliente(id, nome) {
        document.getElementById('cliente_id').value = id;
        document.getElementById('cliente_nome').value = nome;
        fecharModal();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalBusca').addEventListener('input', function() {
            carregarModal(this.value);
        });

        document.getElementById('modalBg').addEventListener('click', function(e) {
            if (e.target === this) fecharModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') fecharModal();
        });
    });

    document.getElementById('formDocumentos').addEventListener('submit', function() {
        this.target = '_blank';
    });

</script>

<!-- MODAL PADRÃO -->
<div class="modal-bg" id="modalBg">
    <div class="modal">
        <h3 id="modalTitulo"></h3>
        <input type="text" id="modalBusca" class="modal-search" placeholder="Digite para buscar...">

        <div class="modal-table-wrapper">
            <table>
                <thead>
                    <tr id="modalHead"></tr>
                </thead>
                <tbody id="modalBody"></tbody>
            </table>
        </div>

        <button class="btn" onclick="fecharModal()">Fechar</button>
    </div>
</div>

</body>
</html>
