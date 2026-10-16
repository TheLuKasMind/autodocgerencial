<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$id = $_GET['id'] ?? null;
$dados = [];
$dadosCusto = [];
$quantidadeMes = 0;
$percentual = 0;
$nomeGrupo = '';

if ($id) {
    $dados = ExSqlNET(
        "SELECT * FROM servprod WHERE id = ? AND idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );
    $dados = $dados[0] ?? null;

    if ($dados) {
        $dadosCusto = ExSqlNET("SELECT Data, ValorCusto, ValorVenda, Unidade,
            CASE Unidade WHEN 1 THEN 'UNIDADE' WHEN 2 THEN 'PACOTE' WHEN 3 THEN 'HORA' WHEN 4 THEN 'MENSAL' END AS UnidadeLiteral
            FROM servprodcusto WHERE servprod = ? AND idEmpresa = ? ORDER BY Data DESC",
            null, [$dados['id'], $_SESSION['idEmpresa']]);

        $dadosMeta = ExSqlNET("SELECT SUM(Qtd) AS Total
            FROM movimentoitem
            LEFT JOIN movimento ON movimento.id = movimentoitem.ControleMovimento
            WHERE ServProd = ? AND movimento.idEmpresa = ?
            AND MONTH(Data) = MONTH(CURDATE()) AND YEAR(Data) = YEAR(CURDATE())",
            null, [$id, $_SESSION['idEmpresa']]);

        $Alterando = true;
        $quantidadeMes = $dadosMeta[0]['Total'] ?? 0;
        $metaMensal = $dados['MetaMensal'] ?? 0;
        if ($metaMensal > 0) {
            $percentual = ($quantidadeMes / $metaMensal) * 100;
        }
    } else {
        $Alterando = false;
        $dados = [];
    }
} else {
    $Alterando = false;
}

$msgRetorno = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['salvar']) || isset($_POST['excluir']))) {
    $dadosPost = [];
    $dadosPost['Tipo'] = $_POST['Tipo'] ?? null;
    switch ((string)$dadosPost['Tipo']) {
        case '1': $dadosPost['Tipo'] = 1; break;
        case '2': $dadosPost['Tipo'] = 2; break;
    }

    $dadosPost['SolicitaVeiculo'] = isset($_POST['SolicitaVeiculo']) ? 1 : 0;
    $dadosPost['idEmpresa'] = $_SESSION['idEmpresa'] ?? null;
    $dadosPost['Nome'] = $_POST['Nome'] ?? '';
    $dadosPost['Inativo'] = isset($_POST['inativo']) ? 1 : 0;
    $dadosPost['Descricao'] = $_POST['Descricao'] ?? '';
    $dadosPost['Unidade'] = $_POST['Unidade'] ?? '';
    $dadosPost['ValorCusto'] = formataValorGravacao($_POST['valorCusto'] ?? 0);
    $dadosPost['ValorVenda'] = formataValorGravacao($_POST['valorVenda'] ?? 0);
    $dadosPost['id'] = $id;
    $dadosPost['Codigo'] = retornaProximoCod("servprod");
    $dadosPost['ServProd'] = $dadosPost['Codigo'];
    $dadosPost['MetaMensal'] = (int)($_POST['MetaMensal'] ?? 0);
    $dadosPost['Grupo'] = !empty($_POST['grupo']) ? (int)$_POST['grupo'] : 0;

    if ($Alterando === false && isset($_POST['salvar'])) {
        $retorno = ServProd($dadosPost, "CADASTRAR");
        if ($retorno === "") {
            $msgRetorno = "Produto / Serviço cadastrado com sucesso!";
            $tipoMsg = "success";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;
            $dadosPost['ServProd'] = $_SESSION['idGerado'];
            $retorno = ServProdCusto($dadosPost, "CADASTRAR");
            if ($retorno === "") {
                $_SESSION['mensagem_sucesso'] = "Produto / Serviço cadastrado com sucesso!";
                header('Location: Produtos');
                exit;
            }
            $msgRetorno = "Erro ao cadastrar MovimentoCusto Produto / Serviço. Erro -> " . $retorno;
            $tipoMsg = "error";
        } else {
            $msgRetorno = "Erro ao cadastrar o Produto / Serviço. Erro -> " . $retorno;
            $tipoMsg = "error";
        }
    } elseif ($Alterando === true && isset($_POST['salvar'])) {
        $dadosAtual = ExSqlNET("SELECT ValorCusto, ValorVenda FROM servprod WHERE id = ? AND idEmpresa = ?", null, [$dadosPost['id'], $_SESSION['idEmpresa']]);
        $dadosAtual = $dadosAtual[0] ?? null;
        $valorCustoAtual = (float)($dadosAtual['ValorCusto'] ?? 0);
        $valorVendaAtual = (float)($dadosAtual['ValorVenda'] ?? 0);
        $valorCustoNovo = (float)$dadosPost['ValorCusto'];
        $valorVendaNovo = (float)$dadosPost['ValorVenda'];
        $cadastrarHistorico = ($valorCustoAtual != $valorCustoNovo || $valorVendaAtual != $valorVendaNovo);

        $retorno = ServProd($dadosPost, "ATUALIZAR");
        if ($retorno === "") {
            if ($cadastrarHistorico) {
                $dadosPost['ServProd'] = $dadosPost['id'];
                $retornoHistorico = ServProdCusto($dadosPost, "CADASTRAR");
                if ($retornoHistorico !== "") {
                    $msgRetorno = "Produto atualizado, mas erro ao gravar histórico de custo. Erro -> " . $retornoHistorico;
                    $tipoMsg = "error";
                } else {
                    $_SESSION['mensagem_sucesso'] = "Produto atualizado com sucesso!";
                    header('Location: Produtos');
                    exit;
                }
            } else {
                $_SESSION['mensagem_sucesso'] = "Produto atualizado com sucesso!";
                header('Location: Produtos');
                exit;
            }
        } else {
            $msgRetorno = "Erro ao atualizar o Produto / Serviço. Erro -> " . $retorno;
            $tipoMsg = "error";
        }
    } elseif (isset($_POST['excluir'])) {
        $dadosPost['servprod'] = $id;
        $retorno = ServProd($dadosPost, "EXCLUIR");
        $retornoCusto = ServProdCusto($dadosPost, "EXCLUIR");
        if ($retorno === "" && $retornoCusto === "") {
            $msgRetorno = "Produto / Serviço excluído com sucesso!";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;
            $tipoMsg = "success";
            header('Location: Produtos');
            exit;
        }
        $msgRetorno = "Erro ao excluir o Produto / Serviço. Erro -> " . $retorno . $retornoCusto;
        $tipoMsg = "error";
    }
    // Mantém os valores preenchidos na tela quando ocorre erro de gravação.
    if ($tipoMsg === 'error') {
        $dados = array_merge($dados ?? [], $dadosPost);
    }
}

$grupos = ExSqlNET("SELECT Id, Nome FROM grupos WHERE idEmpresa = ? AND Tipo = 'P' ORDER BY Nome", null, [$_SESSION['idEmpresa']]);
if (!empty($dados['Grupo'])) {
    $grupoSelecionado = ExSqlNET("SELECT Nome FROM grupos WHERE Id = ? AND idEmpresa = ?", null, [$dados['Grupo'], $_SESSION['idEmpresa']]);
    if ($grupoSelecionado) {
        $nomeGrupo = $grupoSelecionado[0]['Nome'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $Alterando ? 'Editar Produto / Serviço' : 'Cadastro de Produto / Serviço' ?> - Autodoc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        /* Modal de seleção de grupo */
        .modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.48);backdrop-filter:blur(4px);display:none;justify-content:center;align-items:center;z-index:9999;animation:fadeIn .15s ease;padding:16px}
        .modal{background:#fff;width:650px;max-width:100%;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 24px 60px rgba(15,23,42,.2);max-height:85vh;overflow:hidden;display:flex;flex-direction:column;animation:modalUp .18s ease}
        .modal h3{margin:0 0 16px;font-size:18px;color:#1e293b;font-weight:700}
        .modal-search{width:100%;padding:11px 13px;border-radius:10px;border:1px solid #cbd5e1;font-size:14px;outline:none;transition:border .2s,box-shadow .2s}
        .modal-search:focus{border-color:#ea580c;box-shadow:0 0 0 3px rgba(234,88,12,.13)}
        .modal table{width:100%;border-collapse:collapse;margin-top:0;font-size:14px}
        .modal thead{background:#fff7ed}
        .modal th{text-align:left;padding:12px;font-weight:700;color:#9a3412}
        .modal td{padding:11px 12px;border-bottom:1px solid #f1f5f9;cursor:pointer}
        .modal tbody tr{transition:background .15s}.modal tbody tr:hover{background:#fff7ed}
        .modal button{margin-top:16px;align-self:flex-end}
        .modal-table-wrapper{max-height:380px;overflow-y:auto;margin-top:12px;border:1px solid #e2e8f0;border-radius:10px}
        .modal-table-wrapper thead th{position:sticky;top:0;background:#fff7ed;z-index:2}
        @keyframes modalUp{from{transform:translateY(12px);opacity:0}to{transform:translateY(0);opacity:1}}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}

        /* ===== DESIGN MODERNO: CADASTRO DE PRODUTOS ===== */
        .content{width:min(1180px,calc(100% - 40px));max-width:1180px;margin:30px auto 48px;padding:0}
        .content>form{display:flex;flex-direction:column;gap:20px}
        .page-title{font-size:28px;line-height:1.25;font-weight:750;letter-spacing:-.7px;color:#0f172a;margin:0 0 10px}
        .page-subtitle{font-size:14px;line-height:1.6;color:#64748b;margin:0 0 2px;padding-top:2px}
        .content .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 4px 18px rgba(15,23,42,.045);overflow:hidden}
        .section-heading{display:flex;align-items:center;gap:11px;margin:0 0 22px;color:#1e293b;font-size:17px;font-weight:750;letter-spacing:-.2px}
        .section-heading:before{content:"";display:block;width:4px;height:22px;border-radius:5px;background:#f97316;flex:0 0 4px}
        .section-description{font-size:13px;color:#64748b;margin:-13px 0 20px 15px}
        .form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px 18px}
        .form-grid>div{min-width:0}
        .content label{display:block;margin:0 0 7px;color:#334155;font-size:13px;font-weight:650}
        .content input:not([type="checkbox"]):not([type="file"]),.content select,.content textarea{box-sizing:border-box;width:100%;min-height:43px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.4;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
        .content textarea{min-height:100px;resize:vertical}
        .content input:not([type="checkbox"]):not([type="file"]):focus,.content select:focus,.content textarea:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13)}
        .content input[readonly]{background:#f8fafc;color:#64748b;cursor:default}
        .content input[type="checkbox"]{width:17px;height:17px;accent-color:#ea580c;cursor:pointer;margin:0;flex:0 0 auto}
        .content .checkbox{display:flex;align-items:center;gap:9px;align-self:end;min-height:43px;padding:0 2px}
        .content .checkbox label{margin:0;cursor:pointer;font-weight:550;color:#475569}
        .content .actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding:0 2px}
        .content .btn,.content .btn-secondary,.content .btn-excluir{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:10px 16px;border:1px solid transparent;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer;transition:transform .15s,box-shadow .15s,background .15s}
        .content .btn{background:#f97316;color:#fff;box-shadow:0 3px 8px rgba(249,115,22,.18)}
        .content .btn:hover{background:#ea580c;box-shadow:0 5px 12px rgba(249,115,22,.23);transform:translateY(-1px)}
        .content .btn-secondary{background:#fff;color:#334155;border-color:#cbd5e1}
        .content .btn-secondary:hover{background:#f8fafc;border-color:#94a3b8}
        .content .btn-excluir{background:#fff;color:#dc2626;border-color:#fecaca}
        .content .btn-excluir:hover{background:#fef2f2;border-color:#fca5a5}
        .content table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
        .content table thead th{padding:13px 15px;background:#fff7ed;color:#9a3412;text-align:left;font-size:12px;font-weight:750;white-space:nowrap;border-bottom:1px solid #fed7aa}
        .content table thead th:first-child{border-top-left-radius:9px}.content table thead th:last-child{border-top-right-radius:9px}
        .content table tbody td{padding:13px 15px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle}
        .content table tbody tr:hover td{background:#fffaf5}
        .content table tbody tr:last-child td{border-bottom:0}
        .content .alert{padding:13px 16px;border-radius:11px;border:1px solid #e2e8f0;font-size:14px}
        .content .alert.success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .content .alert.error{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .metric-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:22px;padding-top:20px;border-top:1px solid #f1f5f9}
        .metric-card{padding:15px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}
        .metric-card label{color:#64748b!important;font-size:12px!important;margin-bottom:8px!important}
        .metric-card input{font-weight:650}
        .history-card{margin-top:0}
        @media(max-width:850px){.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.metric-grid{grid-template-columns:1fr 1fr}.content{width:calc(100% - 28px);margin-top:22px}.content .card{padding:20px}}
        @media(max-width:560px){.content{width:calc(100% - 22px);margin:16px auto 30px}.page-title{font-size:23px}.content .card{padding:16px;border-radius:13px}.form-grid,.metric-grid{grid-template-columns:1fr;gap:16px}.content .actions{align-items:stretch;flex-direction:column}.content .actions>*{width:100%;box-sizing:border-box}.modal{padding:18px}.content table{min-width:540px}.history-table-wrap{overflow-x:auto;border:1px solid #e2e8f0;border-radius:10px}.content table thead th{padding:11px}.content table tbody td{padding:11px}}
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>
<div class="content">
    <form method="post">
        <div>
            <div class="page-title"><?= $Alterando ? 'Editar Produto / Serviço' : 'Cadastro de Produto / Serviço' ?></div>
            <div class="page-subtitle"><?= $Alterando ? 'Atualize as informações, preços e metas deste cadastro.' : 'Cadastre produtos e serviços e organize custos, preços e metas em um só lugar.' ?></div>
        </div>

        <?php if ($msgRetorno): ?>
            <div class="alert <?= htmlspecialchars($tipoMsg, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($msgRetorno, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card">
            <h2 class="section-heading">Dados do produto ou serviço</h2>
            <p class="section-description">Informações principais, classificação e valores comerciais.</p>
            <div class="form-grid">
                <div>
                    <label for="Nome">Nome</label>
                    <input type="text" id="Nome" name="Nome" placeholder="Nome do produto ou serviço" value="<?= htmlspecialchars($_POST['Nome'] ?? ($dados['Nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label for="Codigo">Código</label>
                    <input type="text" id="Codigo" placeholder="Código interno" value="<?= htmlspecialchars($dados['Codigo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
                </div>
                <div>
                    <label for="grupo_nome">Grupo</label>
                    <input type="hidden" name="grupo" id="grupo_id" value="<?= htmlspecialchars((string)($_POST['grupo'] ?? ($dados['Grupo'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" id="grupo_nome" value="<?= htmlspecialchars($nomeGrupo, ENT_QUOTES, 'UTF-8') ?>" placeholder="Clique para buscar..." onclick="abrirModal('grupo')" readonly>
                </div>
                <div>
                    <label for="Tipo">Tipo</label>
                    <select name="Tipo" id="Tipo">
                        <option value="1" <?= (string)($_POST['Tipo'] ?? ($dados['Tipo'] ?? 1)) === '1' ? 'selected' : '' ?>>Serviço</option>
                        <option value="2" <?= (string)($_POST['Tipo'] ?? ($dados['Tipo'] ?? 1)) === '2' ? 'selected' : '' ?>>Produto</option>
                    </select>
                </div>
                <div>
                    <label for="Unidade">Unidade</label>
                    <select name="Unidade" id="Unidade">
                        <option value="1" <?= (string)($_POST['Unidade'] ?? ($dados['Unidade'] ?? 1)) === '1' ? 'selected' : '' ?>>Unidade</option>
                        <option value="2" <?= (string)($_POST['Unidade'] ?? ($dados['Unidade'] ?? 1)) === '2' ? 'selected' : '' ?>>Pacote</option>
                        <option value="3" <?= (string)($_POST['Unidade'] ?? ($dados['Unidade'] ?? 1)) === '3' ? 'selected' : '' ?>>Hora</option>
                        <option value="4" <?= (string)($_POST['Unidade'] ?? ($dados['Unidade'] ?? 1)) === '4' ? 'selected' : '' ?>>Mensal</option>
                    </select>
                </div>
                <div>
                    <label for="valorCusto">Valor de custo (R$)</label>
                    <input type="text" id="valorCusto" name="valorCusto" inputmode="decimal" placeholder="0,00" value="<?= htmlspecialchars($_POST['valorCusto'] ?? (isset($dados['ValorCusto']) ? number_format((float)$dados['ValorCusto'], 2, ',', '.') : ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label for="valorVenda">Valor de venda (R$)</label>
                    <input type="text" id="valorVenda" name="valorVenda" inputmode="decimal" placeholder="0,00" value="<?= htmlspecialchars($_POST['valorVenda'] ?? (isset($dados['ValorVenda']) ? number_format((float)$dados['ValorVenda'], 2, ',', '.') : ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="SolicitaVeiculo" id="SolicitaVeiculo" <?= (isset($_POST['SolicitaVeiculo']) || (!isset($_POST['salvar']) && !isset($_POST['excluir']) && !empty($dados['SolicitaVeiculo']))) ? 'checked' : '' ?>>
                    <label for="SolicitaVeiculo">Solicita dados do veículo</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="inativo" name="inativo" value="1" <?= (isset($_POST['inativo']) || (!isset($_POST['salvar']) && !isset($_POST['excluir']) && (int)($dados['Inativo'] ?? 0) === 1)) ? 'checked' : '' ?>>
                    <label for="inativo">Produto inativo</label>
                </div>
                <div style="grid-column:1/-1">
                    <label for="Descricao">Descrição</label>
                    <textarea placeholder="Descrição detalhada" id="Descricao" name="Descricao"><?= htmlspecialchars($_POST['Descricao'] ?? ($dados['Descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="metric-grid">
                <div class="metric-card">
                    <label for="MetaMensal">Meta mensal (quantidade)</label>
                    <input type="number" name="MetaMensal" id="MetaMensal" min="0" placeholder="Ex.: 100" value="<?= htmlspecialchars((string)($_POST['MetaMensal'] ?? ($dados['MetaMensal'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="metric-card">
                    <label for="quantidadeMes">Quantidade vendida no mês</label>
                    <input type="text" id="quantidadeMes" readonly value="<?= htmlspecialchars((string)$quantidadeMes, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="metric-card">
                    <label for="percentualMeta">Progresso da meta</label>
                    <input type="text" id="percentualMeta" readonly value="<?= number_format((float)$percentual, 1, ',', '.') ?> %">
                </div>
            </div>
        </div>

        <div class="card history-card">
            <h2 class="section-heading">Histórico de custos e preços</h2>
            <p class="section-description">Consulte as alterações anteriores de custo, venda e unidade.</p>
            <div class="history-table-wrap">
                <table>
                    <thead><tr><th>Data</th><th>Custo</th><th>Preço de venda</th><th>Unidade</th></tr></thead>
                    <tbody>
                    <?php if (!empty($dadosCusto)): ?>
                        <?php foreach ($dadosCusto as $custo): ?>
                            <tr>
                                <td><?= !empty($custo['Data']) ? date('d/m/Y', strtotime($custo['Data'])) : '—' ?></td>
                                <td>R$ <?= number_format((float)$custo['ValorCusto'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format((float)$custo['ValorVenda'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($custo['UnidadeLiteral'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:24px">Nenhum histórico de custo cadastrado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="actions">
            <a href="Produtos" class="btn-secondary">← Listar produtos</a>
            <?php if ($Alterando): ?>
                <button type="submit" name="excluir" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este Produto / Serviço? Essa ação não pode ser desfeita.')">🗑 Excluir</button>
            <?php endif; ?>
            <button type="submit" name="salvar" class="btn">💾 Salvar</button>
        </div>
    </form>

    <div class="modal-bg" id="modalBg">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
            <h3 id="modalTitulo">Selecionar grupo</h3>
            <input type="text" id="modalBusca" class="modal-search" placeholder="Digite para buscar..." autocomplete="off">
            <div class="modal-table-wrapper">
                <table><thead><tr id="modalHead"></tr></thead><tbody id="modalBody"></tbody></table>
            </div>
            <button type="button" class="btn" onclick="fecharModal()">Fechar</button>
        </div>
    </div>
</div>
<script>
    const grupos = <?= json_encode($grupos ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    let tipoModal = '';

    document.getElementById('modalBusca').addEventListener('input', function () {
        carregarModal(this.value.toLowerCase());
    });

    function limparGrupo() {
        document.getElementById('grupo_id').value = '0';
        document.getElementById('grupo_nome').value = '';
    }

    document.getElementById('modalBg').addEventListener('click', function (e) {
        if (e.target === this) fecharModal();
    });

    function carregarModal(filtro = '') {
        const head = document.getElementById('modalHead');
        const body = document.getElementById('modalBody');
        head.innerHTML = '';
        body.innerHTML = '';
        filtro = (filtro || '').toLowerCase();
        if (tipoModal === 'grupo') {
            document.getElementById('modalTitulo').innerText = 'Selecionar grupo';
            head.innerHTML = '<th>Nome</th>';
            (grupos || []).filter(g => (g.Nome || '').toLowerCase().includes(filtro)).forEach(g => {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.textContent = g.Nome || '';
                tr.appendChild(td);
                tr.addEventListener('click', () => selecionarGrupo(g.Id, g.Nome || ''));
                body.appendChild(tr);
            });
        }
    }

    function selecionarGrupo(id, nome) {
        document.getElementById('grupo_id').value = id;
        document.getElementById('grupo_nome').value = nome;
        fecharModal();
    }

    function abrirModal(tipo) {
        tipoModal = tipo;
        document.getElementById('modalBg').style.display = 'flex';
        const input = document.getElementById('modalBusca');
        input.value = '';
        carregarModal('');
        setTimeout(() => input.focus(), 50);
    }

    function fecharModal() {
        document.getElementById('modalBg').style.display = 'none';
    }

    function formatarMoeda(campo) {
        let valor = campo.value.replace(/\D/g, '');
        valor = (Number(valor) / 100).toFixed(2).replace('.', ',');
        campo.value = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    document.getElementById('valorVenda')?.addEventListener('input', function () { formatarMoeda(this); });
    document.getElementById('valorCusto')?.addEventListener('input', function () { formatarMoeda(this); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('modalBg').style.display === 'flex') fecharModal();
    });
</script>
</body>
</html>
