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

// ================= CONSULTA CEP AJAX =================
if (isset($_GET['consultarCepAjax'])) {

    require_once '../base/baseFuncoes.php'; 

    $cep = $_GET['cep'] ?? '';

    $retorno = consultarCep($cep);

    header('Content-Type: application/json');
    echo json_encode($retorno);
    exit;
}

// ================= CONSULTA CNPJ AJAX =================
if (isset($_GET['consultarCnpjAjax'])) {
    $cnpj = $_GET['cnpj'] ?? '';
    
    // Função para consultar a API do CNPJ
    function buscarDadosCNPJ($cnpj) {
        $url = "https://open.cnpja.com/office/$cnpj";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if(json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Erro ao decodificar JSON'];
        }
        return $data;
    }

    $retorno = buscarDadosCNPJ($cnpj);
    header('Content-Type: application/json');
    echo json_encode($retorno);
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $dados = ExSqlNET(
        "SELECT *,
        (
            SELECT IFNULL(SUM(mi.TotalItem),0)
            FROM movimentoitem mi
            INNER JOIN movimento m 
                ON m.id = mi.ControleMovimento
            WHERE m.Forcli = forcli.id
            AND m.idEmpresa = ".$idEmpresa."
            AND m.Status = 0
        ) AS TotalDevedor

         FROM forcli
         WHERE id = ?
           AND idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );

    $dados = $dados[0] ?? null; // pega o registro
    $Alterando = true;

} else {
    $Alterando = false;
}

$msgRetorno =  '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar']) || isset($_POST['excluir'])) {
    
    $dados = [];

    $dados['Tipo'] = $_POST['Tipo'] ?? null;
    switch ($dados['Tipo']) {
        case 1:
            $dados['Tipo'] =  1; //Despachante
            break;
        case 2:
            $dados['Tipo'] = 2; //Revenda
            break;
        case 3:
            $dados['Tipo'] = 3; //Fornecedor
            break;
        case 4:
            $dados['Tipo'] = 4; //Consumidor Final
            break;
    }

    $dados['idEmpresa'] = $_SESSION['idEmpresa']  ?? null;
    
    $dados['Inativo'] = (int)!empty($_POST['inativo']);

    $dados['Descricao'] = $_POST['Descricao'] ?? "";
    $dados['id'] = $id;
    $dados['Nome'] = $_POST['Nome'] ?? "";
    $dados['Documento'] = $_POST['Documento'] ?? "";
    $dados['RazaoSocial'] = $_POST['RazaoSocial'] ?? "";
    $dados['TipoDocumento'] = 0;
    $dados['Email'] = $_POST['Email'] ?? "";
    $dados['Telefone'] = $_POST['Telefone'] ?? "";
    $dados['DataCadastro'] = date('Y-m-d H:i:s');
    $dados['CEP'] = $_POST['CEP'] ?? "";
    $dados['UF'] = $_POST['UF'] ?? "";
    $dados['Bairro'] = $_POST['Bairro'] ?? "";
    $dados['Cidade'] = $_POST['Cidade'] ?? "";
    $dados['Rua'] = $_POST['Rua'] ?? "";
    $dados['NumeroEndereco'] = $_POST['NumeroEndereco'] ?? "";
    $dados['Obs'] = $_POST['Obs'] ?? "";
    
    $dados['Grupo'] = !empty($_POST['grupo']) ? (int)$_POST['grupo'] : 0;
        
    if($Alterando == False){
        $dados['TotalDevedor'] = 0;
    }else{
        $total = ExSqlNET(
            "SELECT IFNULL(SUM(mi.TotalItem),0) As TotalDevedor
                FROM movimentoitem mi
                INNER JOIN movimento m 
                    ON m.id = mi.ControleMovimento
                WHERE m.Forcli = ".$id."
                AND m.idEmpresa = ".$idEmpresa."
                AND m.Status = 0",
            null
            );
        $total = $total[0] ?? [];
        $dados['TotalDevedor'] = $total['TotalDevedor'] ?? 0;
    }
    

    if ($Alterando === false){
        $dados['Codigo'] = retornaProximoCod("forcli");
        $retorno = Forcli($dados, "CADASTRAR");
        if ($retorno === "") {
<<<<<<< Updated upstream
=======

            $documentos = json_decode($_POST['documentos_json'] ?? '[]', true);
            
            // PEGANDO O ID DO FORCLI CADASTRADO AQUI NA HORA
            $result = ExSqlNET("SELECT id FROM forcli WHERE idEmpresa = ? AND Codigo = ? LIMIT 1
            ", null, [$_SESSION['idEmpresa'], $dados['Codigo']]);
            $dados['idForcli'] = $result[0]['id'] ?? null;


            foreach ($documentos as $d) {

                if (!is_array($d) || empty($d['novo'])) {
                    continue; // ignora arquivos antigos
                }

                $tipo = $d['tipo'] ?? null;
                $descricao = $d['descricao'] ?? '';
                $nome = $d['nome'] ?? '';
                $base64 = $d['base64'] ?? null;

                if (!$tipo || !$nome || !$base64) {
                    continue;
                }

                $dadosArquivo = [
                    'idEmpresa' => $_SESSION['idEmpresa'],
                    'idForcli' => $dados['id'],
                    'Tipo' => $tipo,
                    'Descricao' => $descricao,
                    'NomeArquivo' => $nome,
                    'ArquivoBase64' => $base64
                ];

                Arquivo($dadosArquivo, "CADASTRAR");
            }

>>>>>>> Stashed changes
            $msgRetorno = "Cliente / Fornecedor cadastrado com sucesso!";
            $tipoMsg = "success";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;
            header('Location: frmForcliLista.php');
            exit;

        } else {
            $msgRetorno = "Erro ao cadastrar o Cliente / Fornecedor. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }else if ($Alterando === true && isset($_POST['salvar'])){
        $retorno = Forcli($dados, "ATUALIZAR");
<<<<<<< Updated upstream
=======
        $documentos = json_decode($_POST['documentos_json'] ?? '[]', true);

        foreach ($documentos as $d) {

            if (!is_array($d) || empty($d['novo'])) {
                continue; // ignora arquivos antigos
            }

            $tipo = $d['tipo'] ?? null;
            $descricao = $d['descricao'] ?? '';
            $nome = $d['nome'] ?? '';
            $base64 = $d['base64'] ?? null;

            if (!$tipo || !$nome || !$base64) {
                continue;
            }

            $dadosArquivo = [
                'idEmpresa' => $_SESSION['idEmpresa'],
                'idForcli' => $dados['id'],
                'Tipo' => $tipo,
                'Descricao' => $descricao,
                'NomeArquivo' => $nome,
                'ArquivoBase64' => $base64
            ];

            Arquivo($dadosArquivo, "CADASTRAR");
        }

>>>>>>> Stashed changes
        if ($retorno === "") {
            $msgRetorno = "Cliente / Fornecedor atualizado com sucesso!";
            $tipoMsg = "success";
        } else {
            $msgRetorno = "Erro ao atualizar o Cliente / Fornecedor. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }else if(isset($_POST['excluir'])){
        $retorno = Forcli($dados, "EXCLUIR");
        if ($retorno === "") {
            $msgRetorno = "Cliente / Fornecedor excluído com sucesso!";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;
            $tipoMsg = "success";
            header('Location: frmForcliLista.php');
            exit;
        } else {
            $msgRetorno = "Erro ao excluir o Cliente / Fornecedor. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }

}

$nomeGrupo = '';

$grupos = ExSqlNET("
    SELECT Id, Nome 
    FROM grupos 
    WHERE idEmpresa = ? 
    AND Tipo = 'C'
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
    <title>Cadastro de Cliente - Autodoc</title>
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
        <div class="page-title">Cadastro de Cliente</div>
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
        <!-- DADOS PRINCIPAIS -->
        <div class="card">
            <div class="form-grid">

                <div>
                    <label>Tipo de Cliente</label>
                    <select name="Tipo">
                        <option value="1" <?= ($Alterando && $dados['Tipo'] == 1) ? 'selected' : '' ?>>
                            Despachante
                        </option>
                        <option value="2" <?= ($Alterando && $dados['Tipo'] == 2) ? 'selected' : '' ?>>
                            Revenda
                        </option>
                        <option value="3" <?= ($Alterando && $dados['Tipo'] == 3) ? 'selected' : '' ?>>
                            Fornecedor
                        </option>
                        <option value="4" <?= ($Alterando && $dados['Tipo'] == 4) ? 'selected' : '' ?>>
                            Consumidor Final
                        </option>
                    </select>
                </div>

                <!--
                <div>
                    <label>Data de Cadastro</label>
                    <input type="date">
                </div>
                -->

                <div>
                    <label>Grupo</label>
                
                    <input type="hidden" name="grupo" id="grupo_id" value="<?= $dados['Grupo'] ?? '' ?>">
                
                    <input type="text"
                           id="grupo_nome"
                           value="<?= htmlspecialchars($nomeGrupo) ?>"
                           placeholder="Clique para buscar..."
                           onclick="abrirModal('grupo')"
                           readonly>
                </div>
                
                <div class="checkbox">
                    <input 
                        type="checkbox" 
                        id="inativo" 
                        name="inativo"
                        value="1"
                        <?= ($Alterando && $dados['Inativo'] == 1) ? 'checked' : '' ?>
                    >
                    <label for="inativo">Cliente Inativo</label>
                </div>

            </div>
        </div>

        <!-- DADOS DO CLIENTE -->
        <div class="card">
            <div class="form-grid">

                <div>
                    <label>Documento - CPF/CNPJ</label>
                    <input type="text" id = "Documento" name ="Documento" value="<?= $Alterando ? htmlspecialchars($dados['Documento']) : '' ?>" oninput="mascararDocumento(this)">
                </div>

                <div>
                    <label>Nome</label>
                    <input type="text" id= "Nome" name ="Nome" value="<?= $Alterando ? htmlspecialchars($dados['Nome']) : '' ?>">
                </div>

                <div>
                    <label>Razão Social</label>
                    <input type="text" id = "RazaoSocial" name ="RazaoSocial" value="<?= $Alterando ? htmlspecialchars($dados['RazaoSocial']) : '' ?>">
                </div>

                <div>
                    <label>Telefone</label>
                    <input type="text"
                        id="Telefone"
                        name="Telefone"
                        value="<?= $Alterando ? htmlspecialchars($dados['Telefone']) : '' ?>"
                        oninput="formatarTelefone(this)">
                </div>

                <div>
                    <label>E-mail</label>
                    <input type="email" id ="Email" name ="Email" value="<?= $Alterando ? htmlspecialchars($dados['Email']) : '' ?>">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label>Descrição / Observações</label>
                    <textarea id ="Obs" name ="Obs"><?= htmlspecialchars($dados['Obs'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ENDEREÇO -->
        <div class="card">
            <h3 style="margin-bottom:10px; color:#334155;">Endereço</h3>
            <div class="form-grid">
                <div>
                    <label>CEP</label>
                    <input type="text" id ="CEP" name ="CEP" value="<?= $Alterando ? htmlspecialchars($dados['CEP']) : '' ?>">
                </div>
                <div>
                    <label>Rua</label>
                    <input type="text" id ="Rua" name ="Rua" value="<?= $Alterando ? htmlspecialchars($dados['Rua']) : '' ?>">
                </div>
                <div>
                    <label>Número</label>
                    <input type="text" id ="NumeroEndereco" name ="NumeroEndereco" value="<?= $Alterando ? htmlspecialchars($dados['NumeroEndereco']) : '' ?>">
                </div>
                <div>
                    <label>Bairro</label>
                    <input type="text" id ="Bairro" name ="Bairro" value="<?= $Alterando ? htmlspecialchars($dados['Bairro']) : '' ?>">
                </div>
                <div>
                    <label>Cidade</label>
                    <input type="text" id ="Cidade" name ="Cidade" value="<?= $Alterando ? htmlspecialchars($dados['Cidade']) : '' ?>">
                </div>
                <div>
                    <label>UF</label>
                    <input type="text" id ="UF" name ="UF" value="<?= $Alterando ? htmlspecialchars($dados['UF']) : '' ?>">
                </div>
            </div>
        </div>

        <!-- FINANCEIRO -->
        <div class="card">
            <div class="info-box">
                Total Devedor:
                <?= $Alterando
                    ? 'R$ ' . number_format((float)$dados['TotalDevedor'], 2, ',', '.')
                    : 'R$ 0,00'
                ?>
            </div>

            <div class="actions">

                <a href="frmForcliLista.php" class="btn btn-secondary">
                    Listar Clientes
                </a>
                <?php if ($Alterando): ?>
                    <button 
                        type="submit"
                        name="excluir"
                        class="btn-excluir"
                        onclick="return confirm('Tem certeza que deseja excluir este Cliente / Fornecedor? Essa ação não pode ser desfeita.')"
                    >
                        Excluir
                    </button>
                <?php endif; ?>

                <a href="frmForcliExtrato.php?cliente=<?= $id ?>&dataInicial=<?= date('Y-m-d') ?>&dataFinal=<?= date('Y-m-d') ?>" 
                class="btn btn-secondary">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Abrir Extrato
                </a>

                <button type="submit" name = "salvar" class="btn">
                    Salvar
                </button>
            </div>

        </form>
        
         <!-- ================= MODAL ================= -->
    
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
     
 function limparGrupo() {
    document.getElementById('grupo_id').value = 0;
    document.getElementById('grupo_nome').value = '';
}

 document.getElementById('modalBusca').addEventListener('input', function() {
    carregarModal(this.value.toLowerCase());
});

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


 function mascararDocumento(campo) {
        let valor = campo.value.replace(/\D/g, '');

        if (valor.length <= 11) {
            // CPF: 000.000.000-00
            valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
            valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
            valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            // CNPJ: 00.000.000/0000-00
            valor = valor.replace(/^(\d{2})(\d)/, "$1.$2");
            valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            valor = valor.replace(/\.(\d{3})(\d)/, ".$1/$2");
            valor = valor.replace(/(\d{4})(\d)/, "$1-$2");
        }

        campo.value = valor;
    }

    function formatarTelefone(input) {
        let telefone = input.value.replace(/\D/g, '');

        if (telefone.length > 11) {
            telefone = telefone.slice(0, 11);
        }

        if (telefone.length > 10) {
            // Celular
            telefone = telefone.replace(/^(\d{2})(\d{5})(\d{0,4})/, "($1) $2-$3");
        } else if (telefone.length > 6) {
            // Fixo
            telefone = telefone.replace(/^(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
        } else if (telefone.length > 2) {
            telefone = telefone.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
        } else {
            telefone = telefone.replace(/^(\d*)/, "($1");
        }

        input.value = telefone;
    }

    //==============================================================
    //FORMATAR O CEP
    document.getElementById('CEP').addEventListener('input', function(e) {
        let v = e.target.value;
        // Remove tudo que não for dígito
        v = v.replace(/\D/g, '');
        // Aplica a máscara: 5 dígitos + traço + 3 dígitos
        if (v.length > 5) {
            v = v.slice(0,5) + '-' + v.slice(5,8);
        }
        e.target.value = v;
    });

    // =============================
    // CONSULTAR CEP AO SAIR DO CAMPO
    // =============================
    document.getElementById('CEP').addEventListener('blur', function () {

        let cep = this.value.replace(/\D/g, '');

        if (cep.length !== 8) return;

        fetch(`?consultarCepAjax=1&cep=${cep}`)
            .then(response => response.json())
            .then(data => {

                if (data.status_code === 200) {

                    document.getElementById('Rua').value     = data.body.street || '';
                    document.getElementById('Bairro').value  = data.body.neighborhood || '';
                    document.getElementById('Cidade').value  = data.body.city || '';
                    document.getElementById('UF').value      = data.body.state || '';

                } else {
                    alert('CEP não encontrado.');
                }

            })
            .catch(() => {
                alert('Erro ao consultar CEP.');
            });

    });

    document.getElementById('Documento').addEventListener('blur', consultarCNPJ);
    document.getElementById('Documento').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            consultarCNPJ(true);
        }
    });

    function consultarCNPJ(focarProximo = false) {
        let campo = document.getElementById('Documento');
        let cnpj = campo.value.replace(/\D/g, '');

        if (cnpj.length !== 14) {
            if (focarProximo) campo.nextElementSibling?.focus();
            return;
        }

        fetch(`ajaxCnpj.php?cnpj=${cnpj}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Erro ao consultar CNPJ: ' + data.error);
                    if (focarProximo) campo.nextElementSibling?.focus();
                    return;
                }

                const razao = document.getElementById('RazaoSocial');
                if (!razao.value) razao.value = data.company?.name || '';

                const nome = document.getElementById('Nome');
                if (!nome.value) nome.value = data.alias || data.company?.name || '';

                const email = document.getElementById('Email');
                if (!email.value) email.value = data.emails?.[0]?.address || '';

                const telefone = document.getElementById('Telefone');
                if (!telefone.value) {
                    let phone = data.phones?.[0];
                    if (phone) telefone.value = (phone.area || '') + (phone.number || '');
                }

                const rua = document.getElementById('Rua');
                if (!rua.value) rua.value = data.address?.street || '';

                const numero = document.getElementById('NumeroEndereco');
                if (!numero.value) numero.value = data.address?.number || '';

                const bairro = document.getElementById('Bairro');
                if (!bairro.value) bairro.value = data.address?.district || '';

                const cidade = document.getElementById('Cidade');
                if (!cidade.value) cidade.value = data.address?.city || '';

                const uf = document.getElementById('UF');
                if (!uf.value) uf.value = data.address?.state || '';

                if (focarProximo) {
                    setTimeout(() => {
                        let formElements = Array.from(document.querySelectorAll('input, select, textarea'));
                        let index = formElements.indexOf(campo);
                        if (index >= 0 && formElements[index + 1]) {
                            formElements[index + 1].focus();
                        }
                    }, 50); 
                }

            })
            .catch(err => {
                console.error(err);
                alert('Erro ao consultar CNPJ.');
                if (focarProximo) campo.nextElementSibling?.focus();
            });
    }


    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            let modal = document.getElementById('modalBg');
    
            if (modal.style.display === 'flex') {
                fecharModal();
                limparGrupo();
            }
        }
    });
</script>