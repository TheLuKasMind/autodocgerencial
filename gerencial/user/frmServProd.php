<?php
include '../base/baseFuncoes.php'; 
require_once '../base/connection.php'; 
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {

    $quantidadeMes = 0;

    $dados = ExSqlNET(
        "SELECT *
         FROM servprod
         WHERE id = ?
            AND idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );

    $dados = $dados[0] ?? null; // pega o registro (Primeiro registro.)

    $dadosCusto = ExSqlNET("
        SELECT Data, ValorCusto, ValorVenda, Unidade,
            CASE Unidade WHEN 1 THEN 'UNIDADE' WHEN 2 THEN 'PACOTE' WHEN 3 THEN 'HORA' WHEN 4 THEN 'MENSAL'
            END AS UnidadeLiteral
        FROM servprodcusto
        WHERE servprod = ?
        AND idEmpresa = ?
        ORDER BY Data DESC
    ", null, [$dados['id'], $_SESSION['idEmpresa']]);

    $dadosMeta = ExSqlNET("
        SELECT SUM(Qtd) AS Total
        FROM movimentoitem
        LEFT JOIN movimento on movimento.id = movimentoitem.ControleMovimento
        WHERE ServProd = ?
        AND movimento.idEmpresa = ?
        AND MONTH(Data) = MONTH(CURDATE())
        AND YEAR(Data) = YEAR(CURDATE())
    ", null, [$id, $_SESSION['idEmpresa']]);

    $Alterando = true;

    $quantidadeMes = $dadosMeta[0]['Total'] ?? 0;

    $metaMensal = $dados['MetaMensal'] ?? 0;

    $percentual = 0;

    if ($metaMensal > 0) {
        $percentual = ($quantidadeMes / $metaMensal) * 100;
    }



} else {
    $Alterando = false;
}

$msgRetorno =  '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])  || isset($_POST['excluir'])) {
    
    $dados = [];

    $dados['Tipo'] = $_POST['Tipo'] ?? null;
    switch ($dados['Tipo']) {
        case 1:
            $dados['Tipo'] = 1;
            break;
        case 2:
            $dados['Tipo'] = 2;
            break;
    }

    $dados['SolicitaVeiculo'] = isset($_POST['SolicitaVeiculo']) ? 1 : 0;
    $dados['idEmpresa'] = $_SESSION['idEmpresa']  ?? null;
    $dados['Nome'] = $_POST['Nome'] ?? "";
    $dados['Inativo'] = isset($_POST['inativo']) ? 1 : 0;
    $dados['Descricao'] = $_POST['Descricao'] ?? "";
    $dados['Unidade'] = $_POST['Unidade'] ?? "";
    $dados['ValorCusto'] = formataValorGravacao($_POST['valorCusto'] ?? 0); 
    $dados['ValorVenda'] = formataValorGravacao($_POST['valorVenda'] ?? 0); 
    $dados['id'] = $id;
    $dados['Codigo'] = retornaProximoCod("servprod");
    $dados['ServProd'] = $dados['Codigo'];
    
    $dados['MetaMensal'] = (int)($_POST['MetaMensal'] ?? 0);

    $dados['Grupo'] = !empty($_POST['grupo']) ? (int)$_POST['grupo'] : 0;
 
    if ($Alterando === false){
        
        $retorno = ServProd($dados, "CADASTRAR");
        if ($retorno === "") {
            $msgRetorno = "Produto / Serviço com sucesso!";
            $tipoMsg = "success";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;

            //AQUI CADASTRA O MOVIMENTO DE CUSTO
            $dados['ServProd'] = $_SESSION['idGerado'];
            $retorno = ServProdCusto($dados, "CADASTRAR");
            if ($retorno === "") {
                $msgRetorno = "Produto / Serviço cadastrado com sucesso!";
                $tipoMsg = "success";
                $_SESSION['mensagem_sucesso'] = $msgRetorno;
                header('Location: frmServProdLista.php');
                exit;
            } else {
                $msgRetorno = "Erro ao cadastrar MovimentoCusto Produto / Serviço. Erro -> ". $retorno;
                $tipoMsg = "error";
            }

            // header('Location: frmServProdLista.php');
            // exit;
        } else {
            $msgRetorno = "Erro ao cadastrar o Produto / Serviço. Erro -> ". $retorno;
            $tipoMsg = "error";
        }

    }else if ($Alterando === true && isset($_POST['salvar'])){
        $retorno = ServProd($dados, "ATUALIZAR");
        if ($retorno === "") {
            $msgRetorno = "Produto / Serviço atualizado com sucesso!";
            $tipoMsg = "success";

            //AQUI CADASTRA O MOVIMENTO DE CUSTO
            $dados['ServProd'] = $dados['id'];
            $retorno = ServProdCusto($dados, "CADASTRAR");
            if ($retorno === "") {
                $msgRetorno = "Produto / Serviço atualizado com sucesso!";
                $tipoMsg = "success";
                $_SESSION['mensagem_sucesso'] = $msgRetorno;
                header('Location: frmServProdLista.php');
                exit;
            } else {
                $msgRetorno = "Erro ao cadastrar MovimentoCusto Produto / Serviço. Erro -> ". $retorno;
                $tipoMsg = "error";
            }

        } else {
            $msgRetorno = "Erro ao atualizar o Produto / Serviço. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }else if(isset($_POST['excluir'])){
        
        $dados['servprod'] = $id;
        $retorno = ServProd($dados, "EXCLUIR");
        $retornoCusto = ServProdCusto($dados, "EXCLUIR");
        
        if ($retorno === "" && $retornoCusto === "") {
            $msgRetorno = "Produto / Serviço excluído com sucesso!";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;
            $tipoMsg = "success";
            header('Location: frmServProdLista.php');
            exit;
        } else {
            $msgRetorno = "Erro ao excluir o Produto / Serviço. Erro -> ". $retorno. $retornoCusto;
            $tipoMsg = "error";
        }
    }

}

$nomeGrupo = '';

$grupos = ExSqlNET("
    SELECT Id, Nome 
    FROM grupos 
    WHERE idEmpresa = ? 
    AND Tipo = 'P'
    ORDER BY Nome
", null, [$_SESSION['idEmpresa']]);
    
if (!empty($dados['Grupo'])) {

    $grupoSelecionado = ExSqlNET("
        SELECT Nome 
        FROM grupos 
        WHERE Id = ? AND idEmpresa = ?
    ", null, [$dados['Grupo'], $_SESSION['idEmpresa']]);

    if ($grupoSelecionado) {
        $nomeGrupo = $grupoSelecionado[0]['Nome'];
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Produto / Serviço - Autodoc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">

    <!-- CSS BASE DO SISTEMA -->
    <link rel="stylesheet" href="../css/base.css">

<style>
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
        <form method="post">
        <div class="page-title">Cadastro de Produto / Serviço</div>

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

        <!-- DADOS DO PRODUTO -->
        <div class="card">
            <div class="form-grid">

                <div>
                    <label>Nome</label>
                    <input type="text" id ="Nome" name="Nome" placeholder="Nome do produto ou serviço" value="<?= $Alterando ? htmlspecialchars($dados['Nome']) : '' ?>">
                </div>

                <div>
                    <label>Código</label>
                    <input type="text" placeholder="Código interno" value="<?= $Alterando ? htmlspecialchars($dados['Codigo']) : '' ?>" readonly>
                </div>

                <div>
                    <label>Grupo</label>
                
                    <input type="hidden" name="grupo" id="grupo_id" ">
                
                    <input type="text"
                           id="grupo_nome"
                           value="<?= htmlspecialchars($nomeGrupo) ?>"
                           placeholder="Clique para buscar..."
                           onclick="abrirModal('grupo')"
                           readonly>
                </div>
                    
                <!-- <div>
                    <label>Tipo</label>
                    <select name="Tipo" id="Tipo">
                        <option value="servico">Serviço</option>
                        <option value="produto">Produto</option>
                    </select>
                </div> -->

                <div>
                    <label>Tipo</label>
                    <select name="Tipo" id="Tipo">
                        <option value="1" <?= ($Alterando && $dados['Tipo'] == 1) ? 'selected' : '' ?>>
                            Serviço
                        </option>
                        <option value="2" <?= ($Alterando && $dados['Tipo'] == 2) ? 'selected' : '' ?>>
                            Produto
                        </option>
                    </select>
                </div>

                <div>
                    <label>Unidade</label>
                    <select name="Unidade" id="Unidade">
                        <option value="1" <?= ($Alterando && $dados['Unidade'] == 1) ? 'selected' : '' ?>>
                            Unidade
                        </option>
                        <option value="2" <?= ($Alterando && $dados['Unidade'] == 2) ? 'selected' : '' ?>>
                            Pacote
                        </option>
                        <option value="3" <?= ($Alterando && $dados['Unidade'] == 3) ? 'selected' : '' ?>>
                            Hora
                        </option>
                        <option value="4" <?= ($Alterando && $dados['Unidade'] == 4) ? 'selected' : '' ?>>
                            Mensal
                        </option>
                    </select>
                </div>

                <div>
                    <label>Valor de Custo (R$)</label>
                    <input 
                        type="text"
                        id="valorCusto"
                        name="valorCusto"
                        placeholder="0,00"
                        value="<?= $Alterando ? number_format((float)$dados['ValorCusto'], 2, ',', '.') : '' ?>"
                    >
                </div>
                
                <div>
                    <label>Valor de Venda (R$)</label>
                    <input 
                        type="text"
                        id="valorVenda"
                        name="valorVenda"
                        placeholder="0,00"
                        value="<?= $Alterando ? number_format((float)$dados['ValorVenda'], 2, ',', '.') : '' ?>"
                    >

                </div>

                <div class="checkbox">
                    <input type="checkbox" name="SolicitaVeiculo" id ="SolicitaVeiculo"
                        <?= $Alterando && !empty($dados['SolicitaVeiculo']) ? 'checked' : '' ?>>
                    <label for="SolicitaVeiculo">Solicita dados do veículo</label>
                </div>

                <div class="checkbox">
                    <input 
                        type="checkbox" 
                        id="inativo" 
                        name="inativo"
                        value="1"
                        <?= ($Alterando && $dados['Inativo'] == 1) ? 'checked' : '' ?>
                    >
                    <label for="inativo">Produto Inativo</label>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label>Descrição</label>
                    <textarea placeholder="Descrição detalhada" id="Descricao" name="Descricao"><?= htmlspecialchars($dados['Descricao'] ?? '') ?></textarea>
                </div>

                <div>
                    <label>Meta Mensal (Qtd)</label>
                    <input 
                        type="number" 
                        name="MetaMensal" 
                        id="MetaMensal"
                        placeholder="Ex: 100"
                        value="<?= $Alterando ? htmlspecialchars($dados['MetaMensal'] ?? 0) : '' ?>"
                    >
                </div>
                <div>
                    <label>Quantidade Vendida no Mês</label>
                    <input 
                        type="text" 
                        readonly
                        value="<?= $Alterando ? $quantidadeMes : 0 ?>"
                    >
                </div>
                <div>
                    <label>Progresso da Meta</label>
                    <input 
                        type="text" 
                        readonly
                        value="<?= number_format((float)($percentual ?? 0), 1) ?> %"
                    >
                </div>

            </div>
        </div>

        <!-- HISTÓRICO DE CUSTOS -->
        <div class="card">
            <h3 style="margin-bottom: 10px; color:#334155;">Histórico de Custos e Preços</h3>

            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Custo</th>
                        <th>Preço Venda</th>
                        <th>Unidade</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($dadosCusto)): ?>
                    <?php foreach ($dadosCusto as $custo): ?>

                        <tr>
                            <td><?= date('d/m/Y', strtotime($custo['Data'])) ?></td>

                            <td>
                                R$ <?= number_format((float)$custo['ValorCusto'], 2, ',', '.') ?>
                            </td>

                            <td>
                                R$ <?= number_format((float)$custo['ValorVenda'], 2, ',', '.') ?>
                            </td>

                            <td><?= htmlspecialchars($custo['UnidadeLiteral']) ?></td>

                        </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#888;">
                                Nenhum histórico de custo cadastrado
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>

        </div>

        <!-- AÇÕES -->
        <div class="actions">
            <a href="frmServProdLista.php" class="btn btn-secondary">
                Listar Produtos
            </a>
            <?php if ($Alterando): ?>
                <button 
                    type="submit"
                    name="excluir"
                    class="btn-excluir"
                    onclick="return confirm('Tem certeza que deseja excluir este Produto / Serviço? Essa ação não pode ser desfeita.')"
                >
                    Excluir
                </button>
            <?php endif; ?>
            <button type="submit" name= "salvar" class="btn">Salvar</button>
        </div>
        </form>
    
      <!-- ================= MODAL ================= -->
    
        <div class="modal-bg" id="modalBg">
            <div class="modal">
    
                <h3 id="modalTitulo"></h3>
    
                <input type="text" id="modalBusca" class="modal-search" placeholder="Digite para buscar...">
            
                <!--<table>-->
                <!--    <thead>-->
                <!--        <tr id="modalHead"></tr>-->
                <!--    </thead>-->
                <!--    <tbody id="modalBody"></tbody>-->
                <!--</table>-->
    
                    <div class="modal-table-wrapper">
                        <table>
                            <thead>
                                <tr id="modalHead"></tr>
                            </thead>
                            <tbody id="modalBody"></tbody>
                        </table>
                    </div>
    
                <br>
                <button class="btn" onclick="fecharModal()">Fechar</button>
    
            </div>
        </div>
    
    </div>
</body>
</html>
<script>
 
    let grupos = <?= json_encode($grupos ?? []) ?>;
    let tipoModal = '';
     
     
 document.getElementById('modalBusca').addEventListener('input', function() {
    carregarModal(this.value.toLowerCase());
});

function limparGrupo() {
    document.getElementById('grupo_id').value = 0;
    document.getElementById('grupo_nome').value = '';
}

document.getElementById('modalBg').addEventListener('click', function(e) {

    if (e.target === this) {

        if (tipoModal === 'grupo') {
            limparGrupo();
        }

        fecharModal();
    }

});

function carregarModal(filtro = '') {

    let head = document.getElementById('modalHead');
    let body = document.getElementById('modalBody');

    head.innerHTML = '';
    body.innerHTML = '';

    filtro = (filtro || '').toLowerCase();

    let html = '';

    if (tipoModal === 'grupo') {

        document.getElementById('modalTitulo').innerText = 'Selecionar Grupo';

        head.innerHTML = '<th>Nome</th>';

        (grupos || [])
            .filter(g => (g.Nome || '').toLowerCase().includes(filtro))
            .forEach(g => {

                let nomeSeguro = (g.Nome || '').replace(/'/g, "\\'");

                html += `
                    <tr onclick="selecionarGrupo(${g.Id}, '${nomeSeguro}')">
                        <td>${g.Nome || ''}</td>
                    </tr>`;
            });
    }

    body.innerHTML = html;
}

function selecionarGrupo(id, nome) {
    document.getElementById('grupo_id').value = id;
    document.getElementById('grupo_nome').value = nome;
    fecharModal();
}

 function abrirModal(tipo) {

    tipoModal = tipo;

    document.getElementById('modalBg').style.display = 'flex';

    let input = document.getElementById('modalBusca');

    input.value = '';

    carregarModal('');

    setTimeout(() => {
        input.focus();
    }, 50);

}
 
function fecharModal() {
    document.getElementById('modalBg').style.display = 'none';
}
 
function abrirListaProdutos() {
    window.location.href = 'servprodLista.php';
}

//======================================================================
function formatarMoeda(campo) {
    let v = campo.value.replace(/\D/g, '');

    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    campo.value = v;
}

//======================================================================
document.getElementById('valorVenda')?.addEventListener('input', function () {
    formatarMoeda(this);
});
//======================================================================
document.getElementById('valorCusto')?.addEventListener('input', function () {
    formatarMoeda(this);
});

//FECHANDO MODAL DE GRUPO NO ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalBg');
        if (modal.style.display === 'flex') {
            if (tipoModal === 'grupo') {
                limparGrupo();
            }

            fecharModal();
        }
    }
});
</script>
